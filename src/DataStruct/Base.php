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
use RuntimeException;

/**
 * Class Base2
 * @package struct
 * TODO 支持预设默认值
 * TODO 支持广泛原始类型检查(安全类型转换) https://wiki.php.net/rfc/scalar_type_hints_v5
 * TODO 需要做单元测试
 */
class Base implements ArrayAccess, JsonSerializable
{
    /** @var array|iterable */
    protected $property_data = [];
    protected $original_data = [];
    private $metadata = [];
    private $namespace = '';
    private $useStatements = [];
    private $hidden_key = [];
    private $read_only_key = [];
    private $change_count = 0;

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
        static $regex = '~@property\s+(?<type>\w+)\s+\$(?<name>[\w]+)\s+(\[(?<control>\w*)\])?~m';

        $ref = new ReflectionClass($this);
        $refe = new ReflectionClassExpansion($ref);
        $this->namespace = $ref->getNamespaceName();
        $this->useStatements = $refe->getFastUseMapping();

        $doc = $ref->getDocComment();
        $read_olny = [];
        if (preg_match_all($regex, $doc, $match_doc, PREG_SET_ORDER)) {
            foreach ($match_doc as $info) {
                $name = $info['name'];
                $control = trim($info['control'] ?? '');
                $type = $info['type'];
                $realType = $this->typeConversion($type);

                // 分析类型
                if (false === $this->isTypeNotClass($realType)) {
                    $targetClassNames = [
                        $this->useStatements[$realType] ?? null,
                        $this->namespace . '\\' . $realType,
                        $realType,
                    ];
                    $targetClassName = null;
                    foreach ($targetClassNames as $className) {
                        if (is_string($className) && (class_exists($className) || interface_exists($className))) {
                            $targetClassName = $className;
                        }
                    }
                    if (null === $targetClassName) {
                        throw new RuntimeException("目标类型类无法匹配有效导入 {$targetClassName}");
                    }
                    $realType = $targetClassName;
                }

                // 记录元数据
                $this->metadata[$name] = [
                    'type' => $type,
                    'realType' => $realType,
                    'control' => $control,
                ];
                // 写入只读控制
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
            $this->dataChange();
            return true;
        }
    }

    /**
     * 数据被更改
     */
    protected function dataChange()
    {
        $this->change_count++;
    }

    /**
     * 获取数据更改时间
     * @return int
     */
    public function getDataChangeCount()
    {
        return $this->change_count;
    }

    /**
     * 类型统一
     * @param string $type
     * @return string
     * @link https://www.php.net/manual/en/language.types.php
     */
    protected function typeConversion(string $type)
    {
        switch ($type) {
            case 'bool':
            case 'boolean':
            case 'true':
            case 'false':
                $result = 'bool';
                break;
            case 'int':
            case 'integer':
            case 'number':
                $result = 'int';
                break;
            case 'double':
            case 'float':
                $result = 'float';
                break;
            case 'callable':
            case 'callback':
                $result = 'callable';
                break;
            case '':
            case 'mixed':
                $result = 'mixed';
                break;
            case 'string':
            case 'array':
            case 'iterable':
            case 'object':
            case 'resource':
            case 'null':
            default:
                $result = $type;
        }
        return $result;
    }

    /**
     * 这个类型不是一个类
     * @param string $type
     * @return bool
     */
    protected function isTypeNotClass(string $type)
    {
        static $types = [
            'bool' => 0,
            'int' => 0,
            'float' => 0,
            'string' => 0,
            'array' => 0,
            'iterable' => 0,
            'object' => 0,
            'resource' => 0,
            'null' => 0,
            'callable' => 0,
            'mixed' => 0,
            '' => 0,
        ];

        return isset($types[$type]);
    }

    /**
     * 是否基本类型
     * @param string $type
     * @return bool
     */
    protected function isBasicType(string $type)
    {
        static $types = [
            'bool' => 0,
            'int' => 0,
            'float' => 0,
            'string' => 0,
            'null' => 0,
        ];

        return isset($types[$type]);
    }

    /**
     * 类型检查
     * @param $name
     * @param $inputValue
     * @return bool
     * @throws ReflectionException
     * @throws Exception
     * @link https://www.php.net/manual/zh/language.types.php
     */
    public function typeCheck($name, $inputValue): bool
    {
        // TODO 支持多参检查定义解析 string|bool

        $info = $this->metadata[$name] ?? null;
        if (null === $info) {
            return false;
        }

        $targetType = $info['realType'];
        $currentType = gettype($inputValue);
        $result = null;

        switch ($targetType) {
            case 'bool':
                $result = is_bool($inputValue);
                break;
            case 'int':
                $result = is_int($inputValue);
                break;
            case 'float':
                $result = is_float($inputValue);
                break;
            case 'string':
                $result = is_string($inputValue);
                break;
            case 'array':
                $result = is_array($inputValue);
                break;
            case 'iterable':
                $result = is_iterable($inputValue);
                break;
            case 'object':
                $result = is_object($inputValue);
                break;
            case 'resource':
                $result = is_resource($inputValue);
                break;
            case 'null':
                $result = is_null($inputValue);
                break;
            case 'callable':
                $result = is_callable($inputValue);
                break;
            case 'mixed':
                $result = true;
                break;
        }

        if (null === $result && is_object($inputValue)) {
            // 实例类反射
            $targetRef = new ReflectionClass($targetType);
            $valueRef = new ReflectionClass($inputValue);

            // 获取类型
            $currentType = $valueRef->getName();

            // 如果目标是接口，则判断当前值是否实现该接口
            if ($targetRef->isInterface()
                && $valueRef->implementsInterface($targetRef)
            ) {
                $result = true;
            }

            // 如果目标是类，则先判断类是否一致，在判断类是否包含
            if ($targetRef->getName() === $currentType
                || $valueRef->isSubclassOf($targetRef->getName())
            ) {
                $result = true;
            }
        }

        if (true !== $result) {
            $msg = sprintf('属性类型不一致错误，当前类型 %s，目标类型 %s', $currentType, $targetType);
            throw new RuntimeException($msg);
        }

        return $result;
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
        $this->typeCheck($name, $value);
        $info = $this->metadata[$name] ?? null;
        if ($info
            && $this->isBasicType($info['realType'])
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
