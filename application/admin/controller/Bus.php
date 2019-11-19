<?php

namespace app\admin\controller;

use app\common\model\Category;
use app\common\typeCode\cate\Business;
use think\Controller;
use think\Request;
use app\common\model\Business as BusModel;
use think\Validate;

//业务类
class Bus extends Base
{
    public function index()
    {

        $list = (new BusModel())->where(['delete_time'=>0])->order('id','desc')->paginate(10);

        $this->assign('list',$list);


        return $this->fetch();

    }

    public function add()
    {
        $cate = (new Category())->getList((new Business()));

        $this->assign('cate',$cate);

        return $this->fetch();
    }

    public function save(Request $request)
    {
        $data = $request->post();

        $rules = [
            'cate_id|分类'   => 'require',
            'title|标题'      => 'require|max:64',
            'content|内容'    => 'require',
            '__token__|. '   => 'token',
        ];

        $validate = new Validate($rules);

        if (!$validate->check($data)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        $data = [
            'cate_id'   => $data['cate_id'],
            'title'     => $data['title'],
            'content'   => $data['content'],
            'create_time'   => time(),
        ];

        (new \app\common\model\Business())->insert($data);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function edit(Request $request)
    {
        $id = $request->param('id');

        $data = (new \app\common\model\Business())->find($id);

        $cate = (new Category())->getList((new Business()));

        $this->assign('cate',$cate);

        $this->assign('data',$data);

        return $this->fetch();
    }

    public function update(Request $request)
    {
        $data = $request->post();

        $rules = [
            'id|非法'        => 'require',
            'cate_id|分类'   => 'require',
            'title|标题'      => 'require|max:64',
            'content|内容'    => 'require',
            '__token__|. '   => 'token',
        ];

        $validate = new Validate($rules);

        if (!$validate->check($data)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        $update = [
            'cate_id'   => $data['cate_id'],
            'title'     => $data['title'],
            'content'   => $data['content'],
        ];

        (new \app\common\model\Business())->where(['id'=>$data['id']])->update($update);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function delete(Request $request)
    {
        $id = $request->post('id');

        (new \app\common\model\Business())->where(['id'=>$id])->update(['delete_time'=>time()]);

        return json(['code'=>1,'msg'=>'success']);
    }
}
