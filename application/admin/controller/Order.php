<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class Order extends Base
{
    //课程订单
    public function class()
    {
        $list = (new \app\common\model\Order())->alias('order')
            ->join('user user','user.id = order.user_id')
            ->where(['order.type'=>1])
            ->field('order.*,user.nickname')
            ->order('order.id','desc')
            ->paginate(15);

        $this->assign('list',$list);

        return $this->fetch();
    }

    //课程订单详情
    public function classInfo(Request $request)
    {
        $id = $request->param('id');

        $info = (new \app\common\model\Order())->alias('order')
            ->join('user user','user.id = order.user_id')
            ->where(['order.id'=>$id])
            ->field('order.*,user.*')
            ->find()->toArray();

        $info['data'] = json_decode($info['object_json'],256);

        $this->assign('order',$info);

        return $this->fetch();

    }

    //咨询订单
    public function seek()
    {
        $list = (new \app\common\model\Order())->alias('order')
            ->join('user user','user.id = order.user_id')
            ->where(['order.type'=>2])
            ->field('order.*,user.nickname')
            ->order('order.id','desc')
            ->paginate(15);

        $this->assign('list',$list);

        return $this->fetch();
    }

    public function seekInfo(Request $request)
    {
        $id = $request->param('id');

        $info = (new \app\common\model\Order())->alias('order')
            ->join('user user','user.id = order.user_id')
            ->where(['order.id'=>$id])
            ->find()->toArray();
        $info['data'] = json_decode($info['object_json'],256);
        $this->assign('order',$info);

        return $this->fetch();
    }
}