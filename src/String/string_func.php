<?php

namespace Zxin\Str;

use function max;
use function mb_check_encoding;
use function mb_chr;
use function mb_internal_encoding;
use function mb_ord;
use function mb_strcut;
use function preg_replace_callback;
use function str_replace;
use function strlen;
use function strtr;

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
    if ('' === $str) {
        return true;
    }
    return mb_check_encoding($str, 'ASCII');
}

/**
 * Replace all characters with an ASCII equivalent.
 * @param  string $str       Original string converted
 */
function str_replace_umlaut_unaccent(string $str): ?string
{
    // https://gist.github.com/niquenen/d06a55ddf11f4a08a421750c2ccb96b6
    // https://docs.oracle.com/cd/E29584_01/webhelp/mdex_basicDev/src/rbdv_chars_mapping.html // 考虑参考更新映射
    $char = [
        'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A',
        'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C',
        'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I',
        'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U',
        'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'à'=>'a', 'á'=>'a', 'â'=>'a',
        'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e',
        'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
        'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o',
        'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u',
        'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y',
        'ß'=>'s', // 映射由`ss`改为`s`
    ];

    return strtr($str, $char);
}
