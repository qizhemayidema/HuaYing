<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/20
 * Time: 11:11
 */

namespace app\api\model;

use think\Db;
use think\Model;
use think\Cache;
use think\Request;
use app\common\lib\Upload;
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
     * 获取用户信息
     * $data 2019/11/20
     */
    public function getUser()
    {
        $id = input('user_id');

        if ($id && $user = $this->get($id)) {
            # code...
            if ($user->status == 1) {
                # code...
                return ['code'=>0, 'msg'=>'账号已被冻结'];
            }

            return ['code'=>1,'msg'=>'请求成功', 'data'=>$user];
        } else {
            return ['code'=>3, 'msg'=>'用户不存在'];
        }
    }

    /**
     * 用户信息修改
     * $data 2019/11/20
     */
    public function edit(Request $request)
    {
        # code...
        $data = $request->put();

        $user_id = input('user_id');

        if (!$user_id || !Db::name('user')->find($user_id)) {
            # code...
            return ['code' => 3, 'msg' => '用户不存在'];
        }
        // 接收字段参数
        $arr = ['avatar_url', 'nickname', 'sex', 'province', 'user_id'];

        // 判断传参是否存在
        foreach ($data as $k => $v) {
            # code...
            if (!$v || !in_array($k, $arr)) {
                unset($data[$k]);
            }
        }

        // 如果是图片
        if (isset($data['avatar_url'])) {
            # code...
            $res = (new Upload())->uploadBase64Pic($data['avatar_url'], 'headpic');

            if ($res['code'] == 1) {
                # code...
                $data['avatar_url'] = $res['msg'];
            } else {
                return $res;
            }
        }

        if ($data) {
            # code...
            if ($this->allowField(true)->save($data, ['id' => $data['user_id']])) {

                return ['code' => 1, 'msg' => '修改成功'];
            } else {
                return ['code' => 0, 'msg' => '修改失败'];
            }
        } else {
            return ['code' => 1, 'msg' => '修改成功'];
        }
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