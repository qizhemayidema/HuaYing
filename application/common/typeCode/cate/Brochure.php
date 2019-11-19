<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/11/18
 * Time: 19:07
 */

namespace app\common\typeCode\cate;


use app\common\typeCode\impl\CateImpl;

class Brochure implements CateImpl
{
    private $cateType = 4;

    private $cateCacheName = "brochure_cate";

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