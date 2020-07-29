<?php
declare(strict_types=1);

namespace Zxin\Stub;

class ClassC
{
    private $test = '123456';

    /**
     * @return string
     */
    public function getTest(): string
    {
        return $this->test;
    }
}
