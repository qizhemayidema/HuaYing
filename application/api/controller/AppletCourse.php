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
                    $returnRes['sourceType'][] = Array('id'=>$v['id'],'name'=>$v['name']);
                }
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
            $recoVideoListRes = $this->ApiVideo->recoVideoList($where,'a.id,a.pic,a.title,a.price,a.see_sum',4);
            if(!empty($recoVideoListRes)){
                foreach ($recoVideoListRes as $v){
                    $returnRes['recommend'][]=Array(
                        'id'=>$v['id'],
                        'pic'=>config('app.localhost_path').$v['pic'],
                        'title'=>$v['title'],
                        'see_sum'=>$v['see_sum'],
                        'price'=>$v['price']
                    );
                }
            }

            $returnRes['code']=0;
            $returnRes['message']='successs';
            return json_encode($returnRes);
        }
        return json_encode(['code'=>1001,'message'=>'请求错误']);
    }

    /**
     * 在线课程详情
     * $data 2019/11/19 21:09
     */
    public function courseDetail()
    {
        if(request()->isGet()){
            //接收id
            $id = input('get.id');   //视频id
            $knob = input('get.knob',1);   //第几节
            if(!$id) return json_encode(['code'=>1002,'message'=>'请求参数错误']);
            $detailRes = $this->ApiVideo->getVideoDetail($id,$knob,'a.*,b.source_url');
            if(empty($detailRes)) return json_encode(['code'=>1003,'message'=>'不存在课程']);
            //课程信息
            $returnRet['code'] = 0;
            $returnRet['success'] = 'success';
            $returnRet['url'] = config('app.localhost_path').$detailRes['source_url'];
            $returnRet['title'] = $detailRes['title'];
            $returnRet['keywords'] = $detailRes['keywords'];
            $returnRet['desc'] = $detailRes['desc'];
            $returnRet['desc'] = $detailRes['desc'];
            $returnRet['price'] = $detailRes['price'];
            $returnRet['id'] = $detailRes['id'];
            //查找课程章节
            $ApiVideoSection = new ApiVideoSection();
            $getListRes = $ApiVideoSection->getList($id,$knob);
            if(!empty($getListRes)){
                foreach ($getListRes as $v){
                    $returnRet['knobList'][] = Array(
                        'title'=>$v['title'],
                        'knob'=>$v['number'],
                        'url'=>config('app.localhost_path').$detailRes['source_url'],
                    );
                }
            }
            //评价
            $ApiComment = new ApiComment();
            $getListCommentRes = $ApiComment->getList($id,3);
            if(!empty($getListCommentRes)){
                foreach ($getListCommentRes as $vv){
                    $returnRet['comment'][] = Array(
                        'title'=>config('app.localhost_path').$vv['avatar_url'],
                        'content'=>$vv['comment'],
                        'nickname'=>$vv['nickname'],
                        'create_time'=>date('Y-m-d',$vv['create_time']),
                        'score'=>$vv['score'],
                    );
                }
            }
            return json_encode($returnRet);
        }
        return json_encode(['code'=>1001,'message'=>'请求错误']);
    }

}