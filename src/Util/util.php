<?php
declare(strict_types=1);

namespace Zxin\Util;

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
