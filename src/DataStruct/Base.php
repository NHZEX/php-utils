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

    /** @var bool 严格模式 */
    protected $strictMode = false;

    /** @var array|iterable */
    protected $propertyData = [];
    protected $originalData = [];

    private $hiddenKey = [];
    private $changeCount = 0;

    public function __construct(iterable $data = [])
    {
        // 加载规则
        self::loadMeatData();
        // 载入初始值
        foreach ($data as $key => $datum) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->__set($key, $datum);
        }
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
        $this->hiddenKey = array_flip($hidden);
        return $this;
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
    public function toArray(): array
    {
        // 过滤隐藏值输出属性
        if (count($this->hiddenKey)) {
            return array_diff_key($this->propertyData, $this->hiddenKey);
        }

        // 输出属性
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

    /**
     * 获取一个属性值
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->propertyData[$name];
    }

    /**
     * 设置一个属性值
     * @param string $name
     * @param mixed  $value
     * @throws Exception
     */
    public function __set(string $name, $value): void
    {
        if (isset($this->getMetaData(self::METADATA_READ_ONLY)[$name]) && isset($this->propertyData[$name])) {
            throw new Exception(static::class . '->$' . $name . ' Read only');
        }
        $attrInfo = $this->getMetaData(self::METADATA_ATTR)[$name] ?? null;
        if ($this->strictMode && null === $attrInfo) {
            throw new Exception(static::class . '->$' . $name . ' Undefined');
        }
        $this->typeCheck($name, $value);
        if ($attrInfo
            && $attrInfo['isBasicType']
            && isset($this->propertyData[$name])
            && $this->propertyData[$name] === $value
        ) {
            // 内容一致不做更改
            return;
        }
        $this->propertyData[$name] = $value;
        $this->dataChange();
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
     * 销毁一个属性值
     * @param string $name
     * @throws Exception
     */
    public function __unset($name): void
    {
        if (isset($this->getMetaData(self::METADATA_READ_ONLY)[$name])) {
            throw new Exception(static::class . '->$' . $name . ' Read only');
        }
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
