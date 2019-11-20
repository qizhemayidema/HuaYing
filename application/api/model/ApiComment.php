<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/19
 * Time: 22:10
 */

namespace app\api\model;

use  think\Model;
class ApiComment extends Model
{
    protected  $table = "base_comment";

    /**
     * 某课程的评论
     * @param $id       int   评论的id
     * @param string $limits
     * @return array
     * $data 2019/11/19 22:13
     */
    public function getList($id,$limits=''){
        $where[] = ['public_id','=',$id];
        $where[] = ['type','=',1];
        $where[] = ['is_show','=',1];
        return $this->where($where)->order('create_time desc')->limit($limits)->select()->toArray();
    }

    /**
     * 某老师的评论
     * @param $id       int  老师id
     * @param string $limits
     * $data 2019/11/20 13:54
     */
    public function getTeacherList($id,$limits=''){
        $where[] = ['public_id','=',$id];
        $where[] = ['type','=',2];
        $where[] = ['is_show','=',1];
        return $this->where($where)->order('create_time desc')->limit($limits)->select()->toArray();
    }

    public function addComment($data){
        return $this->insert($data);
    }
}