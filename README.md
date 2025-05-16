参考java的pojo而来，将入参绑定到对象中，面向对象编程

支持thinkphp webman框架，其他非常驻内存框架应该都支持，需要自己注意

hyperf框架 https://github.com/afubao/popo-hyperf

```php
# region 参数对象定义
class TestParam extends PoPo {
private int $id = 0; // 这是有默认值，前端可以不传

    private string $name; // 这是必传参数
    
    private string $userName; // 这是必传参数,下划线会自动转为驼峰，对应参数为user_name
    
    #[ObjArray(Obj::class)]
    private string $thisObjArr; // 这是一个对象数组，对应参数格式为 this_obj_arr{[{"title":"这是标题"},{"title":"这是标题"}]}
    
    private Obj $thisObj;// 这是一个对象，对应参数格式为 this_obj_arr{"title":"这是标题"}
}

class Obj extends PoPo {
    private string $title; // 这是必传参数
}

# endregion 参数对象定义

# controller中的使用
public function add(TestParam $param): Response
{
    var_dump($param->name);
    var_dump($param->userName);
    var_dump($param->thisObjArr);
    var_dump($param->thisObj);
    var_dump($param->toArray());
}

```