<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/22
 * Time: 15:58
 */

namespace app\common\lib;

use think\Request;
class Verify
{
    /**
     * 将富文本编辑器的相对地址替换为绝对地址
     * $data 2019/11/22 15:59
     */
    public function replaceImg($value){
        //替换img src路径前加上 当前域名 /a.jpg -> http://www.xxx.com/a.jpg
        $http = request()->domain();
        $regex = "/src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?/";
        $src = "src=".$http.'${1}';
        return $content =  preg_replace($regex, $src, $value);
    }
}