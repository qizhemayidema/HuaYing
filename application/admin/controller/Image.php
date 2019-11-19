<?php

namespace app\admin\controller;

use app\common\lib\Upload;
use think\Controller;
use think\Request;
use think\Validate;

class Image extends Base
{
    public function index()
    {
        $image = (new \app\common\model\Image())->order('id','desc')->paginate(15);

        $this->assign('image',$image);

        return $this->fetch();
    }

    public function add()
    {
        return $this->fetch();
    }

    public function save(Request $request)
    {
        $post = $request->post();

        $rule = [
            'type|选择类型'   => 'require',
            'pic|图片'    => 'require',
        ];

        $validate = new Validate($rule);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        $data = [
            'type'  => $post['type'],
            'url'  => $post['pic'],
        ];

        (new \app\common\model\Image())->insert($data);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function edit(Request $request)
    {
        $id = $request->param('id');

        $data = (new \app\common\model\Image())->find($id);

        $this->assign('data',$data);

        return $this->fetch();


    }

    public function update(Request $request)
    {
        $post = $request->post();

        $rule = [
            'type|选择类型'   => 'require',
            'pic|图片'    => 'require',
        ];

        $validate = new Validate($rule);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        $data = [
            'type'  => $post['type'],
            'url'  => $post['pic'],
        ];

        (new \app\common\model\Image())->where(['id'=>$post['id']])->update($data);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function delete(Request $request)
    {
        $id = $request->post('id');

        (new \app\common\model\Image())->where(['id'=>$id])->delete();

        return json(['code'=>1,'msg'=>'success']);
    }

    public function uploadPic(Request $request)
    {
        return (new Upload())->uploadOnePic('image/');
    }
}
