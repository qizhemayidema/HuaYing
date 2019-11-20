<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/19
 * Time: 17:00
 */

namespace app\api\model;

use think\Model;
class ApiVideo extends Model
{
    protected  $table = "base_video";

    /**
     * 获取推荐课程
     * $data 2019/11/19 17:04
     */
    public function getVideo($where=Array(),$fields='',$limits=''){
        return $this->where(['delete_time'=>0])->where($where)->order('buy_sum desc,see_sum desc')->field($fields)->limit($limits)->select()->toArray();
    }


    public function recoVideoList($where=Array(),$fields='',$limits=''){
        return $this->alias('a')->join('base_category b','a.cate_id=b.id')->field($fields)->where($where)->where(['delete_time'=>0])->order('buy_sum desc,see_sum desc')->limit($limits)->select()->toArray();
    }

    /**
     * 详情
     * @param $id   int   课程id
     * @param $knob int 第几节
     * @param string $fields
     * @return array|false|null|\PDOStatement|string|Model
     * $data 2019/11/19 21:26
     */
    public function getVideoDetail($id,$knob,$fields=''){
        $where[] = Array('a.id','=',$id);
        $where[] = Array('b.number','<>',$knob);
        return $this->alias('a')->join('base_video_section b','a.id=b.video_id')->field($fields)->where($where)->where(['delete_time'=>0])->find();
    }

    public function getVideoAll($id){
        $where[] = ['a.id','=',$id];
        $where[] = ['delete_time','=',0];
        return $this->alias('a')->field('a.*,b.id as sid,b.video_id,b.number,b.title as stitle,b.source_url')->join('base_video_section b','a.id=b.video_id')->where($where)->select()->toArray();
    }

    public function upSetInc($id){
        $where[] = ['id','=',$id];
        return $this->where($where)->setInc('buy_sum');
    }

}