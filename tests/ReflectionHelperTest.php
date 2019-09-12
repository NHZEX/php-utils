<?php
declare(strict_types=1);

namespace HZEX\Tests;

use HZEX\Stub\ClassC;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use function HuangZx\ref_get_prop;

class ReflectionHelperTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testRefGetProp()
    {
        $c = new ClassC();
        $prop = ref_get_prop($c, 'test');
        $this->assertEquals($c->getTest(), $prop->getValue());
        $prop->setValue('789456');
        $this->assertEquals('789456', $c->getTest());
    }
}
