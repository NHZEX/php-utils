<?php

namespace Zxin\Tests\DataStruct;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use StdClass;
use Zxin\DataStruct\ReflectionClassExpansion;

class ReflectionClassExpansionTest extends TestCase
{
    public function analysisSourceProvider()
    {
        $s1 = <<<'SOURCE'
<?php
declare(strict_types=1);
namespace Example;
use HZEX\DataStruct\Base;
use HZEX\DataStruct\ExtendedReflectionClass as Execccc1, HZEX\UnitConvertor\RenMinBi;
use HZEX\{
    Util as Execccc2,
    DataStruct\BaseProperty,
    DataStruct\ExtendedReflectionClass as Execccc3
};
use ReflectionException as Execccc5;
use DeepCopy\Filter\Doctrine\DoctrineEmptyCollectionFilter;
/**
 * Class StructChargeObject
 * @package app\logic\payment\struct
 * @property string $teller_no         [read] 支付号
 * @property string $order_no          [read] 订单号
 */
class StructChargeObject extends Base {}
SOURCE;
        $a1 = [
            [
                'class' => 'HZEX\\DataStruct\\Base',
                'alias' => null,
            ], [
                'class' => 'HZEX\\DataStruct\\ExtendedReflectionClass',
                'alias' => 'Execccc1',
            ], [
                'class' => 'HZEX\\UnitConvertor\\RenMinBi',
                'alias' => null,
            ], [
                'class' => 'HZEX\\Util',
                'alias' => 'Execccc2',
            ], [
                'class' => 'HZEX\\DataStruct\\BaseProperty',
                'alias' => null,
            ], [
                'class' => 'HZEX\\DataStruct\\ExtendedReflectionClass',
                'alias' => 'Execccc3',
            ], [
                'class' => 'ReflectionException',
                'alias' => 'Execccc5',
            ], [
                'class' => 'DeepCopy\\Filter\\Doctrine\\DoctrineEmptyCollectionFilter',
                'alias' => null,
            ],
        ];
        return [
            [$s1, $a1]
        ];
    }

    /**
     * @dataProvider analysisSourceProvider
     * @param string $source
     * @param array  $expected
     * @throws ReflectionException
     */
    public function testAnalysisSource(string $source, array $expected)
    {
        $refl = new ReflectionClassExpansion(new ReflectionClass(new StdClass()));
        $uses = $refl->analysisSource($source);

        $this->assertEquals($expected, $uses);
    }
}
