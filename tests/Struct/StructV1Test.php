<?php
declare(strict_types=1);

namespace Zxin\Tests\Struct;

use Zxin\Stub\DataImmutableStructStub;
use Zxin\Stub\DataStructV1Stub;
use Zxin\Tests\Base;

class StructV1Test extends Base
{
    public function testAll()
    {
        $stub = new DataStructV1Stub();
        $this->assertEquals([
            'pub' => 1,
            'pubHidden' => 456,
        ], $stub->all());
    }

    public function testToArray()
    {
        $stub = new DataStructV1Stub();
        $this->assertEquals([
            'pub' => 1,
        ], $stub->toArray());
    }

    public function testPropNull()
    {
        $stub = new DataStructV1Stub();
        $stub->pub = null;
        $this->assertEquals([
            'pub' => null,
        ], $stub->toArray());
    }

    public function testPropDynamic()
    {
        $stub = new DataStructV1Stub();
        /** @noinspection PhpUndefinedFieldInspection */
        $stub->dynamic = 123;
        $this->assertEquals([
            'pub' => 1,
            'dynamic' => 123,
        ], $stub->toArray());
    }

    public function testPropExistCheck()
    {
        $ref = new \ReflectionClass(DataStructV1Stub::class);
        /** @var DataStructV1Stub $stub */
        $stub = $ref->newInstanceWithoutConstructor();
        $stub->setPropExistCheck(true);
        $stub->__construct([
            'dynamic' => 123,
            'pub'     => 456,
        ]);
        $this->assertEquals([
            'pub' => 456,
        ], $stub->toArray());

        $stub['dynamic'] = 1234;
        $this->assertFalse(isset($stub->dynamic));

        /** @noinspection PhpUndefinedFieldInspection */
        $stub->dynamic = 1234;
        $this->assertFalse(isset($stub->dynamic));

        unset($stub->pub);
        $this->assertFalse(isset($stub->pub));
    }

    public function testImmutableStruct()
    {
        $stub = new DataImmutableStructStub([
            'dynamic' => 123,
            'pub'     => 456,
        ]);
        $this->assertEquals([
            'pub' => 456,
        ], $stub->toArray());

        $stub['dynamic'] = 1234;
        $this->assertFalse(isset($stub->dynamic));

        /** @noinspection PhpUndefinedFieldInspection */
        $stub->dynamic = 1234;
        $this->assertFalse(isset($stub->dynamic));

        unset($stub->pub);
        $this->assertFalse(isset($stub->pub));
    }
}
