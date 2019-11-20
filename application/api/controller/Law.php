<?php
/**
 * Created by PhpStorm.
 * User: xcf
 * Date: 2019/11/19
 * Time: 16:25
 */
namespace app\api\controller;

use think\Controller;
use app\common\model\Law as LawModel;
use app\common\typeCode\cate\Law as CateType;
use app\common\model\Category as CategoryModel;


class Law extends Controller
{
    /**
     * 律所列表
     * $data 2019/11/19 16:26
     */
    public function getList(){

    	//获取律所分类
        $categoryModel = new CategoryModel();
        $cate = $categoryModel->getList((new CateType()));
        if (!$cate) return json(['code' => 0, 'msg'=>'分类为空'], 256);

        //获取律所列表
        //获取请求分类下律所id
        $cate_id = input('cate_id')?input('cate_id'):$cate[0]['id'];
        $list = (new LawModel())->where(['cate_id'=>$cate_id])->order('id','desc')->paginate(10);

        return json(['code' => 1, 'data'=>['cate'=>$cate, 'law'=>$list]], 256);
    }

    /**
     * 律所详情
     * $data 2019/11/19 16:26
     */
    public function detail(){

    	// 获取律所id
        $cate_id = input('id');

        if ($cate_id && $detail = (new LawModel())->find($cate_id)) {

        	return json(['code' => 1, 'data'=>$detail], 256);
        } else {
        	return json(['code' => 0, 'msg'=>'律所不存在'], 256);
        }
    }
}