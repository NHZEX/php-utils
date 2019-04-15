<?php

namespace HZEX\Stub;

use HZEX\DataStruct\DataStruct;

class DataStructStub extends DataStruct
{
    /** @var bool 布尔值 */
    public $bool = false;
    /** @var ?int 整数值 */
    public $int = null;
    /** @var float 浮点数 */
    public $float = 1.1;
    /** @var string 字符串 */
    public $string = '1\'23';
    /** @var array 数组 */
    public $array = [];
}
