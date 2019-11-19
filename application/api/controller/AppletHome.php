<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/19
 * Time: 16:25
 */
namespace app\api\controller;

use think\Controller;
use app\api\model\ApiImage;
use app\api\model\ApiVideo;
use app\api\model\ApiSeek;
use app\api\model\ApiTeacher;
class AppletHome extends Controller
{
    /**
     * 首页
     * $data 2019/11/19 16:26
     */
    public function homeIndex(){
        if(request()->isGet()){
            //首页的banner图
            $ApiImage = new ApiImage();
            $BannerRes = $ApiImage->getBanner(1,'url','3');
            if(!empty($BannerRes)){
                foreach ($BannerRes as $v){
                    $returnArr['homeBanner'][]['url'] = $v;
                }
            }
            //课程
            $ApiVideo = new ApiVideo();
            $VideoRes = $ApiVideo->getVideo('','',2);
            if(!empty($VideoRes)){
                foreach ($VideoRes as $v){
                    $returnArr['homeVideo'][] =Array(
                        'id'=>$v['id'],
                        'url' => $v['pic'],
                        'title'=>$v['title']
                    );
                }
            }
            //法律咨询
            $ApiSeek = new ApiSeek();
            $SeekRes = $ApiSeek->getSeek(3);
            if(!empty($SeekRes)){
                foreach ($SeekRes as $v){
                    $returnArr['homeSeek'][] =Array(
                        'id'=>$v['id'],
                        'url' => $v['pic'],
                        'title'=>$v['title']
                    );
                }
            }
            //师资力量
            $ApiTeacher = new ApiTeacher();
            $TeacherRes = $ApiTeacher->getTeachers(4);
            if(!empty($TeacherRes)){
                foreach ($TeacherRes as $v){
                    $returnArr['homeTeacher'][] =Array(
                        'id'=>$v['id'],
                        'url' => $v['pic'],
                        'name'=>$v['name']
                    );
                }
            }


            $returnArr['code']=0;
            $returnArr['message']='success';
            return json_encode($returnArr);
        }
        return json_encode(['code'=>1001,'message'=>'请求错误']);
    }
}