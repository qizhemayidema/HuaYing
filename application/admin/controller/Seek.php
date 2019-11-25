<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Validate;

class Seek extends Base
{
    public function index()
    {
        $list = (new \app\common\model\Seek())->where(['delete_time'=>0])
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
            'title|标题' => 'require|max:64',
            'name|联系人' => 'require|max:30',
            'phone|联系电话' => 'require|max:30',
            'pic|封面' => 'require',
            'desc|简介' => 'require|max:256',
            'content|内容' => 'require',
            'price|价格' => 'require|number',

        ];

        $validate = new Validate($rules);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        $data = [
            'title' => $post['title'],
            'pic' => $post['pic'],
            'desc' => $post['desc'],
            'content' => $post['content'],
            'price' => $post['price'],
            'name'  => $post['name'],
            'phone' => $post['phone'],
            'create_time' => time(),
        ];

        (new \app\common\model\Seek())->insert($data);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function edit(Request $request)
    {
        $id = $request->param('id');

        $data = (new \app\common\model\Seek())->find($id);

        $this->assign('data',$data);

        return $this->fetch();
    }

    public function update(Request $request)
    {
        $post = $request->post();

        $rules = [
            'title|标题' => 'require|max:64',
            'pic|封面' => 'require',
            'name|联系人' => 'require|max:30',
            'phone|联系电话' => 'require|max:30',
            'desc|简介' => 'require|max:256',
            'content|内容' => 'require',
            'price|价格' => 'require',
        ];

        $validate = new Validate($rules);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        $data = [
            'title' => $post['title'],
            'pic' => $post['pic'],
            'desc' => $post['desc'],
            'content' => $post['content'],
            'name'  => $post['name'],
            'phone' => $post['phone'],
            'price' => $post['price'],
        ];

        (new \app\common\model\Seek())->where(['id'=>$post['id']])->update($data);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function delete(Request $request)
    {
        $id = $request->post('id');

        (new \app\common\model\Seek())->where(['id'=>$id])->update(['delete_time'=>time()]);

        return json(['code'=>1,'msg'=>'success']);

    }
}
