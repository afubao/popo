<?php

namespace JLeo\PoPo;

use Exception;
use ReflectionException;
use ReflectionObject;
use app\common\constant\ErrorNums;
use app\common\exception\AppException;
use think\helper\Str;

trait TraitMagicGetSet
{
    /**
     * @param $name
     * @param $value
     * @return void
     * @throws Exception
     */
    public function __set($name, $value)
    {
        $reflector = new ReflectionObject($this);
        try {
            $property = $reflector->getProperty($name);
        } catch (ReflectionException) {
            if (env('APP_DEBUG')) {
                throw new AppException(ErrorNums::PARAM_ILLEGAL, static::class . "::{$name} does not exist");
            }
        }
        $property->setValue($this, $value);
        $this->data[Str::snake($name)] = $value;
    }

    /**
     * @param $name
     * @return null
     * @throws Exception
     */
    public function __get($name)
    {
        $reflector = new ReflectionObject($this);
        try {
            $property = $reflector->getProperty($name);
        } catch (ReflectionException) {
            if (env('APP_DEBUG')) {
                throw new AppException(ErrorNums::PARAM_ILLEGAL, static::class . "::{$name} does not exist");
            }
        }
        return $property->getValue($this);
    }
}