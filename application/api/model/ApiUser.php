<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/20
 * Time: 11:11
 */

namespace app\api\model;

use Think\Model;
class ApiUser extends Model
{
    protected  $table="base_user";
    /**
     * 获取用户信息
     * @param $uid
     * $data 2019/11/20 11:12
     */
    public function userInfo($uid){
        $where[] = ['id','=',$uid];
        return $this->where($where)->find();
    }

    public function userOpenidInfo($openid){
        $where[] = ['openid','=',$openid];
        return $this->where($where)->find();
    }

    public function addUser($data){
        return $this->insertGetId($data);
    }
}