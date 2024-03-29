<?php

namespace Zxin\Tests;

use PHPUnit\Framework\TestCase;
use function array_chunk;
use function range;
use function Zxin\Arr\array_flatten;
use function Zxin\Arr\array_group;
use function Zxin\Arr\array_index_cb;
use function Zxin\Arr\array_lazy_chunk;
use function Zxin\Arr\array_multi_field_sort;

class ArrTest extends TestCase
{
    public function multiFieldSortProvider()
    {
        return [
            [
                [
                    ['id' => 1, 'num' => 1, 'name' => '11-11'],
                    ['id' => 2, 'num' => 4, 'name' => '24-24'],
                    ['id' => 3, 'num' => 3, 'name' => '33-33'],
                    ['id' => 3, 'num' => 2, 'name' => '32-32'],
                    ['id' => 4, 'num' => 7, 'name' => '47-47'],
                    ['id' => 6, 'num' => 5, 'name' => '65-65'],
                    ['id' => 6, 'num' => 2, 'name' => '62-62'],
                ],
                [
                    ['id' => 1, 'num' => 1, 'name' => '11-11'],
                    ['id' => 3, 'num' => 3, 'name' => '33-33'],
                    ['id' => 2, 'num' => 4, 'name' => '24-24'],
                    ['id' => 4, 'num' => 7, 'name' => '47-47'],
                    ['id' => 6, 'num' => 2, 'name' => '62-62'],
                    ['id' => 6, 'num' => 5, 'name' => '65-65'],
                    ['id' => 3, 'num' => 2, 'name' => '32-32'],
                ],
                ['id', SORT_ASC, 'num', SORT_DESC],
            ], [
                [
                    'aa' => ['id' => 1, 'num' => 1, 'name' => '11-11'],
                    'ac' => ['id' => 2, 'num' => 4, 'name' => '24-24'],
                    'ab' => ['id' => 3, 'num' => 3, 'name' => '33-33'],
                    'ag' => ['id' => 3, 'num' => 2, 'name' => '32-32'],
                    'ad' => ['id' => 4, 'num' => 7, 'name' => '47-47'],
                    'af' => ['id' => 6, 'num' => 5, 'name' => '65-65'],
                    'ae' => ['id' => 6, 'num' => 2, 'name' => '62-62'],
                ],
                [
                    'aa' => ['id' => 1, 'num' => 1, 'name' => '11-11'],
                    'ab' => ['id' => 3, 'num' => 3, 'name' => '33-33'],
                    'ac' => ['id' => 2, 'num' => 4, 'name' => '24-24'],
                    'ad' => ['id' => 4, 'num' => 7, 'name' => '47-47'],
                    'ae' => ['id' => 6, 'num' => 2, 'name' => '62-62'],
                    'af' => ['id' => 6, 'num' => 5, 'name' => '65-65'],
                    'ag' => ['id' => 3, 'num' => 2, 'name' => '32-32'],
                ],
                ['id', SORT_ASC, 'num', SORT_DESC],
            ], [
                [
                    0 => ['id' => 1, 'num' => 1, 'name' => '11-11'],
                    1 => ['id' => 2, 'num' => 4, 'name' => '24-24'],
                    2 => ['id' => 3, 'num' => 3, 'name' => '33-33'],
                    3 => ['id' => 3, 'num' => 2, 'name' => '32-32'],
                    4 => ['id' => 4, 'num' => 7, 'name' => '47-47'],
                    5 => ['id' => 6, 'num' => 5, 'name' => '65-65'],
                    6 => ['id' => 6, 'num' => 2, 'name' => '62-62'],
                ],
                [
                    '11' => ['id' => 1, 'num' => 1, 'name' => '11-11'],
                    '12' => ['id' => 3, 'num' => 3, 'name' => '33-33'],
                    '13' => ['id' => 2, 'num' => 4, 'name' => '24-24'],
                    '14' => ['id' => 4, 'num' => 7, 'name' => '47-47'],
                    '15' => ['id' => 6, 'num' => 2, 'name' => '62-62'],
                    '16' => ['id' => 6, 'num' => 5, 'name' => '65-65'],
                    '17' => ['id' => 3, 'num' => 2, 'name' => '32-32'],
                ],
                ['id', SORT_ASC, 'num', SORT_DESC],
            ],
        ];
    }

    /**
     * @dataProvider multiFieldSortProvider
     * @param array $expected
     * @param array $array1
     * @param array $array2
     */
    public function testMultiFieldSort(array $expected, array $array1, array $array2)
    {
        $this->assertEquals($expected, array_multi_field_sort($array1, ...$array2));
    }

