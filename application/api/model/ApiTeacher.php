<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/19
 * Time: 17:17
 */

namespace app\api\model;

use think\Model;
class ApiTeacher extends Model
{
    protected  $table="base_teacher";
    public function getTeachers($limits=''){
        return $this->where(['delete_time'=>0])->limit($limits)->select()->toArray();
    }
}