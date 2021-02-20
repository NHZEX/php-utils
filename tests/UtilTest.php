<?php

namespace Zxin\Tests;

use Zxin\Util;
use function hex2bin;
use function strlen;

class UtilTest extends Base
{
    /**
     * @dataProvider toUpperCamelCaseProvider
     * @param string $str
     * @param string $expected
     */
    public function testToUpperCamelCase(string $str, string $expected)
    {
        $this->assertEquals($expected, Util::toLowerCamelCase($str));
        // 缓存生效
        $this->assertEquals($expected, Util::toLowerCamelCase($str));
    }

    public function toUpperCamelCaseProvider(): array
    {
        return [
            ['qwe_asd_zxc', 'qweAsdZxc'],
            ['qwe_Asd_Zxc', 'qweAsdZxc'],
            ['qweAsd_Zxc', 'qweAsdZxc'],
        ];
    }

    /**
     * @dataProvider toSnakeCaseProvider
     * @param string $str
     * @param string $expected
     */
    public function testToSnakeCase(string $str, string $expected)
    {
        $this->assertEquals($expected, Util::toUpperCamelCase($str));
    }

    public function toSnakeCaseProvider(): array
    {
        return [
            ['qwe_asd_zxc', 'QweAsdZxc'],
            ['qwe_Asd_Zxc', 'QweAsdZxc'],
            ['qweAsd_Zxc', 'QweAsdZxc'],
        ];
    }

    /**
     * @dataProvider toLowerCamelCaseProvider
     * @param string $str
     * @param string $expected
     */
    public function testToLowerCamelCase(string $str, string $expected)
    {
        $this->assertEquals($expected, Util::toSnakeCase($str));
        $this->assertEquals($expected, Util::toSnakeCase($str));
        $this->assertEquals($expected, Util::toSnakeCase($str));
    }

    public function toLowerCamelCaseProvider()
    {
        return [
            ['qweAsdZxc', 'qwe_asd_zxc'],
            ['QweAsdZxc', 'qwe_asd_zxc'],
            ['qwe_AsdZxc', 'qwe_asd_zxc'],
        ];
    }

    /**
     * @requires extension posix
     * @requires function shell_exec
     */
    public function testWhoami()
    {
        $this->assertEquals(trim(shell_exec('whoami')), Util::whoami());
    }

    public function base64UrlProvider(): array
    {
        return [
            [hex2bin('d19085537f7aebf0ca16beebea'), '0ZCFU3966_DKFr7r6g'],
            [hex2bin('24c7679be53f81a190a5032219'), 'JMdnm-U_gaGQpQMiGQ'],
        ];
    }

    /**
     * @dataProvider base64UrlProvider
     * @param string $plaintext
     * @param string $ciphertext
     */
    public function testBase64Url(string $plaintext, string $ciphertext)
    {
        $result = Util\base64_urlsafe_encode($plaintext);
        $this->proxyAssertDoesNotMatchRegularExpression('~(=|\+|\/)~', $result);
        $this->assertEquals($ciphertext, $result);
        $output = Util\base64_urlsafe_decode($result);
        $this->assertEquals($plaintext, $output);
    }

    /**
     * @requires extension openssl
     */
    public function testUuidV4()
    {
        $uuid = Util\uuidv4();
        $this->assertEquals(36, strlen($uuid));
    }
}
