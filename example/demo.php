<?php

use Zxin\DataStruct\DataStruct;
use Zxin\Stub\DataStructStub;

require __DIR__ . '/../vendor/autoload.php';

DataStruct::setProjectRootPath(__DIR__ . '/../');
DataStruct::setCachePath('./../runtime/');
DataStruct::setProduce(false);

$struct = new DataStructStub();

$struct->bool = true;
$struct->erase('string');
$struct->string = 'asd';
$struct->myArray2 = [
    new DateTime(),
    new DateTime(),
];

DataStruct::dumpCacheFile();
