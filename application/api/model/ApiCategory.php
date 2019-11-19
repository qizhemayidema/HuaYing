<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/19
 * Time: 17:36
 */

namespace app\api\model;

use Think\Model;
class ApiCategory extends Model
{
    protected $table = "base_category";

    public function getList($type){
        return $this->where(['type'=>$type])->order('order_num asc')->select()->toArray();
    }

}