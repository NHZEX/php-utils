<?php
declare(strict_types=1);

namespace Zxin\Tests;

use PHPUnit\Framework\TestCase;
use function Zxin\Str\strcut_omit;

class StrTest extends TestCase
{
    public function strcutOmitProvider()
    {
        return [
            ['abc', 'abcdefg', 1, ''], // 最少三字节
            ['abc', 'abcdefg', 3, ''], // 三字节
            ['abc...', 'abcdefg', 1, '...'], // 最少三字节 + 3字节填充
            ['测', '测试字abcd', 1, ''], // 最少一个字
            ['测试字abcd', '测试字abcd', 9 + 4 + 1, ''], // 全部字符 + 超出一位
            ['测...', '测试字abcd', 1, '...'], // 最少一个汉字 + 3字节填充
            ['测...', '测试字abcd', 2, '...'], // 最少一个汉字 + 3字节填充
            ['测试...', '测试字abcd', 6 + 3, '...'], // 两个汉字 + 3字节填充
            ['测试字ab...', '测试字abcd', 9 + 2 + 3, '...'], // 三个汉字 + 两个英文 + 3字节填充
            ['测试字abcd', '测试字abcd', 9 + 4 + 3 + 1, '...'], // 全部字 + 3字节填充 + 超出一位
        ];
    }

    /**
     * @dataProvider strcutOmitProvider
     * @requires     extension mbstring
     * @param string $expected
     * @param string $string1
     * @param int    $len
     * @param string $dot
     */
    public function testStrcutOmit(string $expected, string $string1, int $len, string $dot = '...')
    {
        $this->assertEquals($expected, strcut_omit($string1, $len, $dot));
    }
}
