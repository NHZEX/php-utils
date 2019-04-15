<?php

namespace HZEX\Tests\Struct;

use Closure;
use DateTime;
use HZEX\DataStruct\StructReadOnlyException;
use HZEX\DataStruct\StructTypeException;
use HZEX\DataStruct\StructUndefinedException;
use HZEX\Stub\DataStructStub;
use HZEX\Stub\EmptyClassA;
use HZEX\Stub\EmptyClassAb;
use PHPUnit\Framework\TestCase;
use stdClass;

class StructV2Test extends TestCase
{
    private function emptyFun()
    {
        return function () {
        };
    }

    private function emptyFunObject()
    {
        return Closure::fromCallable($this->emptyFun());
    }

    /**
     * 测试结构构造初始赋值
     */
    public function testConstruct()
    {
        $struct = new DataStructStub([
            'bool' => false,
            'int' => 456,
            'float' => 4.56,
            'string' => '456',
            'array' => [],
        ]);

        $this->assertNotEmpty($struct->toArray());
    }

    /**
     * 测试赋值
     */
    public function testAssignment()
    {
        $struct = new DataStructStub();
        $struct->bool = true;
        $struct->int = 123;
        $struct->float = 1.23;
        $struct->float = 123456; // 扩大原始转换测试
        $struct->string = '123';
        $struct->array = [];
        $struct->iterable = [];
        $struct->object = (object) [];
        // $struct->callable = $this->emptyFun();
        $struct->closure = $this->emptyFunObject();
        $struct->resource = tmpfile();
        $struct->mixed = 123;
        $struct->mixed = '123';
        $struct->stdClass = new stdClass();
        $struct->dateTime = new DateTime();
        $struct->myClass = new EmptyClassA();
        $struct->myClass = new EmptyClassAb(); // 继承测试
        $struct->myInterface = new EmptyClassAb(); // 接口测试

        $this->assertNotEmpty($struct->toArray());
    }

    /**
     * 测试更改计数
     */
    public function testDataChangeCount()
    {
        $struct = new DataStructStub();
        $this->assertEquals(0, $struct->getDataChangeCount());
        $struct->int = 1;
        $struct->int = 2;
        $struct->int = 2;
        $this->assertEquals(2, $struct->getDataChangeCount());
        $struct->int = 3;
        $this->assertEquals(3, $struct->getDataChangeCount());
    }

    /**
     * 测试只读控制
     */
    public function testPropRead()
    {
        $this->expectException(StructReadOnlyException::class);
        $struct = new DataStructStub();
        $struct->readTest = 123;
        $struct->readTest = 456;
    }

    /**
     * 测试隐藏控制
     */
    public function testPropHide()
    {
        $struct = new DataStructStub([
            'bool' => false,
            'int' => 456,
            'string' => '456',
            'hideTest' => 456,
        ]);

        $this->assertEquals(456, $struct->hideTest);
        $this->assertFalse(isset($struct->toArray()['hideTest']));
    }

    /**
     * 测试属性未定义异常
     */
    public function testStructUndefinedException()
    {
        $this->expectException(StructUndefinedException::class);
        new DataStructStub([
            'Undefined' => null
        ]);
    }

    /**
     * 测试类型校验异常
     * @param $value
     * @dataProvider structExceptionProvider
     */
    public function testStructTypeException($value)
    {
        $this->expectException(StructTypeException::class);
        $struct = new DataStructStub();
        $struct->string = $value;
    }

    public function structExceptionProvider()
    {
        return [
            [132],
            [false],
            [new DateTime()],
        ];
    }
}
