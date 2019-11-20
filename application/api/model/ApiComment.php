<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/19
 * Time: 22:10
 */

namespace app\api\model;

use  Think\Model;
class ApiComment extends Model
{
    protected  $table = "base_comment";

    /**
     * 某视频的评论
     * @param $id       int   评论的id
     * @param string $limits
     * @return array
     * $data 2019/11/19 22:13
     */
    public function getList($id,$limits=''){
        $where[] = ['public_id','=',$id];
        return $this->where($where)->limit($limits)->select()->toArray();
    }
}