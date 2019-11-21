<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/20
 * Time: 10:42
 */

namespace app\api\controller;

use think\Controller;
use think\facade\Env;
use app\common\lib\RequestHttp;
use think\Cache;
use app\api\controller\WXBizDataCrypt;
use app\api\model\ApiUser;
use app\http\middleware\AppletToken;
define('APPLET_ACCESS_TOKEN','applet_access_token');
class AppletUser extends Controller
{
    protected $appletSecret = "";    //密钥   32位
    protected $appletAppid = "";    //小程序appid
    protected $appletMchid = "";   //商户号
    protected $appletPaySecret = "";   //小程序 appSecret
    protected $Curl = "";

    public function __construct()
    {
        $this->appletAppid = Env::get('APPLET_APPID');
        $this->appletSecret = Env::get('APPLET_SECRET');
        $this->appletMchid = Env::get('APPLET_MCHID');
        $this->appletPaySecret = Env::get('APPLET_PAY_SECRET');
        $this->Curl = new RequestHttp();
    }

    /**
     * 用户是授权后没有账户需要生成一个
     * @return string
     * $data 2019/11/20 13:04
     */
    public function Login()
    {
        if (request()->isPost()){
            //接收参数
            $code = input('post.code');   //获取openid 的code
            $nickname = input('post.nickname');  //获取用户昵称
            $avatarUrl = input('post.avatarurl');  //获取用户头像
            if($code=='' || $nickname=='' || $avatarUrl==''){
                return json_encode(['code'=>0,'msg'=>'参数错误']);
            }
            //获取openid
            $getOpenidRes = $this->getAppleOpenId($code);
            if($getOpenidRes['code']!=0){
                return json_encode(['code'=>0,'msg'=>$getOpenidRes['message']]);
            }
            //查询该用户是否存在
            $ApiUser = new ApiUser();
            $userOpenidInfoRes = $ApiUser->userOpenidInfo($getOpenidRes['openid']);
            //给用户生成2个小时的token
            $userToken = $this->setToken($getOpenidRes['openid']);
            if(!empty($userOpenidInfoRes)){   //用户存在
                //修改用户表的token 用户昵称 用户头像
                $upUserTokenRes = $ApiUser->upUserToken($userOpenidInfoRes['id'],$userToken,$nickname,$avatarUrl);
                if(!$upUserTokenRes) return json_encode(['code'=>0,'msg'=>'token生成失败']);
                return json_encode(['code'=>1,'msg'=>'success','data'=>['userToken'=>$userToken]]);
            }
            //组装用户信息
            $userInfoDataArr['openid'] =$getOpenidRes['openid'];
            $userInfoDataArr['nickname'] =$nickname;
            $userInfoDataArr['create_time'] =time();
            $userInfoDataArr['avatar_url'] =$avatarUrl;
            $userInfoDataArr['token'] =$userToken;
            $addFlag = $ApiUser->addUser($userInfoDataArr);
            if(!$addFlag){
                return json_encode(['code'=>0,'msg'=>'用户生成失败']);
            }
            return json_encode(['code'=>1,'msg'=>'success','data'=>['userToken'=>$userToken]]);
        }
        return json_encode(['code'=>0,'msg'=>'请求错误']);
    }

    /**
     * 获取基础的access_toke
     * @return mixed
     * $data 2019/11/20 10:49
     */
    public function getAccessToken()
    {
        $cache = new Cache(['type' => config('cache.type')]);
        if ($cache->has(APPLET_ACCESS_TOKEN)) {
            return $cache->get(APPLET_ACCESS_TOKEN);
        } else {
            $ulr = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->appletAppid . '&secret=' . $this->appletSecret;
            $data = $this->Curl->get($ulr, false);
            $ret = json_decode($data, true);
            if ($ret['errcode'] == 0) {
                $cache->set(APPLET_ACCESS_TOKEN, $ret['access_token'], $ret['expires_in']);
                return $ret['access_token'];
            }
        }
    }

    /**
     * 获取用户openid
     * @param $js_code
     * @return array
     * $data 2019/11/19 22:32
     */
    function getAppleOpenId($js_code){
        if($js_code=='') return array('code'=>001,'message'=>'缺少js_code');

        $appid = $this->appletAppid;

        $appsecret = $this->appletSecret;

        $curl = "https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code";

        $curl = sprintf($curl,$appid,$appsecret,$js_code);

        $result = $this->curls($curl,'');
        $resultArray = json_decode($result,true);
        if(!isset($resultArray['errcode'])){
            $return['code'] = 0;
            $return['message'] ='success';
            $return['openid'] =$resultArray['openid'];
            $return['session_key'] =$resultArray['session_key'];
        }
        else{
            $return['code'] = 002;
            $return['message'] =$resultArray['errmsg'];
        }

        return $return;
    }

    //小程序解密encryptdata
    public function decodeData($encryptdata,$iv,$sessionKey){
        //测试openid   ojbyu4is9DY-khDP0NH4ovGiCAkg
        //测试sessionkey   I/x8gfN+zRfT5uekBxDIAw==
        if($encryptdata==''){
            return array('code'=>001,'message'=>'缺少数据');
        }
        $pc = new WXBizDataCrypt ( $this->appletAppid, $sessionKey);
        $errCode = $pc->decryptData ( $encryptdata, $iv, $data );

        if ($errCode == 0) {
            // 返回值 $data 就是一个json字符串
            $return = json_decode($data,true);
            $return['code'] = 0;
            $return['message'] = 'success';
        } else {
            $return['code'] = $errCode;
            $return['message'] = 'fail';
        }

        return $return;
    }

    /**
     * 设置token
     * @param $openid
     * @return string
     * $data times
     */
    public function setToken($openid){
        $salt = $this->getRandChar(6);
        $times = microtime(true)*10000;
        $token = md5($openid.$times.$salt);
        $cache = new Cache(['type' => config('cache.type')]);
        $cache->set($token,'1',7200);
        return $token;
    }

    //生成随机数
    public function getRandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;

        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }

    //curl模拟发送http[s]请求(get/post)
    function curls($url,$xml,$second=30)
    {
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页

        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);

        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        //运行curl
        $result = curl_exec($ch);
        curl_close($ch);
        if($result)
            return $result;
        else
            return false;
    }
}