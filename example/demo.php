<?php

use Example\StructTest;
use Example\Test123;
use Example\Test1234;
use HZEX\UnitConvertor\RenMinBi;

require __DIR__ . '/../vendor/autoload.php';

$obj = new StructTest([]);

var_dump($obj->getDataChangeCount());
$obj->test0 = new Test1234();
$obj->test1 = new Test123();
$obj->test2 = new RenMinBi();
$obj->test3 = new Test1234();
$obj->test4 = [];
$obj->test5 = new stdClass();

var_dump($obj->getDataChangeCount());
$obj->expire_time = null;
var_dump($obj->expire_time);
var_dump($obj->getDataChangeCount());
$obj->erase('expire_time');
var_dump($obj->getDataChangeCount());
$obj->expire_time = 123;
var_dump($obj->expire_time);
var_dump($obj->getDataChangeCount());
$obj->expire_time = 123;
var_dump($obj->expire_time);
var_dump($obj->getDataChangeCount());

StructTest::$BUILD_PATH = '../runtime/';
$obj->build();

//$ref = new ReflectionClass($obj);
//$refe = new ReflectionClassExpansion($ref);
//var_dump($refe->readHeadSource());
//$uses = $refe->analysisSource(read_file_source($ref));
//var_export($uses);
//var_dump($ref->getNamespaceName());
