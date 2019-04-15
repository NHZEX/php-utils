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
    public $isRead;
    /** @var bool */
    public $isHide;
    /** @var bool */
    public $canNull;
    /** @var bool */
    public $isTypeArray;
    /** @var bool */
    public $isBasicType;
    /** @var bool */
    public $isNotClass;
    /** @var mixed */
    public $defaultValue;
}
