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
class AppleCourse extends Model
{
    /**
     * 在线课程
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
            $ApiVideo = new ApiVideo();
            $where = Array();
            if($type!=''){
                $where['a.cate_id'] = $type;
            }
            if($paytype==1){
                $where['a.price'] = Array('eq',0);
            }elseif ($paytype==2){
                $where['a.price'] = Array('gt',0);
            }
            $recoVideoListRes = $ApiVideo->recoVideoList($where,'a.id,a.pic,a.title,a.price,a.see_sum',4);
            if(!empty($recoVideoListRes)){
                foreach ($recoVideoListRes as $v){
                    $returnRes['recommend'][]=Array(
                        'id'=>$v['id'],
                        'pic'=>$v['pic'],
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
    }
}