    public function testArrayFlatten()
    {
        $this->assertEquals(
            [
                'asd'       => 123,
                'qwe'       => true,
                'uio_1'     => 1,
                'uio_2'     => 2,
                'uio_3'     => 3,
                'uio_4'     => 4,
                'uio_5_asd' => 1,
                'uio_5_fgh' => 4,
                'uio_5_rty' => 3,
                'uio_5_zxc' => 2,
                'zxc_1'     => 5,
                'zxc_2'     => 6,
                'zxc_3'     => 7,
                'zxc_fgh'   => 3,
                'zxc_rty'   => 2,
                'zxc_vbn'   => 4,
                'zxc_zxc'   => 1,
            ],
            array_flatten([
                'zxc' => [
                    2     => 6,
                    'zxc' => 1,
                    'rty' => 2,
                    3     => 7,
                    'fgh' => 3,
                    'vbn' => 4,
                    1     => 5,
                ],
                'qwe' => true,
                'uio' => [
                    3 => 3,
                    4 => 4,
                    5 => ['rty' => 3, 'fgh' => 4, 'asd' => 1, 'zxc' => 2],
                    1 => 1,
                    2 => 2,
                ],
                'asd' => 123,
            ])
        );
    }

    public function testArrayLazyChunk()
    {
        $array = range(0, 100, 1);

        $result = [];
        foreach (array_lazy_chunk($array, 10) as $item) {
            $result[] = $item;
        }

        $this->assertEquals(array_chunk($array, 10, true), $result);
    }

    public function testArrayIndexCallBack()
    {
        $items = [
            ['k' => 'a', 'i' => 1],
            ['k' => 'b', 'i' => 2],
            ['k' => 'b', 'i' => 3],
            ['k' => 'c', 'i' => 4],
            ['k' => 'c', 'i' => 5],
        ];

        $output = array_index_cb($items, function ($val, $key) {
            return $val['i'] . $val['k'] . $key;
        });

        $this->assertEquals([
            '1a0' => ['k' => 'a', 'i' => 1],
            '2b1' => ['k' => 'b', 'i' => 2],
            '3b2' => ['k' => 'b', 'i' => 3],
            '4c3' => ['k' => 'c', 'i' => 4],
            '5c4' => ['k' => 'c', 'i' => 5],
        ], $output);
    }

    public function testArrayGroup()
    {
        $items = [
            ['k' => 'a', 'i' => 1],
            ['k' => 'b', 'i' => 2],
            ['k' => 'b', 'i' => 3],
            ['k' => 'c', 'i' => 4],
            ['k' => 'c', 'i' => 5],
        ];

        $output = array_group($items, 'k');

        $this->assertEquals([
            'a' => [
                0 => ['k' => 'a', 'i' => 1],
            ],
            'b' => [
                1 => ['k' => 'b', 'i' => 2],
                2 => ['k' => 'b', 'i' => 3],
            ],
            'c' => [
                3 => ['k' => 'c', 'i' => 4],
                4 => ['k' => 'c', 'i' => 5],
            ],
        ], $output);

        $output = array_group($items, function ($item) {
            return $item['i'] % 2;
        });

        $this->assertEquals([
            0 => [
                1 => ['k' => 'b', 'i' => 2],
                3 => ['k' => 'c', 'i' => 4],
            ],
            1 => [
                0 => ['k' => 'a', 'i' => 1],
                2 => ['k' => 'b', 'i' => 3],
                4 => ['k' => 'c', 'i' => 5],
            ],
        ], $output);

        $output = array_group($items, function ($_, $key) {
            return $key % 2 ? 'a' : 'b';
        });

        $this->assertEquals([
            'b' => [
                0 => ['k' => 'a', 'i' => 1],
                2 => ['k' => 'b', 'i' => 3],
                4 => ['k' => 'c', 'i' => 5],
            ],
            'a' => [
                1 => ['k' => 'b', 'i' => 2],
                3 => ['k' => 'c', 'i' => 4],
            ],
        ], $output);

        $output = array_group($items, function ($_, $key) {
            return $key % 2 ? 'a' : 'b';
        }, false);

        $this->assertEquals([
            'b' => [
                ['k' => 'a', 'i' => 1],
                ['k' => 'b', 'i' => 3],
                ['k' => 'c', 'i' => 5],
            ],
            'a' => [
                ['k' => 'b', 'i' => 2],
                ['k' => 'c', 'i' => 4],
            ],
        ], $output);
    }
}
