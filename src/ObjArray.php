<?php

namespace JLeo\PoPo;

use Attribute;

#[Attribute]
class ObjArray
{
    public function __construct(public string $objectName)
    {
    }
}