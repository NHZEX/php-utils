<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/3/6
 * Time: 15:10
 */
declare(strict_types=1);

namespace Zxin;

use function count;
use function extension_loaded;
use function lcfirst;
use function posix_geteuid;
use function posix_getpwuid;
use function preg_replace;
use function round;
use function sprintf;
use function str_replace;
use function strtolower;
use function ucwords;

class Util
{
    protected static $snakeChahe = [];
    protected static $upperCamelCache = [];
    protected static $lowerCamelCache = [];

    /**
     * 转换为下划线命名
     * @param string $input
     * @return string
     */
    public static function toSnakeCase(string $input): string
    {
        if (isset(self::$snakeChahe[$input])) {
            return self::$snakeChahe[$input];
        }
        // 小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
        $snake = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $input));
        return self::$snakeChahe[$input] = $snake;
    }

    /**
     * 转换为大驼峰命名
     * @param string $input
     * @return string
     */
    public static function toUpperCamelCase(string $input): string
    {
        if (isset(self::$upperCamelCache[$input])) {
            return self::$upperCamelCache[$input];
        }
        /**
         * step1.原字符串转换下划线命名
         * step3.转换每个单词的首字母到大写
         * step4.移除所有下划线
         */
        $separator = '_';
        $uncamelized_words = self::toSnakeCase($input);
        $uncamelized_words = ucwords($uncamelized_words, '_');
        $uncamelized_words = str_replace($separator, '', $uncamelized_words);
        return self::$upperCamelCache[$input] = $uncamelized_words;
    }

    /**
     * 转换为小驼峰命名
     * @param string $input
     * @return string
     */
    public static function toLowerCamelCase(string $input): string
    {
        if (isset(self::$lowerCamelCache[$input])) {
            return self::$lowerCamelCache[$input];
        }
        $lower = lcfirst(self::toUpperCamelCase($input));
        return self::$lowerCamelCache[$input] = $lower;
    }

    /**
     * 获取当前进程用户
     * @return string
     */
    public static function whoami(): string
    {
        if (!extension_loaded('posix')) {
            return '';
        }
        return posix_getpwuid(posix_geteuid())['name'];
    }

    /**
     * 自动格式化可读字节单位
     */
    public static function formatByte(float $byte, int $dec = 2, bool $unit = true): string
    {
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];
        $count = count($units) - 1;
        $pos  = 0;

        while ($byte >= 1024 && $pos < $count) {
            $byte /= 1024;
            $pos++;
        }

        $result = sprintf("%.{$dec}f", round($byte, $dec));

        if ($unit) {
            return "{$result} {$units[$pos]}";
        } else {
            return $result;
        }
    }
}
