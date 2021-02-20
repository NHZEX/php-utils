<?php
/** @noinspection PhpUnusedPrivateFieldInspection */

namespace Zxin\Stub;

use Zxin\DataStruct\BaseProperty;

class DataStructV1Stub extends BaseProperty
{
    public $pub  = 1;
    protected $prot = 2;
    private $priv = 3;

    public $pubHidden  = 456;

    protected function initialize(): void
    {
        $this->hidden(['pubHidden']);
    }

    /**
     * @param bool $propExistCheck
     */
    public function setPropExistCheck(bool $propExistCheck): void
    {
        $this->propExistCheck = $propExistCheck;
    }
}
