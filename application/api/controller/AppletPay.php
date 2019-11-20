<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/19
 * Time: 22:21
 */

namespace app\api\controller;

use think\Controller;
use app\api\model\ApiOrder;
use app\api\model\ApiVideo;
use app\api\model\ApiSeek;
use app\common\lib\RequestHttp;
use think\Db;
use think\facade\Env;
use app\api\model\ApiUser;
class AppletPay extends Controller
{
    protected $appletSecret="";    //密钥   32位
    protected $appletAppid = "";    //小程序appid
    protected $appletMchid = "";   //商户号
    protected $appletPaySecret = "";   //小程序 appSecret


    public function __construct()
    {
        $this->appletAppid = Env::get('APPLET_APPID');
        $this->appletSecret = Env::get('APPLET_SECRET');
        $this->appletMchid = Env::get('APPLET_MCHID');
        $this->appletPaySecret = Env::get('APPLET_PAY_SECRET');
    }

    /**
     *time:2019/6/13 16:45
     * 小程序支付统一下单接口
     */
    public function appletWeiPay(){
        if(request()->isPost()) {
            //接收参数
            $uid = input('post.uid');     //用户id
            $id  = input('post.id');      //视频id
            $type = input('post.type');  //类型  1视频 2咨询
            if($uid=='' || $id=='' || $type==''){
                return json_encode(['code'=>1002,'message'=>'参数错误']);
            }
            //查询用户信息
            $ApiUser = new ApiUser();
            $userInfo = $ApiUser->userInfo($uid);
            if(empty($userInfo)) json_encode(['code'=>1003,'message'=>'未找到此数据']);

            //查询该视频或者咨询的金额
            if($type==1){
                $ApiVideo = new ApiVideo();
                $getVideoAllData = $ApiVideo->getVideoAll($id);
                if(empty($getVideoAllData)) return json_encode(['code'=>1003,'message'=>'未找到此数据']);
                $total_fee = $getVideoAllData[0]['price']*100;
                $object_json = json_encode($getVideoAllData);
            }elseif ($type==2){
                $piSeek = new ApiSeek();
                $getFindSeekRes = $piSeek->getFindSeek($id);
                if(empty($getFindSeekRes)) return json_encode(['code'=>1003,'message'=>'未找到此数据']);
                $total_fee = $getFindSeekRes['price']*100;
                $object_json = json_encode($getFindSeekRes);
            }else{
                return json_encode(['code'=>1004,'message'=>'接口未开发']);
            }

            //生成订单
            $ApiOrder = new ApiOrder();
            $ordernum = $this->get_order_sn();
            $data['order_code'] =$ordernum;
            $data['user_id'] =$uid;
            $data['pay_money'] =$total_fee;
            $data['type'] =$type;
            $data['object_id'] =$id;
            $data['object_json'] =$object_json;
            $data['status'] =1;
            $data['create_time'] =time();
            Db::startTrans();
            $addOrderRes = $ApiOrder->addOrder($data);
            if(!$addOrderRes) {
                Db::rollback();
                return json_encode(['code'=>1005,'message'=>'接口异常']);
            }

            //拼接请求参数       签名是最后生成
            $nonce_str = $this->getRandChar(18);   //随机字符串
            $notify_url = "http://localhost:85/appleWeCheck";   //回调地址
            $spbill_create_ip = ip2long($this->GetIP());  //终端ip（ip地址）
            $trade_type = "JSAPI";    //支付类型
            $getopenid = $userInfo['openid'];
            if ($getopenid['code'] != 0) {
                return json_encode($getopenid);
            } else {
                $openid = $getopenid['openid'];
            }

            //生成签名   组装后拼接密钥
            $arr = ['appid' => $this->appletAppid, 'attach' => $type, 'body' => $body, 'mch_id' => $this->appletMchid, 'nonce_str' => $nonce_str, 'notify_url' => $notify_url, 'openid' => $openid, 'out_trade_no' => $ordernum, 'spbill_create_ip' => $spbill_create_ip, 'total_fee' => $total_fee, 'trade_type' => $trade_type];
            //key排序
            ksort($arr);
            $tmp = "";
            //组装成微信验签的格式
            foreach ($arr as $key => $value) {
                $tmp .= $key . "=" . $value . '&';
            }
            $tmp .= "key=" . $this->appletSecret;
            //md5 加密后转大写
            $sign = strtoupper(md5($tmp));
            //组装xml格式
            $res = "<xml>
                   <appid>{$this->appletAppid}</appid>
                   <attach>{$type}</attach>
                   <body>{$body}</body>
                   <mch_id>{$this->appletMchid}</mch_id>
                   <nonce_str>{$nonce_str}</nonce_str>
                   <notify_url>{$notify_url}</notify_url>
                   <openid>{$openid}</openid>
                   <out_trade_no>{$ordernum}</out_trade_no>
                   <spbill_create_ip>{$spbill_create_ip}</spbill_create_ip>
                   <total_fee>{$total_fee}</total_fee>
                   <trade_type>JSAPI</trade_type>
                   <sign>{$sign}</sign>
                </xml>";
            //curl 发送请求
            $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
            $RequestHttp = new RequestHttp();
            $fg = $RequestHttp->post($url, $res);
            //将返回的参数转为数组
            $flag = $this->xml2Array($fg);
            //判断是否成功
            if ($flag['return_code'] == 'SUCCESS' && $flag['result_code'] == 'SUCCESS')   //成功
            {
                $return['code'] = 0;
                $return['message'] = 'success';
                $return['prepay_id'] = 'prepay_id=' . $flag['prepay_id'];    //预支付交易会话标识
                $return['nonceStr'] = $nonce_str;   //随机字符串
                $time = time();
                $return['timeStamp'] = (string)$time;   //时间戳
                //签名
                $tmp2 = "appId={$this->appletAppid}&nonceStr={$nonce_str}&package=prepay_id={$flag['prepay_id']}&signType=MD5&timeStamp={$return['timeStamp']}&key={$this->appletSecret}";
                $sign2 = strtoupper(md5($tmp2));
                $return['paySign'] = $sign2;
                Db::commit();
                return json_encode($return);
            } else   //失败
            {
                Db::rollback();
                $errorcode = ["NOTENOUGH", "ORDERPAID", "ORDERCLOSED", "ORDERCLOSED"];
                if (in_array($flag['err_code'], $errorcode)) {
                    $return['code'] = 1006;
                    $return['message'] = $flag['err_code_des'];
                    return json_encode($return);
                } else {
                    return json_encode(['code'=>1007,'message'=>'接口异常']);
//                    $name = date('Y-m-d H:i:s') . '_weipay';
//                    $log = date('Y-m-d H:i:s') . '----' . $flag['err_code_des'];
                    //$this->LogTxt($flag['err_code_des'],$name);
                }
            }
        }
        return json_encode(['code'=>1001,'message'=>'请求错误']);
    }

