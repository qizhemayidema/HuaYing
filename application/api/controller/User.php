<?php
/**
 * Created by PhpStorm.
 * User: xcf
 * Date: 2019/11/19
 */
namespace app\api\controller;

use think\Request;
use think\Controller;
use app\api\model\ApiUser;
use app\api\model\ApiOrder;
use app\api\model\ApiBusOrder;

class User extends Controller
{
	/**
     * 用户信息
     * $data 2019/11/20
     */
    public function user()
    {
    	$list = (new ApiUser)->getUser();
        return json($list, 256);
    }

    /**
     * 用户信息修改
     * $data 2019/11/20
     */
    public function uedit(Request $request)
    {
    	# code...
    	$list = (new ApiUser)->edit($request);
        return json($list, 256);
    }


    /**
     * 我的预约
     * $data 2019/11/20
     */
    public function uenroll()
    {
        # code...
        $list = (new ApiBusOrder)->getUList();
        return json($list, 256);
    }

    /**
     * 我的课程
     * $data 2019/11/20
     */
    public function uvideo()
    {
        # code...
        $where[] = ['a.type', '=', 1];
        $list = (new ApiOrder)->getOrder($where);
        return json($list, 256);
    }

    /**
     * 我的z咨询
     * $data 2019/11/20
     */
    public function useek()
    {
        # code...
        $where[] = ['a.type', '=', 2];
        $list = (new ApiOrder)->getOrder($where, 'seek');
        return json($list, 256);
    }
}