<?php
declare(strict_types=1);

namespace Zxin\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;
use function version_compare;

abstract class Base extends TestCase
{
    protected function proxyAssertDoesNotMatchRegularExpression(string $pattern, string $string, string $message = '')
    {
        if (version_compare(Version::id(), '9.1', '>=')) {
            $this->assertDoesNotMatchRegularExpression($pattern, $string, $message);
        } else {
            $this->assertNotRegExp($pattern, $string, $message);
        }
    }
}
