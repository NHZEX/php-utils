<?php
declare(strict_types=1);

namespace Zxin\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionException;
use Zxin\Stub\ClassC;
use function Zxin\ref_get_prop;

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
