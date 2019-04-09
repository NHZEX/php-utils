<?php

namespace HZEX\DataStruct;

use Exception;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

trait StructSupport
{
    private $metadata = [];
    private $namespace = '';
    private $useStatements = [];

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
                        throw new RuntimeException("目标类型类无法匹配有效导入{$name} {$targetClassName}");
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
            $msg = sprintf('属性类型不一致错误 %s，当前类型 %s，目标类型 %s', $name, $currentType, $targetType);
            throw new RuntimeException($msg);
        }

        return $result;
    }
}
