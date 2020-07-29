<?php
declare(strict_types=1);

namespace Zxin\Crypto;

use RuntimeException;
use function hash_equals;
use function hash_hmac;
use function http_build_query;
use function is_array;
use function ksort;
use function openssl_cipher_iv_length;
use function openssl_decrypt;
use function openssl_encrypt;
use function openssl_random_pseudo_bytes;
use function strrchr;
use function strtolower;
use function substr;

/**
 * @param array|string $data     待签数据
 * @param string       $password 签名秘钥
 * @param string       $algo
 * @param bool         $raw_output
 * @return string
 */
function sign_data($data, string $password, $algo = 'sha1', $raw_output = false): string
{
    if (is_array($data)) {
        ksort($data);
        $data = http_build_query($data);
    }

    $sign = hash_hmac($algo, $data, $password, $raw_output);

    return $sign;
}

/**
 * @param string       $sign     需验证签名
 * @param array|string $data     验签数据
 * @param string       $password 签名秘钥
 * @param string       $algo
 * @param bool         $raw_output
 * @return bool
 */
function sign_verify(string $sign, $data, string $password, string $algo = 'sha1', bool $raw_output = false)
{
    return hash_equals(sign_data($data, $password, $algo, $raw_output), $sign);
}

/**
 * @param string      $data     待加密数据
 * @param string      $password 加密秘钥
 * @param string      $method   加密算法名称
 * @param string|null $add
 * @return string         已加密数据
 */
function encrypt_data(string $data, string $password, string $method = 'aes-128-cfb', ?string $add = null): string
{
    $method = strtolower($method);
    $mode = strrchr($method, '-');

    $iv = '';
    $tag = '';
    $parame = [];
    $ivSize = openssl_cipher_iv_length($method);
    if (!empty($ivSize)) {
        $iv = openssl_random_pseudo_bytes($ivSize);
    }
    if ('-ccm' === $mode || '-gcm' === $mode) {
        $parame = [&$tag, $add, 16];
    }
    $output = openssl_encrypt($data, $method, $password, OPENSSL_RAW_DATA, $iv, ...$parame);
    if (false === $output) {
        throw new RuntimeException("openssl encrypt [$method] failure: " . openssl_error_string());
    }
    return $iv . $tag . $output;
}

/**
 * @param string      $data     待加密数据
 * @param string      $password 加密秘钥
 * @param string      $method   加密算法名称
 * @param string|null $add
 * @return string         已加密数据
 */
function decrypt_data(string $data, string $password, string $method = 'aes-128-cfb', ?string $add = null)
{
    $method = strtolower($method);
    $mode = strrchr($method, '-');
    $ivSize = openssl_cipher_iv_length($method) ?: 0;
    if ('-ccm' === $mode || '-gcm' === $mode) {
        [$iv, $tag, $data] = [
            substr($data, 0, $ivSize),
            substr($data, $ivSize, 16),
            substr($data, $ivSize + 16)
        ];
        $parame = [$iv, $tag, $add];
    } else {
        [$iv, $data] = [substr($data, 0, $ivSize), substr($data, $ivSize)];
        $parame = [$iv];
    }
    $output = openssl_decrypt($data, $method, $password, OPENSSL_RAW_DATA, ...$parame);
    if (false === $output) {
        throw new RuntimeException("openssl decrypt [$method] failure: " . openssl_error_string());
    }
    return $output;
}
