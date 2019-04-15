<?php

namespace HZEX\DataStruct;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;

trait DataStructSupport
{
    /**
     * @param string $path
     * @return bool
     */
    public static function setCacheBuildPath(string $path): bool
    {
        if (!is_dir($path)) {
            return false;
        }
        self::$BUILD_PATH = realpath($path) . DIRECTORY_SEPARATOR;
        return true;
    }

    /**
     * 加载缓存文件
     */
    public static function loadCacheFile(): void
    {
        // TODO 支持校验缓存文件是否合法
        $file = self::$BUILD_PATH . 'struct.dump.php';
        if (false === is_file($file)) {
            return;
        }
        /** @noinspection PhpIncludeInspection */
        $result = require $file;
        self::$GLOBAL_METADATA = $result ?? [];
    }

    /**
     * 生成缓存文件
     */
    public static function dumpCacheFile()
    {
        $file = self::$BUILD_PATH . 'struct.dump.php';
        /** @noinspection PhpUnhandledExceptionInspection */
        $content = '<?php'
            . PHP_EOL
            . 'return unserialize('
            . var_export(serialize(self::$GLOBAL_METADATA), true)
            . ');'
            . PHP_EOL;
        file_put_contents($file, $content);
    }

    /**
     * 加载结构元数据
     */
    public static function loadMeatData()
    {
        if (0 === count(self::$GLOBAL_METADATA)) {
            self::loadCacheFile();
        }
        if (isset(self::$GLOBAL_METADATA[static::class])) {
            return;
        }
        /** @noinspection PhpUnhandledExceptionInspection */
        self::$GLOBAL_METADATA[static::class] = self::analysisStruct();
    }

    /**
     * 获取元数据
     * @return StructMetaData
     */
    protected function getMetaData(): StructMetaData
    {
        return self::$GLOBAL_METADATA[static::class];
    }

    /**
     * @return StructMetaData
     * @throws ReflectionException
     */
    private static function analysisStruct(): StructMetaData
    {
        $ref = new ReflectionClass(static::class);
        $namespace = $ref->getNamespaceName();
        $refe = new ReflectionClassExpansion($ref);
        $useStatements = $refe->getFastUseMapping();
        $props = $ref->getProperties(ReflectionProperty::IS_PUBLIC);
        $propDefaultValues = $ref->getDefaultProperties();
        foreach ($props as $prop) {
            $propDocStr = $prop->getDocComment();
            $propDocArr = self::parseDoc($propDocStr);
            $propName = $prop->getName();
            $propType = $propDocArr['type'];
            $propValue = $propDefaultValues[$propName];
            $propControl = $propDocArr['control'];
            $propControlArr = array_flip(array_map('trim', explode(',', $propControl)));

            // 分析类是否可空
            if ($canNull = ($propType && $propType[0] === '?')) {
                $propType = substr($propType, 1);
            } else {
                $canNull = false;
            }
            $realType = self::typeConversion($propType);

            if (false === $isNotClass = self::isNotClass($realType)) {
                $realType = self::classImport($realType, $namespace, $useStatements);
                if (null === $realType) {
                    throw new RuntimeException("目标类型类无法匹配有效导入{$propName} {$realType}");
                }
            }

            $mProp = new StructMetaDataProp();
            $mProp->type = $propType;
            $mProp->realType = $realType;
            $mProp->control = $propControl;
            $mProp->isHide = isset($propControlArr['hide']);
            $mProp->isRead = isset($propControlArr['read']);
            $mProp->canNull = $canNull;
            $mProp->isBasicType = self::isBasicType($propType);
            $mProp->isNotClass = self::isNotClass($propType);
            $mProp->defaultValue = $propValue;
            $propMetadata[$propName] = $mProp;
        }

        $metadata = new StructMetaData();
        $metadata->hash = sha1_file($ref->getFileName());
        $metadata->props = $propMetadata ?? [];
        return $metadata;
    }

    private function initialStruct()
    {
        $meta = $this->getMetaData();
        foreach ($meta->props as $propName => $propInfo) {
            unset($this->$propName);
            $this->__set($propName, $propInfo->defaultValue);
            $propInfo->isHide && $this->isHideProps[$propName] = true;
            $propInfo->isRead && $this->isReadProps[$propName] = true;
        }
    }

    private static function parseDoc(string $doc): ?array
    {
        static $regex = '~@var\s+([\?\w]+)\s+(?:\{([,\w]*)})?~';
        if (preg_match_all($regex, $doc, $match, PREG_SET_ORDER)) {
            $match = $match[0];
            return [
                'type' => $match[1] ?? '',
                'control' => $match[2] ?? '',
            ];
        }
        return null;
    }

    /**
     * 类名解析导入
     * @param string $type
     * @param string $namespace
     * @param array  $useStatements
     * @return string|null
     */
    protected static function classImport(string $type, string $namespace, array $useStatements): ?string
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
            return null;
        }
        return $targetClassName;
    }

    /**
     * 类型统一转换
     * @param string $type
     * @return string
     * @link https://www.php.net/manual/en/language.types.php
     */
    protected static function typeConversion(string $type)
    {
        switch ($type) {
            case 'bool':
            case 'boolean':
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
    protected static function isNotClass(string $type)
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
            // 'callable' => 0, // 不应该提供支持 https://wiki.php.net/rfc/typed_properties_v2
            'mixed' => 0,
        ];

        return isset($types[$type]);
    }

    /**
     * 是否基本类型
     * @param string $type
     * @return bool
     */
    protected static function isBasicType(string $type)
    {
        static $types = [
            'bool' => 0,
            'int' => 0,
            'float' => 0,
            'string' => 0,
        ];

        return isset($types[$type]);
    }

    /**
     * 类型检查
     * @param StructMetaDataProp|null $propInfo
     * @param string                  $name
     * @param                         $inputValue
     * @return bool
     * @throws ReflectionException
     * @link https://www.php.net/manual/zh/language.types.php
     */
    public function typeCheck(?StructMetaDataProp $propInfo, string $name, &$inputValue): bool
    {
        if (null === $propInfo) {
            return false;
        }
        $result = null;

        $targetType = $propInfo->realType;
        $currentType = gettype($inputValue);

        if ($propInfo->canNull && $inputValue === null) {
            $result = true;
        } elseif (!$propInfo->isNotClass && is_object($inputValue)) {
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
                    $result = is_float($inputValue) || $conversion = is_int($inputValue);
                    if (isset($conversion) && $conversion) {
                        $inputValue = (float) $inputValue;
                    }
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

        if (true !== $result) {
            $msg = sprintf('属性类型不一致错误 %s，当前类型 %s，目标类型 %s', $name, $currentType, $propInfo->type);
            throw new StructTypeException($msg);
        }

        return true;
    }
}
