<?php
namespace HuangZx;

use HZEX\Reflection\Property;
use ReflectionException;
use ReflectionObject;

/**
 * 反射获取类属性操作对象
 * @param object $object
 * @param string $prop
 * @return Property
 * @throws ReflectionException
 */
function ref_get_prop($object, string $prop): Property
{
    $ref = new ReflectionObject($object);
    $refProp = $ref->getProperty($prop);
    return new Property($object, $refProp);
}

/**
 * 引用传递类属性值
 * @param $origin
 * @param $target
 * @param $prop
 */
function ref_prop_value($origin, $target, $prop)
{
    $originGet = function &($prop) {
        return $this->{$prop};
    };
    $originGet = $originGet->bindTo($origin, $origin);

    $targetSet = function ($prop) use ($originGet) {
        $this->{$prop} = &$originGet($prop);
    };
    $targetSet = $targetSet->bindTo($target, $target);
    $targetSet($prop);
}

/**
 * 拷贝类属性值
 * @param $origin
 * @param $target
 * @param $prop
 */
function ref_copy_prop_value($origin, $target, $prop)
{
    $originGet = function ($prop) {
        return $this->{$prop};
    };
    $originGet = $originGet->bindTo($origin, $origin);

    $targetSet = function ($prop) use ($originGet) {
        $this->{$prop} = $originGet($prop);
    };
    $targetSet = $targetSet->bindTo($target, $target);
    $targetSet($prop);
}