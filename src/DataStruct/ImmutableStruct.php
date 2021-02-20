<?php

declare(strict_types=1);

namespace Zxin\DataStruct;

use function property_exists;

abstract class ImmutableStruct extends BaseProperty
{
    protected function load(?iterable $input)
    {
        if (!empty($input)) {
            foreach ($input as $key => $value) {
                if (!property_exists($this, $key)) {
                    continue;
                }
                $this->$key = $value;
            }
        }
    }

    public function __set($name, $value)
    {
        if (!property_exists($this, $name)) {
            return;
        }
        $this->$name = $value;
    }

    public function __unset($name)
    {
        if (!property_exists($this, $name)) {
            return;
        }
        $this->$name = null;
    }
}
