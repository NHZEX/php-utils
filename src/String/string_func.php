<?php

namespace Zxin\Str;

use function max;
use function mb_chr;
use function mb_internal_encoding;
use function mb_ord;
use function mb_strcut;
use function strlen;

/**
 * 支持多字节字符串按照字节长度进行截取
 * @param string      $string  字符串
 * @param int         $bytes   截取长度
 * @param string      $dot     省略符
 * @param string|null $charset 编码
 * @return string
 */
function strcut_omit(string $string, int $bytes, string $dot = '...', ?string $charset = null): string
{
    $dotlen = strlen($dot);
    if (strlen($string) > $bytes - $dotlen) {
        $charset || $charset = mb_internal_encoding();
        $cutlen = $bytes - $dotlen;
        // cutlen 最少保证不少于3字节
        return mb_strcut($string, 0, max($cutlen, 3), $charset) . $dot;
    }

    return $string;
}

/**
 * @param string $input
 * @return string
 */
function str_fullwidth_to_ascii(string $input): string
{
    return preg_replace_callback("/[\x{3000}|\x{FF01}-\x{FF5E}]/u", function ($match) {
        $str = $match[0];
        if ("\u{3000}" === $str) {
            return ' ';
        }

        // 资料：https://zh.wikipedia.org/wiki/%E5%85%A8%E5%BD%A2%E5%92%8C%E5%8D%8A%E5%BD%A2
        return mb_chr(mb_ord($str) - 0xFEE0, 'UTF-8');
    }, $input);
}

/**
 * @param string|string[] $search
 * @param string $replace
 * @return string|string[]
 */
function str_trim_nbsp($search, string $replace = '')
{
    return str_replace("\xc2\xa0", $replace, $search);
}

function str_is_ascii(string $str): bool
{
    return mb_check_encoding($str, 'ASCII');
}