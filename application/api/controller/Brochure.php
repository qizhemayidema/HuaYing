<?php
/**
 * Created by PhpStorm.
 * User: xcf
 * Date: 2019/11/19
 * Time: 16:25
 */
namespace app\api\controller;

use think\Controller;
use app\common\model\Brochure as BrochureModel;
use app\common\typeCode\cate\Brochure as CateType;
use app\common\model\Category as CategoryModel;


class Brochure extends Controller
{
    /**
     * 分校列表
     * $data 2019/11/19 16:26
     */
    public function getList(){

    	//获取分校分类
        $categoryModel = new CategoryModel();
        $cate = $categoryModel->getList((new CateType()));
        if (!$cate) return json(['code' => 0, 'msg'=>'分类为空'], 256);

        //获取分校列表
        $strip = input('strip')?input('strip'):12;
        //获取请求分类下分校id
        $cate_id = input('cate_id')?input('cate_id'):$cate[0]['id'];
        $list = (new BrochureModel())->where(['cate_id'=>$cate_id])->order('id','desc')->paginate($strip)->each(function($item, $key){
                            $item['pic'] = config('app.localhost_path').$item['pic'];
                            $item['create_time'] = date("Y-m-d H:i:s", $item['create_time']);
                          });

        return json(['code' => 1,'msg'=> '请求成功', 'data'=>['cate'=>$cate, 'brochureModel'=>$list]], 256);
    }

    /**
     * 分校详情
     * $data 2019/11/19 16:26
     */
    public function detail(){

    	// 获取分校id
        $cate_id = input('id');

        if ($cate_id && $detail = (new BrochureModel())->find($cate_id)) {

            $detail['pic'] = config('app.localhost_path').$detail['pic'];
            $detail['create_time'] = date("Y-m-d H:i:s", $detail['create_time']);
        	return json(['code' => 1, 'msg'=> '请求成功', 'data'=>$detail], 256);
        } else {
        	return json(['code' => 0, 'msg'=>'分校不存在'], 256);
        }
    }
}