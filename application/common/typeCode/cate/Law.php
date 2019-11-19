<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/11/18
 * Time: 17:33
 */

namespace app\common\typeCode\cate;


use app\common\typeCode\impl\CateImpl;

class Law implements CateImpl
{
    private $cateType = 2;

    private $cateCacheName = "law_cate";

    private $levelType = 'one';

    public function getCateType() : int
    {
        // TODO: Implement getCateType() method.

        return $this->cateType;
    }

    public function getCateCacheName(): string
    {
        // TODO: Implement getCateCacheName() method.

        return $this->cateCacheName;
    }

    public function getLevelType(): string
    {
        // TODO: Implement getLevelType() method.

        return $this->levelType;
    }
}