<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use app\common\model\BusinessOrder as BusSubModel;

class BusSub extends Base
{
    public function index()
    {
        $list = (new BusSubModel())->alias('sub')
            ->join('business bus','bus.id = sub.bus_id')
            ->field('bus.title,sub.*')
            ->order('sub.id','desc')
            ->paginate(15);

        $this->assign('list',$list);

        return $this->fetch();
    }

    public function add()
    {

    }

    public function save()
    {

    }
}
