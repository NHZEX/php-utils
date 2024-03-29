<?php

declare(strict_types=1);

namespace Zxin\Util;

use Exception;
use RuntimeException;
use Zxin\Util;
use function base64_decode;
use function base64_encode;
use function bin2hex;
use function chr;
use function filter_var;
use function is_dir;
use function is_writable;
use function ltrim;
use function ord;
use function random_bytes;
use function rtrim;
use function str_repeat;
use function str_replace;
use function str_split;
use function strip_tags;
use function strlen;
use function strrpos;
use function strtr;
use function substr;
use function sys_get_temp_dir;
use function uniqid;
use function vsprintf;

/**
 * Base64 Url安全编码
 * @param string $data
 * @return string
 * @link http://php.net/manual/zh/function.base64-encode.php
 */
function base64_urlsafe_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Base64 Url安全解码
 * @param string $data
 * @param bool   $strict
 * @return false|string
 * @link http://php.net/manual/zh/function.base64-encode.php
 */
function base64_urlsafe_decode(string $data, bool $strict = true)
{
    if ($remainder = strlen($data) % 4) {
        $data .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($data, '-_', '+/'), $strict);
}

/**
 * 生成 uuid v4
 * @return string
 * @link https://stackoverflow.com/a/15875555/10242420
 */
function uuidv4(): string
{
    try {
        $data = random_bytes(16);
    } catch (Exception $e) {
        throw new RuntimeException('uuidv4 generate fail', 1, $e);
    }

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * @param int $byte
 * @param int $dec
 * @return string
 */
function format_byte(int $byte, int $dec = 2): string
{
    return Util::formatByte($byte, $dec);
}

function get_temp_dir(bool $allowShmDir = false): string
{
    if (!$allowShmDir) {
        return sys_get_temp_dir();
    }

    if (is_dir('/dev/shm') && is_writable('/dev/shm')) {
        $cacheDir = '/dev/shm';
    } elseif (is_dir('/run/shm') && is_writable('/run/shm')) {
        $cacheDir = '/run/shm';
    } else {
        $cacheDir = sys_get_temp_dir();
    }

    return $cacheDir;
}

function get_temp_filename(string $prefix, string $suffix, bool $allowShmDir = false): string
{
    return get_temp_dir($allowShmDir) . DIRECTORY_SEPARATOR . uniqid($prefix, true) . $suffix;
}

/**
 * @param string|string[]|null $allowable_tags
 * @link https://stackoverflow.com/a/38200395
 */
function strip_tags_with_whitespace(string $string, $allowable_tags = null): string
{
    $string = str_replace('<', ' <', $string);
    $string = strip_tags($string, $allowable_tags);
    $string = str_replace('  ', ' ', $string);
    return ltrim($string, ' ');
}

function parse_str_to_ip_and_port(string $str): ?array
{
    if (empty($str)) {
        return null;
    }
    // ipv6
    if (($pos = strrpos($str, ']')) !== false) {
        $ip = substr($str, 1, $pos - 1);
        $port = substr($str, $pos + 2);
    } elseif (($pos = strrpos($str, ':')) !== false) {
        $ip = substr($str, 0, $pos);
        $port = substr($str, $pos + 1);
    } else {
        $ip = $str;
        $port = '';
    }

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return null;
    }
    // 验证是有效的端口号范围
    if (
        $port
        && !filter_var(
            $port,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 65535]]
        )
    ) {
        return null;
    }
    return [$ip, $port];
}
