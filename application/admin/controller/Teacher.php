<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Validate;

class Teacher extends Base
{
    public function index()
    {
        $list = (new \app\common\model\Teacher())->where(['delete_time'=>0])
            ->order('id','desc')->paginate(10);

        $this->assign('list',$list);

        return $this->fetch();
    }

    public function add()
    {
        return $this->fetch();
    }

    public function save(Request $request)
    {
        $post = $request->post();

        $rules = [
            'name|姓名'  => 'require|max:30',
            'pic|照片'    => 'require',
            'desc|简介'   => 'require|max:128',
            'content|介绍' => 'require',
        ];

        $validate = new Validate($rules);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        $data = [
            'name'  => $post['name'],
            'pic'  => $post['pic'],
            'desc'  => $post['desc'],
            'content'  => $post['content'],
            'create_time' => time(),
        ];

        (new \app\common\model\Teacher())->insert($data);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function edit(Request $request)
    {
        $id = $request->param('id');

        $data = (new \app\common\model\Teacher())->find($id);

        $this->assign('data',$data);

        return $this->fetch();

    }

    public function update(Request $request)
    {
        $post = $request->post();

        $rules = [
            'name|姓名'  => 'require|max:30',
            'pic|照片'    => 'require',
            'desc|简介'   => 'require|max:128',
            'content|介绍' => 'require',
        ];

        $validate = new Validate($rules);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        $data = [
            'name'  => $post['name'],
            'pic'  => $post['pic'],
            'desc'  => $post['desc'],
            'content'  => $post['content'],
        ];

        (new \app\common\model\Teacher())->where(['id'=>$post['id']])->update($data);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function delete(Request $request)
    {
        $id = $request->post('id');

        (new \app\common\model\Teacher())->where(['id'=>$id])->update(['delete_time'=>time()]);

        return json(['code'=>1,'msg'=>'success']);

    }
}