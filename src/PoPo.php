<?php

namespace Leo\PoPo;

use app\common\exception\PoPoException;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use think\contract\Arrayable;
use think\helper\Str;
use think\Request;

abstract class PoPo implements Arrayable
{
    use TraitMagicGetSet;
    /**
     * @var array $data 数据
     */
    private array $data = [];

    /**
     * @var array $validates 验证器
     */
    protected array $validates = [];

    /**
     * @var bool $autoValidate 自动验证
     */
    protected bool $autoValidate = true;

    /**
     * @var array $notFilterField 无需全局过滤的字段
     */
    protected array $notFilterField = [];

    /**
     * @var ReflectionClass
     */
    private ReflectionClass $reflectionClass;

    /**
     * PoPo constructor.
     * @param Request $request
     * @param array $param
     * @throws ReflectionException|PoPoException
     */
    public function __construct(Request $request, array $param = []) {
        if (empty($param)) {
            $inputData = $request->getInput();
            $inputData = $this->fitterData(json_decode($inputData, true) ?? []);
        } else {
            $inputData = $param;
        }
        $this->reflectionClass = new ReflectionClass($this);
        $this->setData($inputData);
        if ($this->autoValidate) {
            $this->validate();
        }
    }

    /**
     * 触发验证
     */
    public function validate(): void
    {
        foreach ($this->validates as $scene => $validate) {
            if (is_int($scene)) {
                validate($validate)->check($this->toArray());
            } else {
                validate($validate)->scene($scene)->check($this->toArray());
            }
        }
    }

    /**
     * 将类的私有属性转为数组
     * @return array
     */
    public function toArray(): array {
        return $this->data;
    }

    /**
     * 设置数据
     * @param $inputData
     * @throws ReflectionException|PoPoException
     */
    private function setData($inputData): void
    {
        $properties = $this->reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $propertySnakeName = Str::snake($property->getName());
            if (isset($inputData[$propertySnakeName])) {
                $propertyOriginValue = $propertyValue = $inputData[$propertySnakeName];
                // 处理对象数据，一维数据，暂不考虑union类型
                if ($property->getType() instanceof ReflectionNamedType) {
                    if (!$property->getType()->isBuiltin()) {
                        $objName = $property->getType()->getName();
                        $valueObj = app($objName, [
                            'param' => [Str::random()]
                        ],true);
                        if ($valueObj instanceof PoPo) {
                            /**
                             * 没有默认值的数据，无法重置，只能先每次new一个对象
                             */
                            $valueObj->setData($propertyValue);
                            $propertyOriginValue = $valueObj->toArray();
                            $propertyValue = $valueObj;
                        } else {
                            unset($valueObj);
                        }
                    }
                }
                $attributes = $property->getAttributes();
                // 处理对象数组数据，二维数据
                foreach ($attributes as $attribute) {
                    if ($attribute->getName() === ObjArray::class) {
                        $objName = $attribute->getArguments()[0];
                        foreach ($propertyValue as $key => $value) {
                            /**
                             * @var PoPo $valueObj
                             * 没有默认值的数据，无法重置，只能先每次new一个对象
                             */
                            $valueObj = app($objName, [
                                'param' => $value
                            ],true);
                            $propertyValue[$key] = $valueObj;
                            $propertyOriginValue[$key] = $valueObj->toArray();
                        }
                    }
                }
                $this->$propertyName = $propertyValue;
                $this->data[$propertySnakeName] = $propertyOriginValue;
            } else {
                $this->data[$propertySnakeName] = $property->getDefaultValue();
            }
        }
    }

    /**
     * 数据过滤
     * @param array $params
     * @return array
     */
    public function fitterData(array $params): array {
        foreach ($params as $paramKey => $paramValue) {
            if (!in_array($paramKey, $this->notFilterField)) {
                $param = app()->request->only([$paramKey], [$paramKey => $paramValue]);
                $params[$paramKey] = $param[$paramKey];
            }
        }
        return $params;
    }
}