<?php

namespace HZEX\DataStruct;

class StructMetaDataProp
{
    /** @var string */
    public $type;
    /** @var string */
    public $realType;
    /** @var string */
    public $control;
    /** @var bool */
    public $canNull;
    /** @var bool */
    public $isBasicType;
    /** @var bool */
    public $isNotClass;
    /** @var mixed */
    public $defaultValue;
}
