<?php
declare(strict_types=1);

namespace HZEX\Tests\Crypto;

use PHPUnit\Framework\TestCase;
use function HZEX\Crypto\decrypt_data;
use function HZEX\Crypto\encrypt_data;
use function HZEX\Crypto\sign_data;
use function HZEX\Crypto\sign_verify;

class CryptoTest extends TestCase
{

    public function signDataProvider()
    {
        return [
            [[[1, 2, 3, 4], '123123'], [[1, 2, 3, 4], '123123']],
            [[['a' => 1, 'b' => 2, 'c' => 3], '123123'], [['c' => 3, 'b' => 2, 'a' => 1], '123123']],
            [['11111', '123123'], ['11111', '123123']],
            [['11111', '123123'], ['22222', '123123'], false],
        ];
    }

    /**
     * @dataProvider signDataProvider
     * @param array $args1
     * @param array $args2
     * @param bool  $result
     */
    public function testSignData(array $args1, array $args2, $result = true)
    {
        $sign = sign_data(...$args1);
        $verify = sign_verify($sign, ...$args2);

        $this->assertEquals($result, $verify);
    }

    public function encryptDataProvider()
    {
        $ignore_methods = [
            'aes-128-ocb',
            'aes-192-ocb',
            'aes-256-ocb',
            'id-aes128-wrap',
            'id-aes192-wrap',
            'id-aes256-wrap',
            'id-aes128-wrap-8',
            'id-aes192-wrap-8',
            'id-aes256-wrap-8',
            'id-aes128-wrap-pad',
            'id-aes192-wrap-pad',
            'id-aes256-wrap-pad',
            'id-aes128-wrap-pad-4',
            'id-aes192-wrap-pad-4',
            'id-aes256-wrap-pad-4',
            'id-smime-alg-cms3deswrap',
        ];
        $real_ignore = array_flip($ignore_methods);
        foreach (openssl_get_cipher_methods() as $method) {
            if (isset($real_ignore[strtolower($method)])) {
                echo 'not support openssl method: ' . $method . PHP_EOL;
                continue;
            }
            yield [openssl_random_pseudo_bytes(64), openssl_random_pseudo_bytes(8), $method];
            yield [openssl_random_pseudo_bytes(64), openssl_random_pseudo_bytes(32), $method];
            yield [openssl_random_pseudo_bytes(64), openssl_random_pseudo_bytes(64), $method];
            yield [openssl_random_pseudo_bytes(64), openssl_random_pseudo_bytes(128), $method];
        }
    }

    /**
     * @dataProvider encryptDataProvider
     * @param string $data
     * @param string $password
     * @param string $method
     */
    public function testEncryptData(string $data, string $password, string $method)
    {
        $add = strpos($method, '-ccm') >= 0 ? $password : null;
        $enc = encrypt_data($data, $password, $method, $add);
        $verify = decrypt_data($enc, $password, $method, $add);

        $this->assertEquals($data, $verify);
    }
}
