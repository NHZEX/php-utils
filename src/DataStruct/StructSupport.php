<?php

namespace HZEX\DataStruct;

use Exception;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

/**
 * Trait StructSupport
 * @package HZEX\DataStruct
 * TODO 支持广泛原始类型检查(安全类型转换) https://wiki.php.net/rfc/scalar_type_hints_v5
 * TODO 编译优化代码
 */
trait StructSupport
{
    public static $BUILD_PATH = './';

    private $metadata = [];

    /**
     * 自动解析规则
     * @throws ReflectionException
     */
    protected function loadRule(): void
    {
        static $regex = '~@property\s+(?<type>[\w|]+)\s+\$(?<name>[\w]+)\s+(\[(?<control>\w*)\])?~m';

        $ref = new ReflectionClass($this);
        $refe = new ReflectionClassExpansion($ref);
        $namespace = $ref->getNamespaceName();
        $useStatements = $refe->getFastUseMapping();

        $doc = $ref->getDocComment();
        $read_olny = [];
        if (preg_match_all($regex, $doc, $match_doc, PREG_SET_ORDER)) {
            foreach ($match_doc as $info) {
                $name = $info['name'];
                $type = $info['type'];
                $types = array_map('trim', explode('|', $type));
                $control = trim($info['control'] ?? '');

                // 记录元数据
                $this->metadata[$name] = [
                    'type' => $info['type'],
                    'realType' => [],
                    'control' => $control,
                    'isBasicType' => $this->isBasicType($info['type']),
                ];
                // 处理类型定义
                foreach ($types as $k => $type) {
                    // 统一转换标指类型
                    $realType = $this->typeConversion($type);
                    // 分析类型是否类
                    if (false === $isNotClass = $this->isNotClass($realType)) {
                        $realType = $this->classNameImport($name, $realType, $namespace, $useStatements);
                    }
                    $this->metadata[$name]['realType'][$realType] = !$isNotClass;
                }

                // 属性只读
                if ('read' === $control) {
                    $read_olny[$name] = true;
                }
            }
        }
        $this->read_only_key = $read_olny;
    }

    /**
     *
     */
    public function build()
    {
        $class_name = static::class;
        $class_hash = md5_file(__FILE__);
        $class_data = [
            'hash' => $class_hash,
            'metadata' => $this->metadata,
        ];

        $file = self::$BUILD_PATH . 'struct.php';
        /** @noinspection PhpIncludeInspection */
        $data = is_file($file) ? require $file : [];
        $data = is_array($data) ? $data : [];
        $data[$class_name] = $class_data;

        $content = '<?php' . PHP_EOL . var_export($data, true) . ';';
        file_put_contents($file, $content);
    }

    /**
     * 类名解析导入
     * @param string $name
     * @param string $type
     * @param string $namespace
     * @param array  $useStatements
     * @return string|null
     */
    protected function classNameImport(string $name, string $type, string $namespace, array $useStatements)
    {
        $targetClassNames = [
            $useStatements[$type] ?? null,
            $namespace . '\\' . $type,
            $type,
        ];
        $targetClassName = null;
        foreach ($targetClassNames as $className) {
            if (is_string($className) && (class_exists($className) || interface_exists($className))) {
                $targetClassName = $className;
            }
        }
        if (null === $targetClassName) {
            throw new RuntimeException("目标类型类无法匹配有效导入{$name} {$targetClassName}");
        }
        return $targetClassName;
    }

    /**
     * 类型统一转换
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
     * 类型不是一个类
     * @param string $type
     * @return bool
     */
    protected function isNotClass(string $type)
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
        $info = $this->metadata[$name] ?? null;
        if (null === $info) {
            return false;
        }

        $realType = $info['realType'];
        $currentType = gettype($inputValue);
        $result = null;

        foreach ($realType as $targetType => $isClass) {
            $result = null;
            if ($isClass && is_object($inputValue)) {
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
            } else {
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
            }
            if (true === $result) {
                break;
            }
        }

        if (true !== $result) {
            $msg = sprintf('属性类型不一致错误 %s，当前类型 %s，目标类型 %s', $name, $currentType, $info['type']);
            throw new RuntimeException($msg);
        }

        return true;
    }
}
