<?php
declare(strict_types=1);

namespace HZEX\Stub;

use DateTime;
use HZEX\DataStruct\Base;
use stdClass;

/**
 * 结构基本测试对象
 * Class StructBaseTest
 * @package HZEX\Stub
 * @property bool        $bool       布尔值
 * @property int         $int        整数值
 * @property float       $float      浮点数
 * @property string      $string     字符串
 * @property array       $array      数组
 * @property iterable    $iterable   可枚举
 * @property object      $object     任意对象
 * @property callable    $callable   匿名函数
 * @property resource    $resource   资源
 * @property mixed       $mixed      任意类型值
 * @property stdClass    $class1     系统类测试
 * @property DateTime    $dateTime   系统类测试2
 * @property EmptyClassA $myClass    自定义类测试
 * @property int         $readOnly   [read] 只读测试
 */
class StructBaseTest extends Base
{
    protected $strictMode = true;

    protected function initialize(): void
    {
    }
}
