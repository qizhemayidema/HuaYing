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
            $token = input('post.token');     //用户token
            $id  = input('post.id');      //视频id
            $type = input('post.type');  //类型  1视频 2咨询
            if($token=='' || $id=='' || $type==''){
                return json_encode(['code'=>0,'msg'=>'参数错误']);
            }
            //查询用户信息
            $ApiUser = new ApiUser();
            $userInfo = $ApiUser->userInfo($token);
            if(empty($userInfo)) json_encode(['code'=>3,'msg'=>'用户不存在']);

            //查询该视频或者咨询的金额
            if($type==1){
                $ApiVideo = new ApiVideo();
                $getVideoAllData = $ApiVideo->getVideoAll($id);
                unset($getVideoAllData['desc']);
                if(empty($getVideoAllData)) return json_encode(['code'=>0,'msg'=>'未找到此数据']);
                $order_price = $getVideoAllData[0]['price'];
                $total_fee = $getVideoAllData[0]['price']*100;
                $object_json = json_encode($getVideoAllData,true);
                $body = '华莹法律研究中心-'.$getVideoAllData[0]['title'];
            }elseif ($type==2){
                $piSeek = new ApiSeek();
                $getFindSeekRes = $piSeek->getFindSeek($id);
                unset($getFindSeekRes['content']);
                if(empty($getFindSeekRes)) return json_encode(['code'=>0,'msg'=>'未找到此数据']);
                $order_price = $getFindSeekRes['price'];
                $total_fee = $getFindSeekRes['price']*100;
                $object_json = json_encode($getFindSeekRes,true);
                $body = '华莹法律研究中心-'.$getFindSeekRes['title'];
            }else{
                return json_encode(['code'=>0,'msg'=>'接口未开发']);
            }

            //生成订单
            $ApiOrder = new ApiOrder();
            $ordernum = $this->get_order_sn();
            $data['order_code'] =$ordernum;
            $data['user_id'] =$userInfo['id'];
            $data['pay_money'] =$order_price;
            $data['type'] =$type;
            $data['object_id'] =$id;
            $data['object_json'] =$object_json;
            $data['status'] =1;
            $data['create_time'] =time();
            Db::startTrans();
            $addOrderRes = $ApiOrder->addOrder($data);
            if(!$addOrderRes) {
                Db::rollback();
                return json_encode(['code'=>0,'msg'=>'接口异常']);
            }

            //拼接请求参数       签名是最后生成
            $nonce_str = $this->getRandChar(32);   //随机字符串
            $notify_url = request()->Domain().url('pay.notify');   //回调地址
            $spbill_create_ip = $this->GetIP();  //终端ip（ip地址）
            $trade_type = "JSAPI";    //支付类型
            $openid = $userInfo['openid'];

            //生成签名   组装后拼接密钥
            $arr = ['appid' => $this->appletAppid, 'attach' => $type, 'body' => $body, 'mch_id' => $this->appletMchid, 'nonce_str' => $nonce_str, 'notify_url' => $notify_url, 'openid' => $openid, 'out_trade_no' => $ordernum, 'spbill_create_ip' => $spbill_create_ip, 'total_fee' => $total_fee, 'trade_type' => $trade_type];
            //key排序
            ksort($arr);
            $tmp = "";
            //组装成微信验签的格式
            foreach ($arr as $key => $value) {
                $tmp .= $key . "=" . $value . '&';
            }
            $tmp .= "key=" . $this->appletPaySecret;
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
                $return['code'] = 1;
                $return['msg'] = 'success';
                $return['data']['prepay_id'] = 'prepay_id=' . $flag['prepay_id'];    //预支付交易会话标识
                $return['data']['nonceStr'] = $nonce_str;   //随机字符串
                $time = time();
                $return['data']['timeStamp'] = (string)$time;   //时间戳
                //签名
                $tmp2 = "appId={$this->appletAppid}&nonceStr={$nonce_str}&package=prepay_id={$flag['prepay_id']}&signType=MD5&timeStamp={$return['data']['timeStamp']}&key={$this->appletPaySecret}";
                $sign2 = strtoupper(md5($tmp2));
                $return['data']['paySign'] = $sign2;
                Db::commit();
                return json_encode($return);
            } else   //失败
            {
                Db::rollback();
                return json_encode(['code'=>0,'msg'=>'接口异常']);
            }
        }
        return json_encode(['code'=>0,'msg'=>'请求错误']);
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
        $orderRes =$ApiOrder->findOrderNum($order_num);
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
        $tmp.= "key=".$this->appletSecret;
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
            $token = input('get.token');
            //查询用户信息
            $ApiUser = new ApiUser();
            $userInfo = $ApiUser->userInfo($token);
            if(empty($userInfo)) json_encode(['code'=>3,'msg'=>'用户不存在']);

            if(!$ordernum) return json_encode(['code'=>0,'msg'=>'参数错误']);
            $ApiOrder = new ApiOrder();
            $findOrderRes = $ApiOrder->findOrder($ordernum,$userInfo['id']);
            if(empty($findOrderRes)) return json_encode(['code'=>0,'msg'=>'订单不存在']);
            if($findOrderRes['status']==2){  //已支付
                return json_encode(['code'=>1,'msg'=>'success']);
            }elseif ($findOrderRes['status']==1){
                return json_encode(['code'=>0,'msg'=>'订单未支付']);
            }
        }
        return json_encode(['code'=>0,'msg'=>'请求错误']);
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

    //服务器生成日志
    public function LogTxt($log_txt="",$folder_file="log.txt"){
        $folder_path =__DIR__.'/log/';
        if (!file_exists($folder_path)) {
            mkdir($folder_path,0777,TRUE);
        }
        $folder_path .= $folder_file;
        $handle = fopen($folder_path,"a");
        fwrite($handle,$log_txt);
        fclose($handle);
    }
}