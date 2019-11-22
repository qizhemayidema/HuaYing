<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/19
 * Time: 17:33
 */

namespace app\api\controller;

use think\Model;
use app\api\model\ApiCategory;
use app\api\model\ApiVideo;
use app\api\model\ApiVideoSection;
use app\api\model\ApiComment;
use app\common\lib\Verify;
use app\api\model\ApiOrder;
use app\api\model\ApiUser;
class AppletCourse extends Model
{
    protected  $ApiVideo = '';
    public function __construct(){
        $this->ApiVideo = new ApiVideo();
    }
    /**
     * 在线课程列表
     * $data 2019/11/19 17:34
     */
    public function courseList(){
        if(request()->isGet()){
            //接收参数
            $type = input('get.type');    //类别id
            $paytype = input('get.paytype');   //免费不免费   1免费 2付费
            //课程分类
            $ApiCategory = new ApiCategory();
            $ApiCategoryRes = $ApiCategory->getList(3);
            if(!empty($ApiCategoryRes)){
                foreach ($ApiCategoryRes as $v){
                    $returnRes['data']['sourceType'][] = Array('id'=>$v['id'],'name'=>$v['name']);
                }
            }else{
                $returnRes['data']['sourceType'] = Array();
            }
            //为你推荐
            $where = Array();
            if($type!=''){
                $where[] = Array('a.cate_id','=',$type);
            }
            if($paytype==1){
                $where[] = Array('a.price','=',0);
            }elseif ($paytype==2){
                $where[] = Array('a.price','>',0);
            }
            $recoVideoListRes = $this->ApiVideo->recoVideoList($where,'a.id,a.pic,a.title,a.price,a.buy_sum,count(*) as knob_sum',4);
            if(!empty($recoVideoListRes)){
                foreach ($recoVideoListRes as $v){
                    $returnRes['data']['recommend'][]=Array(
                        'id'=>$v['id'],
                        'pic'=>config('app.localhost_path').$v['pic'],
                        'title'=>$v['title'],
                        'buy_sum'=>$v['buy_sum'],
                        'price'=>$v['price'],
                        'knob_sum'=>$v['knob_sum']
                    );
                }
            }else{
                $returnRes['data']['recommend']=Array();
            }

            $returnRes['code']=1;
            $returnRes['msg']='successs';
            return json_encode($returnRes);
        }
        return json_encode(['code'=>0,'msg'=>'请求错误']);
    }

    /**
     * 在线课程详情
     * $data 2019/11/19 21:09
     */
    public function courseDetail()
    {
        if(request()->isGet()){
            $Verify = new Verify();
            //接收id
            $id = input('get.id');   //视频id
            $knob = input('get.knob',1);   //第几节
            $token = input('get.token');  //token
            if(!$id) return json_encode(['code'=>0,'msg'=>'请求参数错误']);
            $detailRes = $this->ApiVideo->getVideoDetail($id,$knob,'a.*,b.source_url');
            if(empty($detailRes)) return json_encode(['code'=>0,'msg'=>'不存在课程']);
            //课程信息
            $returnRet['code'] = 1;
            $returnRet['msg'] = 'success';
            if($token==''){   //用户没有登陆
                $returnRet['data']['url'] = '';
            }else{
                $ApiOrder = new ApiOrder();
                //通过token查询用户id
                $ApiUser = new ApiUser();
                $userInfo = $ApiUser->userInfo($token);
                if(empty($userInfo)) return json_encode(Array('code'=>0,'msg'=>'用户不存在'));
                //查找该用户有没有购买此课程
                $orderRes = $ApiOrder->userOrderFind($userInfo['id'],1,$id);
                if(empty($orderRes)){
                    $returnRet['data']['url'] = '';
                }else{
                    $returnRet['data']['url'] = config('app.localhost_path').$detailRes['source_url'];
                }
            }

            $returnRet['data']['title'] = $detailRes['title'];
            $returnRet['data']['keywords'] = $detailRes['keywords'];
            $returnRet['data']['desc'] = $Verify->replaceImg($detailRes['desc']);
            $returnRet['data']['price'] = $detailRes['price'];
            $returnRet['data']['id'] = $detailRes['id'];
            //查找课程章节
            $ApiVideoSection = new ApiVideoSection();
            $getListRes = $ApiVideoSection->getList($id,$knob);
            if(!empty($getListRes)){
                foreach ($getListRes as $v){
                    $returnRet['data']['knobList'][] = Array(
                        'title'=>$v['title'],
                        'knob'=>$v['number'],
                        'url'=>config('app.localhost_path').$detailRes['source_url'],
                    );
                }
            }else{
                $returnRet['data']['knobList'] = Array();
            }
            //评价
            $ApiComment = new ApiComment();
            $getListCommentRes = $ApiComment->getList($id,6);
            if(!empty($getListCommentRes)){
                foreach ($getListCommentRes as $vv){
                    $returnRet['data']['comment'][] = Array(
                        'url'=>config('app.localhost_path').$vv['avatar_url'],
                        'content'=>$vv['comment'],
                        'nickname'=>$vv['nickname'],
                        'create_time'=>date('Y-m-d',$vv['create_time']),
                        'score'=>$vv['score'],
                    );
                }
            }else{
                $returnRet['data']['comment'] = Array();
            }
            return json_encode($returnRet);
        }
        return json_encode(['code'=>0,'msg'=>'请求错误']);
    }

}