<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/29
 * Time: 18:11
 */
declare(strict_types=1);

namespace HZEX\DataStruct;

use ArrayAccess;
use Exception;
use JsonSerializable;

/**
 * Class Base2
 * @package struct
 * TODO 需要做单元测试
 */
class Base implements ArrayAccess, JsonSerializable
{
    use StructSupport;

    protected const METADATA_ATTR = 'attr';
    protected const METADATA_READ_ONLY = 'readOnly';

    /** @var array|iterable */
    protected $property_data = [];
    protected $original_data = [];

    private $hidden_key = [];
    private $change_count = 0;

    public function __construct(iterable $data = [])
    {
        self::loadMeatData();
        $this->property_data = (array) $data;
        $this->initialize();
    }

    /**
     * 额外的初始化
     * @return void
     */
    protected function initialize(): void
    {
    }

    /**
     * 设置只读属性
     * @deprecated
     * @param array $keys
     * @return Base
     */
    protected function setReadProperty(array $keys): self
    {
        $this->setMetaData(self::METADATA_READ_ONLY, array_flip($keys));
        return $this;
    }

    /**
     * 设置需要隐藏的输出属性
     * @param array $hidden 属性列表
     * @return $this
     */
    public function setHidden(array $hidden): self
    {
        $this->hidden_key = array_flip($hidden);
        return $this;
    }

    /**
     * 返回该集合内部原始属性
     * @return array
     */
    public function all(): array
    {
        return $this->property_data;
    }


    /**
     * 把结构对象转换为数组输出
     * @return array
     */
    public function toArray(): array
    {
        // 过滤隐藏值输出属性
        if (count($this->hidden_key)) {
            return array_diff_key($this->property_data, $this->hidden_key);
        }

        // 输出属性
        return $this->property_data;
    }

    /**
     * 擦除一个属性
     * @param      $name
     * @return bool
     */
    public function erase($name): bool
    {
        if (isset($this->property_data[$name])) {
            unset($this->property_data[$name]);
            $this->dataChange();
            return true;
        }
        return false;
    }

    /**
     * 数据被更改计数
     */
    protected function dataChange()
    {
        $this->change_count++;
    }

    /**
     * 获取数据更改计数
     * @return int
     */
    public function getDataChangeCount()
    {
        return $this->change_count;
    }

    /**
     * 获取一个属性值
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->property_data[$name];
    }

    /**
     * 设置一个属性值
     * @param string $name
     * @param mixed  $value
     * @throws Exception
     */
    public function __set(string $name, $value): void
    {
        if (isset($this->getMetaData(self::METADATA_READ_ONLY)[$name]) && isset($this->property_data[$name])) {
            throw new Exception(static::class . '::' . $name . ' read only');
        }
        $this->typeCheck($name, $value);
        $info = $this->getMetaData(self::METADATA_ATTR)[$name] ?? null;
        if ($info
            && $info['isBasicType']
            && isset($this->property_data[$name])
            && $this->property_data[$name] === $value
        ) {
            // 内容一致不做更改
            return;
        }
        $this->property_data[$name] = $value;
        $this->dataChange();
    }

    /**
     * 一个属性值是否存在
     * @param string $name
     * @return bool
     */
    public function __isset($name): bool
    {
        return isset($this->property_data[$name]);
    }

    /**
     * 销毁一个属性值
     * @param string $name
     * @throws Exception
     */
    public function __unset($name): void
    {
        if (isset($this->getMetaData(self::METADATA_READ_ONLY)[$name])) {
            throw new Exception(static::class . 'property' . $name . " read only");
        }
        unset($this->property_data[$name]);
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
     * @since 5.0.0
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
     * @throws Exception
     * @since 5.0.0
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
     * @throws Exception
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
}
