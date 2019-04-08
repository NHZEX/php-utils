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
use ReflectionClass;
use ReflectionException;

abstract class Base implements ArrayAccess, JsonSerializable
{
    /** @var array|iterable */
    protected $property_data = [];
    private $hidden_key = [];
    private $read_only_key = [];

    public function __construct(iterable $data = [])
    {
        $this->property_data = (array) $data;
        $this->initialize();
    }

    /**
     * 额外的初始化
     */
    protected function initialize(): void
    {
    }

    /**
     * 自动解析规则
     * @throws ReflectionException
     */
    protected function loadRule(): void
    {
        static $regex = '~@property\s+(?<type>\w+)\s+\$(?<name>[\w]+)\s+\[(?<control>\w*)\]~m';

        $reflection = new ReflectionClass($this);
        $doc = $reflection->getDocComment();

        $read_olny = [];
        if (preg_match_all($regex, $doc, $match_doc, PREG_SET_ORDER)) {
            foreach ($match_doc as $value) {
                ['name' => $name, 'type' => $type] = $value;
                $control = trim($value['control'] ?? '');
                if ('read' === $control) {
                    $read_olny[$name] = true;
                }
            }
        }

        $this->read_only_key = $read_olny;
    }

    protected function setReadProperty(array $keys): self
    {
        $this->read_only_key = array_flip($keys);
        return $this;
    }

    /**
     * 设置需要隐藏的输出值
     * @access public
     * @param  array $hidden 属性列表
     * @return $this
     */
    public function setHidden($hidden = []): self
    {
        $this->hidden_key = array_flip($hidden);
        return $this;
    }

    /**
     * 返回该集合内部属性
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
     * @param bool $force 忽略保护强制擦除
     * @return bool
     */
    public function erase($name, $force = false): bool
    {
        if (!$force && isset($this->read_only_key[$name]) && isset($this->property_data[$name])) {
            return false;
        } else {
            unset($this->property_data[$name]);
            return true;
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->property_data[$name];
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @throws Exception
     */
    public function __set(string $name, $value): void
    {
        if (isset($this->read_only_key[$name]) && isset($this->property_data[$name])) {
            throw new Exception(static::class . '::' . $name . " read only");
        }
        $this->property_data[$name] = $value;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name): bool
    {
        return isset($this->property_data[$name]);
    }

    /**
     * @param string $name
     * @throws Exception
     */
    public function __unset($name): void
    {
        if (isset($this->read_only_key[$name])) {
            throw new Exception(static::class . 'property' . $name . " read only");
        }
        unset($this->property_data[$name]);
    }

    /**
     * Whether a offset exists
     * @link  https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     * @return bool true on success or false on failure.
     *                      </p>
     *                      <p>
     *                      The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * Offset to retrieve
     * @link  https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * Offset to set
     * @link  https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     * @return void
     * @throws Exception
     * @since 5.0.0
     */
    public function offsetSet($offset, $value): void
    {
        $this->__set($offset, $value);
    }

    /**
     * Offset to unset
     * @link  https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     * @return void
     * @throws Exception
     * @since 5.0.0
     */
    public function offsetUnset($offset): void
    {
        $this->__unset($offset);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
