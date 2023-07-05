<?php

namespace Zxin\Arr;

use Generator;
use function array_column;
use function call_user_func_array;
use function is_array;
use function is_callable;
use function is_string;
use function ksort;

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
 * @deprecated
 */
function array_flatten(array $a, string $previous = ''): array
{
    return array_flatten_ex($a, '_', true, $previous);
}

function array_flatten_ex(array $array, string $separator = '.', bool $sort = true, string $previous = ''): array
{
    $mapping = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $mapping += array_flatten_ex($value, $separator, false, $previous . $key . $separator);
        } else {
            $mapping[$previous . $key] = $value;
        }
    }
    if ($sort) {
        ksort($mapping);
    }
    return $mapping;
}

/**
 * @template T
 * @param iterable<T> $items
 * @param int $limit
 * @param bool $preserveKeys
 * @return Generator<int, array<T>>
 */
function array_lazy_chunk(iterable $items, int $limit, bool $preserveKeys = true): Generator
{
    $buffer = [];
    $i = 0;
    foreach ($items as $key => $item) {
        if ($preserveKeys) {
            $buffer[$key] = $item;
        } else {
            $buffer[] = $item;
        }
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

/**
 * @template T
 * @template K
 * @param array<K, T> $arr
 * @param callable(T, K): int|string $cb
 * @return array<int|string, T>
 */
function array_index_cb(array $arr, callable $cb): array
{
    $output = [];
    foreach ($arr as $key => $item) {
        $output[$cb($item, $key)] = $item;
    }

    return $output;
}

/**
 * @param iterable            $arr
 * @param int|string|callable $groupKey
 * @param bool                $preserveKeys
 * @return array
 */
function array_group(iterable $arr, $groupKey, bool $preserveKeys = true): array
{
    $isCall = is_callable($groupKey);
    $output = [];

    foreach ($arr as $k => $item) {
        if ($isCall) {
            $key = $groupKey($item, $k);
        } else {
            $key = $item[$groupKey];
        }

        if ($preserveKeys) {
            $output[$key][$k] = $item;
        } else {
            $output[$key][] = $item;
        }
    }

    return $output;
}

/**
 * @template TK of string|int
 * @template TV of mixed
 * @param callable $cb
 * @param array<TK, TV> $arr
 * @return array<TK, mixed>
 */
function array_map_with_key(callable $cb, array $arr): array
{
    $out = [];
    foreach ($arr as $k => $v) {
        $out[$k] = $cb($v, $k);
    }

    return $out;
}
