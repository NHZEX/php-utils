<?php

namespace Zxin\Arr;

use Generator;
use function array_column;
use function call_user_func_array;
use function is_string;

/**
 * 多维数组指定多字段排序
 * order：SORT_ASC升序 , SORT_DESC降序
 * flags：详情 \array_multisort
 * 示例：arrayMultisortField($arr, 'num', SORT_DESC, 'sort', SORT_ASC)
 * @like https://www.php.net/manual/zh/function.array-multisort.php
 * @like https://blog.csdn.net/qq_35296546/article/details/78812176
 * @param array $arr
 * @param array $args
 * @return array
 */
function array_multi_field_sort(array $arr, ...$args): array
{
    foreach ($args as $key => $field) {
        if (is_string($field)) {
            $args[$key] = array_column($arr, $field);
        }
    }
    $args[] = &$arr;
    call_user_func_array('\array_multisort', $args);
    return $arr;
}

/**
 * @param array  $a
 * @param string $previous
 * @return array
 */
function array_flatten(array $a, string $previous = ''): array
{
    $mapping = [];
    foreach ($a as $key => $value) {
        if (!is_array($value)) {
            $mapping[$previous . $key] = $value;
        } else {
            $mapping += array_flatten($value, $previous . $key . '_');
        }
    }
    if (empty($previous)) {
        ksort($mapping);
    }
    return $mapping;
}

function array_lazy_chunk(iterable $items, int $limit): Generator
{
    $buffer = [];
    $i = 0;
    foreach ($items as $key => $item) {
        $buffer[$key] = $item;
        $i++;
        if ($i >= $limit) {
            yield $buffer;
            $buffer = [];
            $i = 0;
        }
    }

    if (!empty($buffer)) {
        yield $buffer;
    }
}