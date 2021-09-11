<?php

namespace Zxin\Stub;

use Zxin\DataStruct\BaseStruct;

class DataStructV2Stub extends BaseStruct
{
    public    $pub  = 1;
    protected $protected1 = 2;
    private   $private1 = 3;

    public $pubHidden = 456;

    /** @var bool */
    private $propExistCheck = false;


    /**
     * @param bool $propExistCheck
     */
    public function setPropExistCheck(bool $propExistCheck): void
    {
        $this->propExistCheck = $propExistCheck;
    }

    public function checkPropExist(): bool
    {
        return $this->propExistCheck;
    }

    public function getHiddenKey(): array
    {
        return ['pubHidden'];
    }
}
