<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/19
 * Time: 22:33
 */

namespace app\api\model;


use think\Db;
use think\Model;
class ApiOrder extends Model
{
    protected $table = "base_order";

    public function addOrder($data){
        return $this->insert($data);
    }

    public function findOrder($ordernum,$uid)
    {
        $where[] = ['order_code','=',$ordernum];
        $where[] = ['user_id','=',$uid];
        return $this->where($where)->find();
    }

    public function findOrderNum($ordernum)
    {
        $where[] = ['order_code','=',$ordernum];
        return $this->where($where)->find();
    }

    /**
     * 修改订单为支付
     * @param $ordernum
     * @return int|string
     * $data 2019/11/20 9:15
     */
    public function upOrderStatus($ordernum,$times){
        $where[] = ['order_code','=',$ordernum];
        $update['status'] = 2;
        $update['pay_time'] = $times;
        return $this->where($where)->update();
    }

    /**
     * 获取订单列表
     * @return int|string
     * $data 2019/11/20 9:15
     */
    public function getOrder($where = [], $table = 'video')
    {
        # code...
        $where[] = ['a.status', '=', 2];

        if (input('user_id') && Db::name('user')->find(input('user_id'))) {
            # code...
            $where[] = ['a.user_id', '=', input('user_id')];
            $strip = input('strip')?input('strip'):10;
            $list = $this -> alias('a')
                          -> field('b.*, a.user_id, a.pay_time')
                          -> join($table.' b', 'a.object_id = b.id')
                          -> where($where)
                          -> order('a.pay_time desc')
                          -> paginate($strip)
                          ->each(function($item, $key){
                            $item['pic'] = config('app.localhost_path').$item['pic'];
                          });

            return ['code'=>1,'msg'=>'请求成功', 'data'=>$list];
        } else {
            return ['code' => 0, 'msg'=>'用户不存在'];
        }
    }

    /**
     * 查询该用户的某个
     * @param $uid
     * @param $ordernum
     * $data times
     */
    public function userOrderFind($uid,$type,$id){
        $where['user_id'] =$uid;
        $where['object_id'] = $id;
        $where['type']  = $type;
        $where['status']  = 2;
        return $this->where($where)->find();
    }
}