<?php

namespace app\validate;

use think\Validate;

class SystemRole extends Validate
{
    protected $rule = [
        'name|名称' => 'require|max:32',
        'status|状态'   => 'in:0,1'
    ];
}
