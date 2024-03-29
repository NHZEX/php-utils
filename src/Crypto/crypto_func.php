<?php

declare(strict_types=1);

namespace Zxin\Crypto;

use LengthException;
use RuntimeException;
use function hash_equals;
use function hash_hmac;
use function http_build_query;
use function in_array;
use function is_array;
use function ksort;
use function openssl_cipher_iv_length;
use function openssl_decrypt;
use function openssl_encrypt;
use function openssl_error_string;
use function openssl_random_pseudo_bytes;
use function sprintf;
use function str_ends_with;
use function strlen;
use function strtolower;
use function substr;

const AES_KEY_SIZES = [16, 24, 32];

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

    return hash_hmac($algo, $data, $password, $raw_output);
}

/**
 * @param string       $sign     需验证签名
 * @param array|string $data     验签数据
 * @param string       $password 签名秘钥
 * @param string       $algo
 * @param bool         $raw_output
 * @return bool
 */
function sign_verify(string $sign, $data, string $password, string $algo = 'sha1', bool $raw_output = false): bool
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
function encrypt_data(string $data, string $password, string $method = 'aes-128-cbc', ?string $add = null): string
{
    $method = strtolower($method);

    $iv = '';
    $tag = '';
    $params = [];
    $ivSize = openssl_cipher_iv_length($method);
    if (!empty($ivSize)) {
        $iv = openssl_random_pseudo_bytes($ivSize);
    }
    if (str_ends_with($method, 'gcm') || str_ends_with($method, 'ccm') || str_ends_with($method, 'ocb')) {
        $params = [&$tag, $add, 16];
    }
    $output = openssl_encrypt($data, $method, $password, OPENSSL_RAW_DATA, $iv, ...$params);
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
 * @return string
 */
function decrypt_data(string $data, string $password, string $method = 'aes-128-cbc', ?string $add = null): string
{
    $method = strtolower($method);
    $ivSize = openssl_cipher_iv_length($method) ?: 0;
    if (str_ends_with($method, 'gcm') || str_ends_with($method, 'ccm') || str_ends_with($method, 'ocb')) {
        [$iv, $tag, $data] = [
            substr($data, 0, $ivSize),
            substr($data, $ivSize, 16),
            substr($data, $ivSize + 16)
        ];
        $params = [$iv, $tag, $add];
    } else {
        [$iv, $data] = [substr($data, 0, $ivSize), substr($data, $ivSize)];
        $params = [$iv];
    }
    $output = openssl_decrypt($data, $method, $password, OPENSSL_RAW_DATA, ...$params);
    if (false === $output) {
        throw new RuntimeException("openssl decrypt [$method] failure: " . openssl_error_string());
    }
    return $output;
}

function aes_gcm_encrypt(string $content, string $key): string
{
    if (!in_array($keyLen = strlen($key), AES_KEY_SIZES)) {
        throw new LengthException(sprintf('invalid key length: %d', $keyLen));
    }
    $aesBitSize = $keyLen * 8;
    $iv = openssl_random_pseudo_bytes(12);
    $tagLen = 16;
    $add = '';

    $ciphertext = openssl_encrypt($content, "aes-{$aesBitSize}-gcm", $key, OPENSSL_RAW_DATA, $iv, $tag, $add, $tagLen);
    if (false === $ciphertext) {
        throw new RuntimeException("aes encrypt fail: " . openssl_error_string());
    }
    return $iv . $ciphertext . $tag;
}

function aes_gcm_decrypt(string $content, string $key): string
{
    if (!in_array($keyLen = strlen($key), AES_KEY_SIZES)) {
        throw new LengthException(sprintf('invalid key length: %d', $keyLen));
    }

    $aesBitSize = $keyLen * 8;
    $iv = substr($content, 0, 12);
    $tag = substr($content, -16);
    $add = '';
    $ciphertext = substr($content, 12, -16);

    $plaintext = openssl_decrypt($ciphertext, "aes-{$aesBitSize}-gcm", $key, OPENSSL_RAW_DATA, $iv, $tag, $add);
    if ($plaintext === false) {
        throw new RuntimeException("aes decrypt fail: " . openssl_error_string());
    }
    return $plaintext;
}
