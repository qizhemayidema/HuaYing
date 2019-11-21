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
        $list = (new \app\common\model\Comment())->alias('comment')
            ->join('video video','video.id = comment.public_id and comment.type = 1')
            ->order('comment.id','desc')
            ->field('video.title class_name,comment.*')
            ->paginate(15);

        $this->assign('list',$list);

        return $this->fetch();
    }

    public function changeStatus(Request $request)
    {
        $commentId = $request->post('comment_id');


        $commentModel = (new \app\common\model\Comment());

        $comm = $commentModel->find($commentId);

        if ($comm){
            $flag = $comm->is_show ? 0 : 1;

            $commentModel->where(['id'=>$commentId])->update(['is_show'=>$flag]);
        }

        return ['code'=>1,'msg'=>'success'];
    }
}
