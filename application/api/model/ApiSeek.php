<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/19
 * Time: 17:17
 */

namespace app\api\model;

use think\Model;
class ApiSeek extends Model
{
    protected  $table="base_seek";
    public function getSeek($limits=''){
        return $this->where(['delete_time'=>0])->limit($limits)->select()->toArray();
    }

    public function getFindSeek($id){
        $where[] = ['id','=',$id];
        $where[] = ['delete_time','=',0];
        return $this->where($where)->find();
    }
}