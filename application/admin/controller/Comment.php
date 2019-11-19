<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class Comment extends Base
{
    public function teacher()
    {
        $list = (new \app\common\model\Comment())->alias('comment')
            ->join('teacher teacher','teacher.id = comment.public_id and comment.type = 2')
            ->order('comment.id','desc')
            ->field('teacher.name teacher_name,comment.*')
            ->paginate(15);

        $this->assign('list',$list);

        return $this->fetch();
    }

    public function class()
    {
//        (new \app\common\model\Comment())->alias('comment')
//            ->join('')
//        return $this
    }
}
