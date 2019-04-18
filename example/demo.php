<?php

use HZEX\DataStruct\DataStruct;
use HZEX\Stub\DataStructStub;

require __DIR__ . '/../vendor/autoload.php';
//require __DIR__ . '/../vendor/topthink/framework/base.php';

DataStruct::setCacheBuildPath('./../runtime/');

$struct = new DataStructStub();

$struct->bool = true;
$struct->erase('string');
$struct->string = 'asd';
$struct->myArray2 = [
    new DateTime(),
    new DateTime(),
];

//var_dump($struct->toArray());


DataStruct::dumpCacheFile();
