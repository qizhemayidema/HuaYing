<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/20
 * Time: 14:16
 */

namespace app\api\controller;

use think\Controller;
use app\api\model\ApiUser;
use app\api\model\ApiVideo;
use app\api\model\ApiTeacher;
use app\api\model\ApiComment;
class AppletComment extends Controller
{
    /**
     * 发表评论
     * @return string
     * $data 2019/11/20 14:48
     */
    public function publishComment(){
        if(request()->isPost()){
            $token = input('post.token');  //用户token
            $type = input('post.type');  //评价类型  1课程 2老师
            $object_id = input('post.object_id');  //评论的对象id
            $score = input('post.score');  //分数
            $comment = input('post.comment');  //内容
            if($token=='' || $type=='' || $object_id=='' || $score=='' || $comment==''){
                return  json_encode(['code'=>0,'msg'=>'参数错误']);
            }
            //查询用户信息
            $ApiUser = new ApiUser();
            $userInfoRes = $ApiUser->userInfo($token);
            if(empty($userInfoRes)) return  json_encode(['code'=>3,'msg'=>'用户不存在']);
            //组装评论表数据
            if($type==1){  //课程
                $data['type'] = 1;
                $ApiVideo = new ApiVideo();
                $getFindVideoRes = $ApiVideo->getFindVideo($object_id);
                if(empty($getFindVideoRes))  return  json_encode(['code'=>0,'msg'=>'课程不存在']);
            }elseif ($type==2){
                $data['type'] = 2;
                $ApiTeacher = new ApiTeacher();
                $getFindTeacher = $ApiTeacher->getFindTeacher($object_id);
                if(empty($getFindTeacher))  return  json_encode(['code'=>0,'msg'=>'老师不存在']);
            }else{
                return  json_encode(['code'=>0,'msg'=>'类型错误']);
            }
            $data['public_id'] = $object_id;
            $data['user_id'] = $userInfoRes['id'];
            $data['nickname'] = $userInfoRes['nickname'];
            $data['avatar_url'] = $userInfoRes['avatar_url'];
            $data['comment'] = $comment;
            $data['score'] = $score;
            $data['create_time'] = time();
            $ApiComment = new ApiComment();
            $res = $ApiComment->addComment($data);
            if(!$res){
                return  json_encode(['code'=>0,'msg'=>'评论失败']);
            }
            return  json_encode(['code'=>1,'msg'=>'success']);
        }
        return  json_encode(['code'=>0,'msg'=>'请求错误']);
    }
}