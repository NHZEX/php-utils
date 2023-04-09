<?php

declare(strict_types=1);

namespace Zxin\DataStruct;

use ArrayAccess;
use JsonSerializable;
use function array_diff_key;
use function array_flip;
use function get_object_vars;
use function property_exists;

abstract class BaseProperty implements ArrayAccess, JsonSerializable
{
    private $hiddenKey = [];

    /** @var bool */
    protected $propExistCheck = false;

    public function __construct(?iterable $input = [])
    {
        $this->load($input);
        $this->initialize();
    }

    protected function load(?iterable $input)
    {
        if (!empty($input)) {
            foreach ($input as $key => $value) {
                if ($this->propExistCheck && !property_exists($this, $key)) {
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
     * 设置需要隐藏的输出值
     * @param  array $hidden 属性列表
     * @return $this
     */
    public function hidden(array $hidden = []): BaseProperty
    {
        $this->hiddenKey = array_flip($hidden);
        return $this;
    }

    /**
     * 返回该集合内部属性
     * @return array
     */
    public function all(): array
    {
        return $this->getPublicVars();
    }

    /**
     * 把结构对象转换为数组输出
     * @return array
     */
    public function toArray(): array
    {
        $data = $this->getPublicVars();
        if (!empty($this->hiddenKey)) {
            $data  = array_diff_key($data, $this->hiddenKey);
        }
        return $data;
    }

    /**
     * 获取类的公开属性
     * @return array
     */
    private function getPublicVars(): array
    {
        return (function ($that) {
            return get_object_vars($that);
        })->bindTo(null, null)($this);
    }

    /**
     * Whether a offset exists
     * @link  https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param string $offset An offset to check for.
     * @return bool true on success or false on failure.
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    /**
     * Offset to retrieve
     * @link  https://php.net/manual/en/arrayaccess.offsetget.php
     * @param string $offset The offset to retrieve.
     * @return mixed Can return all value types.
     */
    #[\ReturnTypeWillChange]
    public function &offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Offset to set
     * @link  https://php.net/manual/en/arrayaccess.offsetset.php
     * @param string $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * Offset to unset
     * @link  https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param string $offset The offset to unset.
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

    public function __set($name, $value)
    {
        if ($this->propExistCheck && !property_exists($this, $name)) {
            return;
        }
        $this->$name = $value;
    }

    public function __unset($name)
    {
        if ($this->propExistCheck && !property_exists($this, $name)) {
            return;
        }
        $this->$name = null;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by json_encode, which is a value of any type other than a resource.
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function __debugInfo()
    {
        return $this->getPublicVars();
    }
}
