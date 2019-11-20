<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/20
 * Time: 10:42
 */

namespace app\api\controller;

use Think\Controller;
use think\facade\Env;
use app\common\lib\RequestHttp;
use Think\Cache;
use app\api\controller\WXBizDataCrypt;
use app\api\model\ApiUser;
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
            $iv = input('post.iv');   //解密数据需要的
            $encrypteddata = input('post.encrypteddata');   //数据
            if($code=='' || $iv=='' || $encrypteddata==''){
                return json_encode(['code'=>1002,'message'=>'参数错误']);
            }
            //获取openid
            $getOpenidRes = $this->getAppleOpenId($code);
            if($getOpenidRes['code']!=0){
                return json_encode(['code'=>1003,'message'=>$getOpenidRes['message']);
            }
            //查询该用户是否存在
            $ApiUser = new ApiUser();
            $userOpenidInfoRes = $ApiUser->userOpenidInfo($getOpenidRes['openid']);
            if(!empty($userOpenidInfoRes)){   //用户存在
                return json_encode(['code'=>0,'message'=>'success','uid'=>$userOpenidInfoRes['id']]);
            }
            //解密数据
            $encrypteddataRes = $this->decodeData($encrypteddata,$iv,$getOpenidRes['session_key']);
            if($encrypteddataRes['code']!=0){
                return json_encode(['code'=>1004,'message'=>'解密失败');
            }
            //组装用户信息
            $userInfoDataArr['openid'] =$getOpenidRes['openid'];
            $userInfoDataArr['nickname'] =$getOpenidRes['nickName'];
            $userInfoDataArr['city'] =$getOpenidRes['city'];
            $userInfoDataArr['province'] =$getOpenidRes['province'];
            $userInfoDataArr['country'] =$getOpenidRes['country'];
            $userInfoDataArr['unionid'] =$getOpenidRes['unionId'];
            $userInfoDataArr['create_time'] =time();
            $userInfoDataArr['avatar_url'] =$getOpenidRes['avatarUrl'];
            $addFlag = $ApiUser->addUser($userInfoDataArr);
            if(!$addFlag){
                return json_encode(['code'=>1004,'message'=>'用户生成失败');
            }
            return json_encode(['code'=>0,'message'=>'success','uid'=>$addFlag]);
        }
        return json_encode(['code'=>1001,'message'=>'请求错误']);
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

        $result = $this->Curl->get($curl,false);
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
}