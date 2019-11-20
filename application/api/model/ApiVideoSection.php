<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/19
 * Time: 21:38
 */

namespace app\api\model;

use think\Model;
class ApiVideoSection extends Model
{
    protected  $table ="base_video_section";

    /**
     * 获取除了本章节以外的其他章节
     * @param $id   int 课程id
     * @param $knob int  章节id
     * $data times
     */
    public function getList($id,$knob){
        $where[] = Array('video_id','=',$id);
        $where[] = Array('number','<>',$knob);
        return $this->where($where)->select()->toArray();
    }
}