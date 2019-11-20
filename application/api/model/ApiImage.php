<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/19
 * Time: 16:31
 */
namespace app\api\model;

use think\Model;
class ApiImage extends Model
{
    protected  $table = "base_image";
    /**
     * 查找对应类别下的图片
     * @param $type
     * @param string $fields   返回的字段
     * @return array
     * $data 2019/11/19 16:47
     */
    public function getBanner($type,$fields='',$limits=''){
        return $this->where(['type'=>$type])->field($fields)->limit($limits)->select()->toArray();
    }
}