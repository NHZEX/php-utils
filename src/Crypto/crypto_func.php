<?php
declare(strict_types=1);

namespace HZEX\Crypto;

use RuntimeException;

const BLOCK_LEN = [
    'aes128' => 16,
    'aes192' => 24,
    'aes256' => 32,
];

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
 * @param string $data     待加密数据
 * @param string $password 加密秘钥
 * @param string $method   加密算法名称
 * @return string         已加密数据
 */
function encrypt_data(string $data, string $password, string $method = 'AES-128-CFB'): string
{
    $method = strtolower($method);
    $mode = strrchr($method, '-');

    $add = '';
    $parame = [];
    if ('-ecb' === $mode) {
        $iv = '';
    } else {
        $iv_len = openssl_cipher_iv_length($method);
        if (false !== $iv_len) {
            $iv = openssl_random_pseudo_bytes($iv_len);
            if ('-ccm' === $mode || '-gcm' === $mode) {
                $tag = null;
                $parame = [$iv, &$tag, $add, 16];
            } elseif (false !== $iv) {
                $parame = [$iv];
            }
        }
    }
    $output = openssl_encrypt($data, $method, $password, OPENSSL_RAW_DATA, ...$parame);
    if (false === $output) {
        throw new RuntimeException('openssl operating failure: ' . openssl_error_string());
    }
    if (isset($iv)) {
        if (isset($tag)) {
            return $output . $iv . $tag;
        }
        return $output . $iv;
    }
    return $output;
}

/**
 * @param string $data     待加密数据
 * @param string $password 加密秘钥
 * @param string $method   加密算法名称
 * @return string         已加密数据
 */
function decrypt_data(string $data, string $password, string $method = 'AES-128-CFB')
{
    $method = strtolower($method);
    $mode = strrchr($method, '-');
    $tag = '';
    $add = '';
    if ('-ecb' === $mode) {
        $iv = '';
    } elseif ('-ccm' === $mode || '-gcm' === $mode) {
        $iv_len = openssl_cipher_iv_length($method);
        $iv_len *= -1;
        [$data, $iv, $tag] = [
            substr($data, 0, $iv_len - 16),
            substr($data, $iv_len - 16, $iv_len * -1),
            substr($data, -16)
        ];
    } else {
        $iv_len = openssl_cipher_iv_length($method);
        if (false === $iv_len) {
            $iv = '';
        } else {
            $iv_len *= -1;
            [$data, $iv] = [substr($data, 0, $iv_len), substr($data, $iv_len)];
        }

    }
    $output = openssl_decrypt($data, $method, $password, OPENSSL_RAW_DATA, $iv, $tag);
    if (false === $output) {
        throw new RuntimeException('openssl operating failure: ' . openssl_error_string());
    }
    return $output;
}