    /**
     * 小程序回调
     * $data 2019/11/20 9:04
     */
    public function appletWeiCheck(){
        $flag = file_get_contents("php://input");
        $res = $this->xml2Array($flag);
        $order_num  = $res['out_trade_no'];
        //查询订单
        $ApiOrder = new ApiOrder();
        $orderRes =$ApiOrder->findOrder($order_num);
        if(empty($orderRes)){
            return '<xml>
                      <return_code><![CDATA[FAIL]]></return_code>
                    </xml>';
        }
        if($orderRes['status']==2){  //已支付
            return '<xml>
                      <return_code><![CDATA[SUCCESS]]></return_code>
                      <return_msg><![CDATA[OK]]></return_msg>
                    </xml>';
        }
        //异步信息存储下来
        $sign2 = $res['sign'];
        unset($res['sign']);
        //key排序
        ksort($res);
        $tmp = "";
        //组装
        foreach ($res as $key => $value) {
            $tmp.=$key."=".$value.'&';
        }
        $tmp.= "key=".$this->key;
        //md5 加密后转大写
        $sign = strtoupper(md5($tmp));
        //验签
        if($sign==$sign2)    //验签通过
        {
            if ($res['return_code'] == 'SUCCESS')   //支付成功
            {
                if($orderRes['pay_money']==$res['total_fee']/100){
                    //修改订单状态
                    $paytimes = strtotime($res['time_end']);
                    Db::startTrans();
                    $upOrderStatusRes =$ApiOrder->upOrderStatus($order_num,$paytimes);
                    if(!$upOrderStatusRes){
                        Db::rollback();
                        return '<xml>
                              <return_code><![CDATA[FAIL]]></return_code>
                            </xml>';
                    }
                    //修改订单视频的对应购买数量
                    if($orderRes['type']==1){  //视频
                        //增加购买的商品数量
                        $ApiVideo = new ApiVideo();
                        $upSetIncRes = $ApiVideo->upSetInc($orderRes['object_id']);
                        if(!$upSetIncRes){
                            Db::rollback();
                            return '<xml>
                              <return_code><![CDATA[FAIL]]></return_code>
                            </xml>';
                        }
                    }
                    Db::commit();
                    return '<xml>
                      <return_code><![CDATA[SUCCESS]]></return_code>
                      <return_msg><![CDATA[OK]]></return_msg>
                    </xml>';
                }else{
                    return '<xml>
                              <return_code><![CDATA[FAIL]]></return_code>
                            </xml>';
                }
            }
        }
    }

    /**
     * 查询订单的支付状态
     * $data 2019/11/20 9:34
     */
    public function getOrderPayStatus()
    {
        if(request()->isGet()){
            //接收订单号
            $ordernum = input('get.ordernum');
            if(!$ordernum) return json_encode(['code'=>1002,'message'=>'参数错误']);
            $ApiOrder = new ApiOrder();
            $findOrderRes = $ApiOrder->findOrder($ordernum);
            if(empty($findOrderRes)) return json_encode(['code'=>1003,'message'=>'订单不存在']);
            if($findOrderRes['status']==2){  //已支付
                return json_encode(['code'=>0,'message'=>'success']);
            }elseif ($findOrderRes['status']==1){
                return json_encode(['code'=>1004,'message'=>'订单未支付']);
            }
        }
        return json_encode(['code'=>1001,'message'=>'请求错误']);
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

    //生成订单号  保持唯一
    public function get_order_sn(){
        $ApiOrder = new ApiOrder();
        $order_sn = null;
        //保证订单不会有重复
        while (true)
        {
            $order_sn = date('YmdHis').rand(100000,999999);  //订单号
            $order_sn_count = $ApiOrder->where('order_code',$order_sn)->count();
            if($order_sn_count==0)
            {
                break;
            }
        }
        return $order_sn;
    }

    //获取IP
    public function GetIP() {
        static $ip = NULL;
        if ($ip !== NULL)
            return $ip;

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos)
                unset($arr[$pos]);
            $ip = trim($arr[0]);
        }
        else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        //IP地址合法验证
        $ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
        return $ip;
    }

    //xml转数组
    public function xml2Array($xml){
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring),true);
        return $val;
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