<?php

namespace JLeo\PoPoTp;

use Attribute;

#[Attribute]
class ObjArray
{
    public function __construct(public string $objectName)
    {
    }
}