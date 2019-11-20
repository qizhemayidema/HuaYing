<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/20
 * Time: 13:26
 */

namespace app\api\controller;

use think\Controller;
use app\api\model\ApiTeacher;
use app\api\model\ApiVideo;
use app\api\model\ApiComment;
class AppletTeacher extends Controller
{
    /**
     * 师资力量详情
     * $data 2019/11/20 13:27
     */
    public function getTeacherDetail(){
        if(request()->isGet()){
            //接收教师id
            $id = input('get.id');
            if(!$id) return json_encode(['code'=>0,'msg'=>'参数错误']);
            //查询该教师信息
            $ApiTeacher = new ApiTeacher();
            $getTeachersInfo = $ApiTeacher->getTeachersInfo($id);
            if(empty($getTeachersInfo)) return json_encode(['code'=>0,'msg'=>'教师信息不存在']);
            $returnRes['code'] = 1;
            $returnRes['msg'] = 'success';
            $returnRes['data']['name'] = $getTeachersInfo['name'];
            $returnRes['data']['content'] = $getTeachersInfo['content'];  //介绍
            $returnRes['data']['desc'] = $getTeachersInfo['desc'];       //简介
            $returnRes['data']['teacherPic'] = config('app.localhost_path').$getTeachersInfo['pic'];       //简介
            //查询该教师的课程
            $ApiVideo = new ApiVideo();
            $getAuthorIdVideoRes = $ApiVideo->getAuthorIdVideo($id);
            if(!empty($getAuthorIdVideoRes)){
                foreach ($getAuthorIdVideoRes as $v){
                    $returnRes['data']['video'][] = [
                        'videoId'=>$v['id'],
                        'videoPic'=>$v['pic'],
                        'videoName'=>$v['title'],
                        'videoBuySum'=>$v['buy_sum']
                    ];
                }
            }
            //评价
            $ApiComment = new ApiComment();
            $getTeacherListRes = $ApiComment->getTeacherList($id,6);
            if(!empty($getTeacherListRes)){
                foreach ($getTeacherListRes as $vv){
                    $returnRes['data']['comment'][] = [
                        'title'=>config('app.localhost_path').$vv['avatar_url'],
                        'content'=>$vv['comment'],
                        'nickname'=>$vv['nickname'],
                        'create_time'=>date('Y-m-d',$vv['create_time']),
                        'score'=>$vv['score'],
                    ];
                }
            }
            return json_encode($returnRes);
        }
        return json_encode(['code'=>0,'msg'=>'请求错误']);
    }
}