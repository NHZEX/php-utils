<?php
declare(strict_types=1);

namespace HZEX\Reflection;

use ReflectionProperty;

class Property
{
    protected $object;

    protected $refProp;

    /**
     * Property constructor.
     * @param object $object
     * @param ReflectionProperty $property
     */
    public function __construct($object, ReflectionProperty $property)
    {
        $this->object = $object;
        $this->refProp = $property;
        $this->refProp->setAccessible(true);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->refProp->getValue($this->object);
    }

    /**
     * @param mixed $val
     */
    public function setValue($val)
    {
        $this->refProp->setValue($this->object, $val);
    }
}
