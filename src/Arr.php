<?php
declare(strict_types=1);

namespace Zxin;

use function array_column;
use function call_user_func_array;
use function is_string;

class Arr
{
    /**
     * 多维数组指定多字段排序
     * order：SORT_ASC升序 , SORT_DESC降序
     * flags：详情 \array_multisort
     * 示例：arrayMultisortField($arr, 'num', SORT_DESC, 'sort', SORT_ASC)
     * @like https://www.php.net/manual/zh/function.array-multisort.php
     * @like https://blog.csdn.net/qq_35296546/article/details/78812176
     * @param array $arr
     * @param array<int, string|int> $args
     * @return array
     */
    public static function multiFieldSort(array $arr, ...$args): array
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
}
