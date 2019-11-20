<?php
/**
 * Created by PhpStorm.
 * User: xcf
 * Date: 2019/11/20
 */

namespace app\api\model;

use think\Db;
use think\Model;

class ApiBus extends Model
{
    protected  $table="base_business";


    /**
     * 获取所有业务
     * $data 2019/11/20
     */
    public function getAllBusiness()
    {

    	$where[] = ['type', '=', 1];
    	$list = Db::name('category')->field('name,id')->where($where)->select();

    	// 组成搜索条件
    	$map = ['delete_time'=>0];
    	foreach ($list as $k => &$v) {
    		
    		$map['cate_id'] = $v['id'];
    		$v['bus'] = $this->where($map)->field('id,title')->select()->toArray();
    	}

        return $list;
    }

}