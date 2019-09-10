<?php
namespace HuangZx;

use HZEX\Reflection\Property;
use ReflectionException;
use ReflectionObject;

/**
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