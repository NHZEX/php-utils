<?php

namespace Zxin\DataStruct;

use JsonSerializable;
use RuntimeException;

use function array_diff_key;
use function array_flip;
use function get_object_vars;
use function is_iterable;
use function property_exists;

abstract class BaseStruct implements JsonSerializable
{
    /**
     * @param null|iterable<string, mixed> $input
     */
    public function __construct($input = [])
    {
        if (null !== $input && !is_iterable($input)) {
            throw new RuntimeException('input type must be iterable');
        }
        $this->load($input);
        $this->initialize();
    }

    /**
     * @return array
     */
    abstract public function getHiddenKey(): array;

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

    /**
     * 结构初始化
     * @return void
     */
    protected function initialize(): void
    {
    }

    /**
     * 返回该集合内部属性
     * @return array
     */
    public function all(): array
    {
        return $this->getPublicProp();
    }

    /**
     * 把结构对象转换为数组输出
     * @return array
     */
    public function toArray(): array
    {
        $data = $this->getPublicProp();
        $hiddenKey = $this->getHiddenKey();
        if (!empty($hiddenKey)) {
            $hiddenKey = array_flip($hiddenKey);
            $data  = array_diff_key($data, $hiddenKey);
        }
        return $data;
    }

    /**
     * 获取类的公开属性
     * @return array
     */
    private function getPublicProp(): array
    {
        return (function ($that) {
            return get_object_vars($that);
        })->bindTo(null, null)($this);
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

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __debugInfo()
    {
        return $this->getPublicProp();
    }
}
