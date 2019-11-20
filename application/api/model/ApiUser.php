<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/20
 * Time: 11:11
 */

namespace app\api\model;

use think\Model;
class ApiUser extends Model
{
    protected  $table="base_user";
    /**
     * 获取用户信息
     * @param $uid
     * $data 2019/11/20 11:12
     */
    public function userInfo($token){
        $where[] = ['token','=',$token];
        return $this->where($where)->find();
    }

    public function userOpenidInfo($openid){
        $where[] = ['openid','=',$openid];
        return $this->where($where)->find();
    }

    public function addUser($data){
        return $this->insertGetId($data);
    }

    /**
     * 更新用户的token
     * @param $id
     * @param $token
     * $data times
     */
    public function upUserToken($id,$token,$nickname,$avatarUrl){
        $where[] = ['id','=',$id];
        $updatedata['token'] = $token;
        $updatedata['nickname'] = $nickname;
        $updatedata['avatar_url'] = $avatarUrl;
        return $this->where($where)->update($updatedata);
    }
}