<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/19
 * Time: 22:33
 */

namespace app\api\model;

use Think\Model;
class ApiOrder extends Model
{
    protected $table = "base_order";

    public function addOrder($data){
        return $this->insert($data);
    }

    public function findOrder($ordernum)
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
}