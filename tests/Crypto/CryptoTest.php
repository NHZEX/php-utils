<?php
declare(strict_types=1);

namespace Zxin\Tests\Crypto;

use LengthException;
use PHPUnit\Framework\TestCase;
use function openssl_random_pseudo_bytes;
use function str_ends_with;
use function str_repeat;
use function strtolower;
use function Zxin\Crypto\aes_gcm_decrypt;
use function Zxin\Crypto\aes_gcm_encrypt;
use function Zxin\Crypto\decrypt_data;
use function Zxin\Crypto\encrypt_data;
use function Zxin\Crypto\sign_data;
use function Zxin\Crypto\sign_verify;

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
        $ignoreMethodSuffix = [
            'wrap',
            'wrap-pad',
        ];
        foreach (openssl_get_cipher_methods() as $method) {
            foreach ($ignoreMethodSuffix as $suffix) {
                if (str_ends_with(strtolower($method), $suffix)) {
                    echo '> not support openssl method: ' . $method . PHP_EOL;
                    continue 2;
                }
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
        $method = strtolower($method);
        $add = str_ends_with($method, 'ccm') || str_ends_with($method, 'gcm') || str_ends_with($method, 'ocb')
            ? $password
            : null;
        $enc = encrypt_data($data, $password, $method, $add);
        $verify = decrypt_data($enc, $password, $method, $add);

        $this->assertEquals($data, $verify);
    }

    public function aesGcmProvider()
    {
        yield [openssl_random_pseudo_bytes(64), openssl_random_pseudo_bytes(16)];
        yield [openssl_random_pseudo_bytes(64), openssl_random_pseudo_bytes(24)];
        yield [openssl_random_pseudo_bytes(64), openssl_random_pseudo_bytes(32)];
    }

    /**
     * @dataProvider aesGcmProvider
     * @param string $data
     * @param string $password
     */
    public function testAesGcm(string $data, string $password)
    {
        $ciphertext = aes_gcm_encrypt($data, $password);
        $plaintext = aes_gcm_decrypt($ciphertext, $password);
        $this->assertEquals($data, $plaintext);
    }

    public function aesGcmExceptionProvider()
    {
        yield [str_repeat('0', 8)];
        yield [str_repeat('0', 38)];
    }

    /**
     * @dataProvider aesGcmExceptionProvider
     * @param string $password
     */
    public function testAesGcmException(string $password)
    {
        $this->expectException(LengthException::class);
        aes_gcm_encrypt('0', $password);
    }
}
