<?php

namespace Zxin;

use Closure;
use ReflectionException;
use ReflectionFunction;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

use function var_export;
use function is_array;
use function is_object;
use function get_class;
use function spl_object_id;

class Debug
{
    /**
     * dump 值并以字符串形式返回
     * @param mixed $val
     * @return string
     */
    public static function dumpValue($val): string
    {
        static $cloner = null;
        static $dumper = null;
        if (null === $dumper) {
            $cloner = new VarCloner();
            // $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO); // 用途未知
            // TODO 使用了外部资源，可能会导致协程兼容问题
            $dumper = new CliDumper();
        }
        return $dumper->dump($cloner->cloneVar($val), true);
    }

    /**
     * @param iterable $data
     * @param bool     $display
     * @return iterable
     */
    public static function array(iterable $data, bool $display = false): iterable
    {
        foreach ($data as &$item) {
            if (is_array($item)) {
                $item = self::array($item, $display);
            } else {
                $item = self::value($item);
            }
        }
        $content = $data;

        if ($display) {
            echo var_export($content, true);
        }
        return $content;
    }

    public static function value($val)
    {
        if (is_array($val)) {
            return self::array($val);
        } elseif (is_object($val)) {
            if ($val instanceof Closure) {
                return self::debugClosure($val, false);
            }
            return '\\' . get_class($val) . '#' . spl_object_id($val);
        } else {
            return self::dumpValue($val);
        }
    }

    /**
     * @param Closure $object
     * @param bool    $display
     * @return string|null
     */
    public static function debugClosure(Closure $object, bool $display = false): ?string
    {
        try {
            $ref = new ReflectionFunction($object);
        } catch (ReflectionException $e) {
            return null;
        }
        if ($ref->getClosureThis()) {
            $thisClass = self::value($ref->getClosureThis());
        } else {
            $thisClass = $ref->getFileName();
        }

        $content = "$thisClass@{$ref->getStartLine()}-{$ref->getEndLine()}\n";
        if ($display) {
            echo $content;
        }
        return $content;
    }
}
