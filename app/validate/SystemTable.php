<?php

namespace app\validate;

use think\Validate;

class SystemTable extends Validate
{
    protected $rule = [
        'code|代码' => 'require|alphaDash|max:32|unique:\\app\\model\\SystemTable',
        'name|名称' => 'require|max:100',
    ];

    // 更新验证场景
    public function sceneUpdate()
    {
        return $this->remove('code', true);
    }
}
