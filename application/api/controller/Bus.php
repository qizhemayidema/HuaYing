<?php
/**
 * Created by PhpStorm.
 * User: xcf
 * Date: 2019/11/19
 */
namespace app\api\controller;

use think\Controller;
use app\common\model\Business as BusinessModel;
use app\common\typeCode\cate\Business as CateType;
use app\common\model\Category as CategoryModel;
use app\api\model\ApiBus;
use app\api\model\ApiBusOrder;
use app\common\lib\Verify;
class Bus extends Controller
{
    /**
     * 业务列表
     * $data 2019/11/19 16:26
     */
    public function getList(){

    	//获取业务分类
        $categoryModel = new CategoryModel();
        $cate = $categoryModel->getList((new CateType()));
        if (!$cate) return json(['code' => 0, 'msg'=>'分类为空'], 256);

        //获取业务列表
        //获取请求分类下业务id
        $cate_id = input('cate_id')?input('cate_id'):$cate[0]['id'];

        $strip = input('strip')?input('strip'):120;
        $where[] = ['cate_id', '=', $cate_id];
        $where[] = ['delete_time', '=', 0];
        $list = (new BusinessModel())->where($where)->order('id','desc')->paginate($strip)->each(function($item, $key){
                            $item['avatar_url'] = config('app.localhost_path').$item['avatar_url'];
                          });

        return json(['code' => 1,'msg'=> '请求成功', 'data'=>['cate'=>$cate, 'BusinessModel'=>$list]], 256);
    }

    /**
     * 业务详情
     * $data 2019/11/19 16:26
     */
    public function detail(){

    	// 获取业务id
        $cate_id = input('id');

        if ($cate_id && $detail = (new BusinessModel())->find($cate_id)) {
            $Verify = new Verify();
            $detail['content'] = $Verify->replaceImg($detail['content']);
        	return json(['code' => 1,'msg'=> '请求成功', 'data'=>$detail], 256);
        } else {
        	return json(['code' => 0, 'msg'=>'该业务不存在'], 256);
        }
    }

    /**
     * 预约报名
     * $data 2019/11/20
     */
    public function enroll()
    {
        # code...
        $list = (new ApiBus())->getAllBusiness();
        
        return json(['code' => 1,'msg'=> '请求成功', 'data'=>$list], 256);
    }

    /**
     * 报名提交
     * $data 2019/11/20
     */
    public function submit()
    {
        # code...
        $list = (new ApiBusOrder())->add();
        
        return json($list, 256);
    }
}