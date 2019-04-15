<?php

namespace HZEX\DataStruct;

use ReflectionException;

/**
 * Class DataStruct
 * @package HZEX\DataStruct
 * TODO 输出隐藏
 * TODO 只读控制
 */
class DataStruct
{
    use DataStructSupport;

    /** @var string */
    private static $BUILD_PATH = './';
    /** @var StructMetaData[] */
    private static $GLOBAL_METADATA = [];

    private const METADATA_PROP = 'prop';
    private const METADATA_HASH = 'hash';

    /** @var array */
    private $propertyData = [];
    /** @var int */
    private $changeCount = 0;

    public function __construct()
    {
        self::loadMeatData();
        $this->initialStruct();
    }

    /**
     * 返回该集合内部原始属性
     * @return array
     */
    public function all(): array
    {
        return $this->propertyData;
    }

    /**
     * 把结构对象转换为数组输出
     * @return array
     */
    public function toArray()
    {
        return $this->propertyData;
    }

    /**
     * 擦除一个属性
     * @param      $name
     * @return bool
     */
    public function erase($name): bool
    {
        if (isset($this->propertyData[$name])) {
            unset($this->propertyData[$name]);
            $this->dataChange();
            return true;
        }
        return false;
    }

    /**
     * 一个属性值是否存在
     * @param string $name
     * @return bool
     */
    public function __isset($name): bool
    {
        return isset($this->propertyData[$name]);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->propertyData[$name];
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws ReflectionException
     */
    public function __set(string $name, $value)
    {
        $attrInfo = $this->getMetaData()->props[$name] ?? null;
        $this->typeCheck($name, $value);

        if ($attrInfo
            && isset($this->propertyData[$name])
            && $this->propertyData[$name] === $value
        ) {
            // 内容一致不做更改
            return;
        }
        $this->propertyData[$name] = $value;
    }

    /**
     * 销毁一个属性值
     * @param string $name
     */
    public function __unset($name): void
    {
//        if (isset($this->getMetaData()->props[$name])) {
//            throw new StructReadOnlyException(static::class . '->$' . $name . ' Read only');
//        }
        unset($this->propertyData[$name]);
    }

    /**
     * 一个成员值是否存在
     * @link  https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset
     * @return bool true on success or false on failure.
     */
    public function offsetExists($offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * 获取一个成员
     * @link  https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * 设置一个成员
     * @link  https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset
     * @param mixed $value
     * @return void
     * @throws ReflectionException
     */
    public function offsetSet($offset, $value): void
    {
        $this->__set($offset, $value);
    }

    /**
     * 销毁一个成员
     * @link  https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        $this->__unset($offset);
    }

    /**
     * json 序列化
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }


    /**
     * 数据被更改计数
     */
    protected function dataChange()
    {
        $this->changeCount++;
    }

    /**
     * 获取数据更改计数
     * @return int
     */
    public function getDataChangeCount()
    {
        return $this->changeCount;
    }
}
