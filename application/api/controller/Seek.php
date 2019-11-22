<?php
/**
 * Created by PhpStorm.
 * User: xcf
 * Date: 2019/11/19
 * Time: 16:25
 */
namespace app\api\controller;

use think\Controller;
use app\common\model\Seek as SeekModel;
use app\common\lib\Verify;

class Seek extends Controller
{
    /**
     * 咨询列表
     * $data 2019/11/19 16:26
     */
    public function getList(){

        //获取咨询列表
        $strip = input('strip')?input('strip'):10;
        $list = (new SeekModel())->where(['delete_time'=>0])->order('id','desc')->paginate($strip)->each(function($item, $key){
                            $item['pic'] = config('app.localhost_path').$item['pic'];
                          });

        return json(['code' => 1,'msg'=> '请求成功', 'data'=>$list], 256);
    }

    /**
     * 咨询详情
     * $data 2019/11/19 16:26
     */
    public function detail(){

    	// 获取咨询id
        $cate_id = input('id');

        if ($cate_id && $detail = (new SeekModel())->find($cate_id)) {
            $Verify = new Verify();
            $detail['content'] = $Verify->replaceImg($detail['content']);
            $detail['pic'] = config('app.localhost_path').$detail['pic'];
        	return json(['code' => 1,'msg'=> '请求成功', 'data'=>$detail], 256);
        } else {
        	return json(['code' => 0, 'msg'=>'咨询不存在'], 256);
        }
    }
}