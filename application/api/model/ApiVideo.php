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

}