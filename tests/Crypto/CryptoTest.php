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
        foreach (openssl_get_cipher_methods() as $method) {
            yield ['aaaaaaaaaa', '123456789', $method];
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
        try {
            $enc = encrypt_data($data, $password, $method);
            $verify = decrypt_data($enc, $password, $method);
        } catch (\Throwable $exception) {
            var_dump($method);
            throw $exception;
        }


        $this->assertEquals($data, $verify);
    }
}
