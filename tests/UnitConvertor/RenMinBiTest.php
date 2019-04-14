<?php

namespace Tests\UnitConvertor;

use HZEX\UnitConvertor\RenMinBi;
use PHPUnit\Framework\TestCase;

class RenMinBiTest extends TestCase
{
    public function fenToYuanProvider()
    {
        return [
            [100, 1],
            [57, 0.57],
            [57, '0.57'],
        ];
    }

    public function yuanToFenProvider()
    {
        return [
            [57, 5700],
            [0.57, 57],
            [0.571, 57],
            [0.57, '57'],
        ];
    }

    /**
     * @dataProvider fenToYuanProvider
     * @param $yuan
     * @param $fen
     */
    public function testFenToYuan($fen, $yuan)
    {
        $this->assertEquals($yuan, RenMinBi::fenToYuan($fen));
    }

    /**
     * @dataProvider yuanToFenProvider
     * @param $yuan
     * @param $fen
     */
    public function testYuanToFen($yuan, $fen)
    {
        $this->assertEquals($fen, RenMinBi::yuanToFen($yuan));
    }
}
