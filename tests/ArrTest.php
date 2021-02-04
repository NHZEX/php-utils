<?php

namespace Zxin\Tests;

use PHPUnit\Framework\TestCase;
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
}
