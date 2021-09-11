<?php

namespace Zxin\Stub;

use Zxin\DataStruct\BaseStruct;

class DataStructV2Stub extends BaseStruct
{
    public    $pub  = 1;
    protected $protected1 = 2;
    private   $private1 = 3;

    public $pubHidden = 456;

    public function getHiddenKey(): array
    {
        return ['pubHidden'];
    }
}
