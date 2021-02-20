<?php
/** @noinspection PhpUnusedPrivateFieldInspection */

namespace Zxin\Stub;

use Zxin\DataStruct\ImmutableStruct;

class DataImmutableStructStub extends ImmutableStruct
{
    public $pub  = 1;
    protected $prot = 2;
    private $priv = 3;

    public $pubHidden  = 456;

    protected function initialize(): void
    {
        $this->hidden(['pubHidden']);
    }
}
