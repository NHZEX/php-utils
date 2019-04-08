<?php
declare(strict_types=1);

namespace HZEX\UnitConvertor;

/**
 * Class RenMinBi (RMB)
 * @package HZEX\UnitConvertor
 */
class RenMinBi
{
    /**
     * 换算 元转换分，
     * @param float|int|string $yuan
     * @return int
     */
    public static function yuanToFen($yuan): int
    {
        $result = (int) bcmul((string) $yuan, '100', 0);
        return $result;
    }

    /**
     * 换算 分转换元，
     * @param int|string $fen
     * @return float
     */
    public static function fenToYuan($fen): float
    {
        $result = (float) bcdiv((string) $fen, '100', 2);
        return $result;
    }
}
