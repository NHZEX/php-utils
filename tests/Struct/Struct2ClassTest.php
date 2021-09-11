<?php

namespace Zxin\Tests\Struct;

use Zxin\Stub\DataStructV2Stub;
use Zxin\Tests\Base;

class Struct2ClassTest extends Base
{
    public function testAll()
    {
        $stub = new DataStructV2Stub();
        $this->assertEquals([
            'pub' => 1,
            'pubHidden' => 456,
        ], $stub->all());
    }

    public function testToArray()
    {
        $stub = new DataStructV2Stub();
        $this->assertEquals([
            'pub' => 1,
        ], $stub->toArray());
    }

    public function testPropNull()
    {
        $stub = new DataStructV2Stub();
        $stub->pub = null;
        $this->assertEquals([
            'pub' => null,
        ], $stub->toArray());
    }

    public function testPropDynamic()
    {
        $stub = new DataStructV2Stub();
        /** @noinspection PhpUndefinedFieldInspection */
        $stub->dynamic = 123;
        $this->assertEquals([
            'pub' => 1,
            'dynamic' => 123,
        ], $stub->toArray());
    }

    public function testPropExistCheck()
    {
        $ref = new \ReflectionClass(DataStructV2Stub::class);
        /** @var DataStructV2Stub $stub */
        $stub = $ref->newInstanceWithoutConstructor();
        $stub->setPropExistCheck(true);
        $stub->__construct([
            'dynamic' => 123,
            'pub'     => 456,
        ]);
        $this->assertEquals([
            'pub' => 456,
        ], $stub->toArray());

        /** @noinspection PhpUndefinedFieldInspection */
        $stub->dynamic = 1234;
        $this->assertFalse(isset($stub->dynamic));

        unset($stub->pub);
        $this->assertFalse(isset($stub->pub));
    }
}
