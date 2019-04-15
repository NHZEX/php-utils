<?php

namespace HZEX\Stub;

use Closure;
use DateTime;
use HZEX\DataStruct\DataStruct;
use stdClass;

class DataStructStub extends DataStruct
{
    /** @var bool 布尔值 */
    public $bool;
    /** @var int 整数值 */
    public $int;
    /** @var float 浮点数 */
    public $float;
    /** @var string 字符串 */
    public $string;
    /** @var array 数组 */
    public $array;
    /** @var iterable 可枚举 */
    public $iterable;
    /** @var object 任意对象 */
    public $object;
    // /** @var callable 匿名函数*/
    // public $callable;
    /** @var Closure 匿名函数类 */
    public $closure;
    /** @var resource 任意资源 */
    public $resource;
    /** @var mixed 任意类型值 */
    public $mixed;
    /** @var stdClass 系统类测试1 */
    public $stdClass;
    /** @var DateTime 系统类测试2 */
    public $dateTime;
    /** @var EmptyClassA 自定义类测试 */
    public $myClass;
    /** @var EmptyInterface 自定义接口测试 */
    public $myInterface;
    /** @var int[] 类型数组1 */
    public $myArray1;
    /** @var DateTime[] 类型数组2 */
    public $myArray2;
    /** @var int {read} 只读测试 */
    public $readTest;
    /** @var int {hide} 输出隐藏测试 */
    public $hideTest;
}
