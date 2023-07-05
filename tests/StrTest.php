<?php
declare(strict_types=1);

namespace Zxin\Tests;

use PHPUnit\Framework\TestCase;
use function Zxin\Str\str_fullwidth_to_ascii;
use function Zxin\Str\str_replace_umlaut_unaccent;
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

    public function strFullwidthToAsciiProvider()
    {
        return [
            ['　', ' '], ['！', '!'], ['＂', '"'], ['＃', '#'], ['＄', '$'], ['％', '%'], ['＆', '&'], ['＇', '\''],
            ['（', '('], ['）', ')'], ['＊', '*'], ['＋', '+'], ['，', ','], ['－', '-'], ['．', '.'], ['／', '/'],
            ['０', '0'], ['１', '1'], ['２', '2'], ['３', '3'], ['４', '4'], ['５', '5'], ['６', '6'], ['７', '7'], ['８', '8'], ['９', '9'],
            ['：', ':'], ['；', ';'], ['＜', '<'], ['＝', '='], ['＞', '>'], ['？', '?'], ['＠', '@'],
            ['Ａ', 'A'], ['Ｂ', 'B'], ['Ｃ', 'C'], ['Ｄ', 'D'], ['Ｅ', 'E'], ['Ｆ', 'F'], ['Ｇ', 'G'], ['Ｈ', 'H'], ['Ｉ', 'I'], ['Ｊ', 'J'], ['Ｋ', 'K'], ['Ｌ', 'L'], ['Ｍ', 'M'],
            ['Ｎ', 'N'], ['Ｏ', 'O'], ['Ｐ', 'P'], ['Ｑ', 'Q'], ['Ｒ', 'R'], ['Ｓ', 'S'], ['Ｔ', 'T'], ['Ｕ', 'U'], ['Ｖ', 'V'], ['Ｗ', 'W'], ['Ｘ', 'X'], ['Ｙ', 'Y'], ['Ｚ', 'Z'],
            ['［', '['], ['＼', '\\'], ['］', ']'], ['＾', '^'], ['＿', '_'], ['｀', '`'],
            ['ａ', 'a'], ['ｂ', 'b'], ['ｃ', 'c'], ['ｄ', 'd'], ['ｅ', 'e'], ['ｆ', 'f'], ['ｇ', 'g'], ['ｈ', 'h'], ['ｉ', 'i'], ['ｊ', 'j'], ['ｋ', 'k'], ['ｌ', 'l'], ['ｍ', 'm'],
            ['ｎ', 'n'], ['ｏ', 'o'], ['ｐ', 'p'], ['ｑ', 'q'], ['ｒ', 'r'], ['ｓ', 's'], ['ｔ', 't'], ['ｕ', 'u'], ['ｖ', 'v'], ['ｗ', 'w'], ['ｘ', 'x'], ['ｙ', 'y'], ['ｚ', 'z'],
            ['｛', '{'], ['｜', '|'], ['｝', '}'], ['～', '~'],
        ];
    }

    /**
     * @dataProvider strFullwidthToAsciiProvider
     * @param string $fullwidth
     * @param string $ascii
     */
    public function testStrFullwidthToAscii(string $fullwidth, string $ascii)
    {
        $this->assertEquals($ascii, str_fullwidth_to_ascii($fullwidth));
    }

    public function strReplaceUmlautUnaccentProvider(): array
    {
        return [
            ['Š', 'S'],
            ['á', 'a'],
            ['áa?', 'aa?'],
        ];
    }

    /**
     * @dataProvider strReplaceUmlautUnaccentProvider
     * @param string $str1
     * @param string $str2
     */
    public function testStrReplaceUmlautUnaccent(string $str1, string $str2)
    {
        $this->assertEquals($str2, str_replace_umlaut_unaccent($str1));
    }
}
