<?php

namespace app\admin\controller;

use app\common\lib\Upload;
use app\common\model\Category;
use think\Controller;
use think\Request;

class Video extends Base
{

    public function index()
    {
        return $this->fetch();
    }

    public function add()
    {
        //分类
        $type = new \app\common\typeCode\cate\Video();
        $cate = (new Category())->getList($type);

        //作者
        $author = (new \app\common\model\Teacher())->order('id','desc')->select()->toArray();

        $this->assign('cate',$cate);

        $this->assign('author',$author);

        return $this->fetch();
    }

    public function save()
    {

    }

    public function edit()
    {

    }

    public function update()
    {

    }

    public function delete()
    {

    }

    public function uploadPic()
    {
        $path = 'video/';
        return (new Upload())->uploadOnePic($path);
    }
}
