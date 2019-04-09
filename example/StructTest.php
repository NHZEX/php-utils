<?php
declare(strict_types=1);

namespace Example;

use HZEX\DataStruct\Base;
use HZEX\UnitConvertor\RenMinBi;
use ReflectionException;
use stdClass;

/**
 * 支付对象
 * Class StructChargeObject
 * @package app\logic\payment\struct
 * @property bool     $is_success            [read] 是否成功
 * @property string   $body                  [read] 字符串内容
 * @property int      $expire_time           [read] 失效时间
 * @property array    $array                 [read] 数组内容
 * @property Test123  $test0                 [read] 继承测试1
 * @property Test123  $test1                 [read] 类测试1(相同命名空间)
 * @property RenMinBi $test2                 [read] 类测试2(不同命名空间)
 * @property Test0    $test3                 [read] 接口测试3
 * @property iterable $test4                 [read] 迭代类测试
 * @property stdClass $test5                 [read] 系统类测试
 */
class StructTest extends Base
{
    /**
     * 数据结构初始化
     * @throws ReflectionException
     */
    protected function initialize(): void
    {
        // 加载结构规则
        $this->loadRule();
    }
}
