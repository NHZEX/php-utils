<?php

namespace HZEX\Tests\Struct;

use DateTime;
use HZEX\DataStruct\StructReadOnlyException;
use HZEX\DataStruct\StructTypeException;
use HZEX\DataStruct\StructUndefinedException;
use HZEX\Stub\EmptyClassA;
use HZEX\Stub\EmptyClassAb;
use HZEX\Stub\StructBaseTest;
use PHPUnit\Framework\TestCase;
use stdClass;

class StructTest extends TestCase
{
    private function emptyFun()
    {
        return function () {
        };
    }

    /**
     * 测试结构构造初始赋值
     */
    public function testConstruct()
    {
        $struct = new StructBaseTest([
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
        $struct = new StructBaseTest();
        $struct->bool = true;
        $struct->int = 123;
        $struct->float = 1.23;
        $struct->float = 123456; // 扩大原始转换测试
        $struct->string = '123';
        $struct->array = [];
        $struct->iterable = [];
        $struct->object = (object) [];
        $struct->callable = $this->emptyFun();
        $struct->resource = tmpfile();
        $struct->mixed = 123;
        $struct->mixed = '123';
        $struct->class1 = new stdClass();
        $struct->dateTime = new DateTime();
        $struct->myClass = new EmptyClassA();
        $struct->myClass = new EmptyClassAb(); // 继承测试
        $struct->myInter = new EmptyClassAb(); // 接口测试

        $this->assertNotEmpty($struct->toArray());
    }

    /**
     * 测试更改计数
     */
    public function testDataChangeCount()
    {
        $struct = new StructBaseTest();
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
    public function testReadOnly()
    {
        $this->expectException(StructReadOnlyException::class);
        $struct = new StructBaseTest();
        $struct->readOnly = 123;
        $struct->readOnly = 456;
    }

    /**
     * 测试属性未定义异常
     */
    public function testStructUndefinedException()
    {
        $this->expectException(StructUndefinedException::class);
        new StructBaseTest([
            'null' => null
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
        $struct = new StructBaseTest();
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
