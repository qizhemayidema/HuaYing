<?php

namespace app\admin\controller;

use app\common\lib\Upload;
use think\Controller;
use think\Request;
use app\common\model\Brochure as BrochureModel;
use app\common\model\Category;
use app\common\typeCode\cate\Brochure as CateType;
use think\Validate;

class Brochure extends Base
{
    public function index()
    {
        $list = (new BrochureModel())->order('id','desc')->paginate(10);

        $this->assign('list',$list);

        return $this->fetch();

    }

    public function add()
    {
        $cate = (new Category())->getList((new CateType()));

        $this->assign('cate',$cate);

        return $this->fetch();
    }


    public function save(Request $request)
    {
        $post = $request->post();

        $rules = [
            'cate_id|分类' => 'require',
            'name|名称' => 'require|max:64',
            'pic|封面图'   => 'require',
            'content|内容' => 'require',
        ];

        $validate = new Validate($rules);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        $insert = [
            'cate_id' => $post['cate_id'],
            'name'  => $post['name'],
            'pic'  => $post['pic'],
            'content'  => $post['content'],
            'create_time' => time(),
        ];

        (new BrochureModel())->insert($insert);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function edit(Request $request)
    {
        $id = $request->param('id');

        $data = (new BrochureModel())->find($id);

        $cate = (new Category())->getList(new CateType());

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
            'pic|封面图'   => 'require',
            'content|内容' => 'require',
        ];

        $validate = new Validate($rules);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        $update = [
            'cate_id' => $post['cate_id'],
            'name'  => $post['name'],
            'pic'  => $post['pic'],
            'content'  => $post['content'],
        ];

        (new BrochureModel())->where(['id'=>$post['id']])->update($update);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function delete(Request $request)
    {
        $id = $request->post('id');

        (new BrochureModel())->where(['id'=>$id])->delete();

        return json(['code'=>1,'msg'=>'success']);
    }

    public function uploadPic()
    {
        $path = 'brochure/';
        return (new Upload())->uploadOnePic($path);
    }
}