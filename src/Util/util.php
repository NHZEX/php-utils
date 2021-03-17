<?php

declare(strict_types=1);

namespace Zxin\Util;

use Exception;
use RuntimeException;
use function base64_decode;
use function base64_encode;
use function bin2hex;
use function chr;
use function ord;
use function random_bytes;
use function rtrim;
use function str_repeat;
use function str_split;
use function strlen;
use function strtr;
use function vsprintf;
use function count;
use function abs;
use function sprintf;
use function round;

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
    $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];
    $count = count($units) - 1;
    $pos  = 0;

    $minus = $byte < 0;
    $byte = abs($byte);

    while ($byte >= 1024 && $pos < $count) {
        $byte /= 1024;
        $pos++;
    }

    $result = sprintf('%.2f', round($byte * ($minus ? -1 : 1), $dec));

    return "{$result} {$units[$pos]}";
}
