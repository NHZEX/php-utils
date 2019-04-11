<?php

namespace HZEX\DataStruct;

use Exception;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Symfony\Component\VarExporter\VarExporter;

/**
 * Trait StructSupport
 * @package HZEX\DataStruct
 * 支持扩大原始转换 https://docs.oracle.com/javase/specs/jls/se7/html/jls-5.html#jls-5.1.2
 * TODO 支持隐藏声明控制符
 */
trait StructSupport
{
    private static $BUILD_PATH = './';
    private static $GLOBAL_METADATA = [];

    public static function setCacheBuildPath(string $path): bool
    {
        if (!is_dir($path)) {
            return false;
        }
        self::$BUILD_PATH = realpath($path) . DIRECTORY_SEPARATOR;
        return true;
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
        self::$GLOBAL_METADATA[static::class] = self::analysisRule();
    }

    /**
     * 自动解析规则
     * @throws ReflectionException
     */
    private static function analysisRule(): array
    {
        static $regex = '~@property\s+(?<type>[\w|]+)\s+\$(?<name>[\w]+)\s+(\[(?<control>\w*)\])?~m';

        $ref = new ReflectionClass(static::class);
        $refe = new ReflectionClassExpansion($ref);
        $namespace = $ref->getNamespaceName();
        $useStatements = $refe->getFastUseMapping();
        $doc = $ref->getDocComment();

        $metadataAttr = [];
        $metadataReadOnly = [];
        if (preg_match_all($regex, $doc, $match_doc, PREG_SET_ORDER)) {
            foreach ($match_doc as $info) {
                $name = $info['name'];
                $type = $info['type'];
                $types = array_map('trim', explode('|', $type));
                $control = trim($info['control'] ?? '');

                // 记录元数据
                $metadataAttr[$name] = [
                    'type' => $info['type'],
                    'realType' => [],
                    'control' => $control,
                    'isBasicType' => self::isBasicType($info['type']),
                ];
                // 处理类型定义
                foreach ($types as $k => $type) {
                    // 统一转换标指类型
                    $realType = self::typeConversion($type);
                    // 分析类型是否类
                    if (false === $isNotClass = self::isNotClass($realType)) {
                        $realType = self::classNameImport($name, $realType, $namespace, $useStatements);
                    }
                    $metadataAttr[$name]['realType'][$realType] = !$isNotClass;
                }

                // 属性只读
                if ('read' === $control) {
                    $metadataReadOnly[$name] = true;
                }
            }
        }
        return [
            'hash' => md5_file(__FILE__),
            self::METADATA_ATTR => $metadataAttr,
            self::METADATA_READ_ONLY => $metadataReadOnly,
        ];
    }

    /**
     * 获取元数据
     * @param string|null $type
     * @return array
     */
    protected function getMetaData(?string $type): array
    {
        return self::$GLOBAL_METADATA[static::class][$type];
    }

    /**
     * 设置元数据
     * @param string|null $type
     * @param mixed       $value
     * @return void
     */
    protected function setMetaData(string $type, $value): void
    {
        self::$GLOBAL_METADATA[static::class][$type] = $value;
    }

    /**
     * 加载缓存文件
     */
    public static function loadCacheFile(): void
    {
        $file = self::$BUILD_PATH . 'struct.dump.php';
        if (false === is_file($file)) {
            return;
        }
        /** @noinspection PhpIncludeInspection */
        $result = require $file;
        self::$GLOBAL_METADATA = $result ?? [];
    }

    /**
     * 保存缓存文件
     */
    public static function saveCacheFile()
    {
        $file = self::$BUILD_PATH . 'struct.dump.php';
        /** @noinspection PhpUnhandledExceptionInspection */
        $content = '<?php' . PHP_EOL . 'return ' . VarExporter::export(self::$GLOBAL_METADATA) . ';' . PHP_EOL;
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
    protected static function classNameImport(string $name, string $type, string $namespace, array $useStatements)
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
    protected static function typeConversion(string $type)
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
    protected static function isBasicType(string $type)
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
    public function typeCheck($name, &$inputValue): bool
    {
        $info = $this->getMetaData(self::METADATA_ATTR)[$name] ?? null;
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
