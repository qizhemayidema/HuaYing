<?php

namespace app\admin\controller;

use app\common\lib\Upload;
use app\common\model\Category;
use think\Request;
use app\common\model\Law as LawModel;
use think\Validate;


class Law extends Base
{
    public function index()
    {
        $list = (new LawModel())->order('id','desc')->paginate(10);

        $this->assign('list',$list);

        return $this->fetch();

    }

    public function add()
    {
        $cate = (new Category())->getList((new \app\common\typeCode\cate\Law()));

        $this->assign('cate',$cate);

        return $this->fetch();
    }


    public function save(Request $request)
    {
        $post = $request->post();

        $rules = [
            'cate_id|分类' => 'require',
            'name|名称' => 'require|max:64',
            'phone|联系方式' => "require|max:20",
            'pic|封面图'   => 'require',
            'address|地址' => 'require|max:128',
            'content|介绍' => 'require',
        ];

        $validate = new Validate($rules);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        $insert = [
            'cate_id' => $post['cate_id'],
            'name'  => $post['name'],
            'phone'  => $post['phone'],
            'address'  => $post['address'],
            'pic'  => $post['pic'],
            'content'  => $post['content'],
            'create_time' => time(),
        ];

        (new \app\common\model\Law())->insert($insert);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function edit(Request $request)
    {
        $id = $request->param('id');

        $data = (new \app\common\model\Law())->find($id);

        $cate = (new Category())->getList((new \app\common\typeCode\cate\Law()));

        $this->assign('cate',$cate);

        $this->assign('data',$data);

        return $this->fetch();
    }

    public function update(Request $request)
    {
        $post = $request->post();

        $rules = [
            'cate_id|分类' => 'require',
            'name|名称' => 'require|max:64',
            'phone|联系方式' => "require|max:20",
            'pic|封面图'   => 'require',
            'address|地址' => 'require|max:128',
            'content|介绍' => 'require',
        ];

        $validate = new Validate($rules);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        $update = [
            'cate_id' => $post['cate_id'],
            'name'  => $post['name'],
            'phone'  => $post['phone'],
            'address'  => $post['address'],
            'pic'  => $post['pic'],
            'content'  => $post['content'],
        ];

        (new \app\common\model\Law())->where(['id'=>$post['id']])->update($update);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function delete(Request $request)
    {
        $id = $request->post('id');

        (new \app\common\model\Law())->where(['id'=>$id])->delete();

        return json(['code'=>1,'msg'=>'success']);
    }

    public function uploadPic()
    {
        $path = 'law/';
        return (new Upload())->uploadOnePic($path);
    }
}