<?php

namespace app\validate;

use think\Validate;

class SystemDict extends Validate
{
    protected $rule = [
        'key_|代码' => 'require|alphaDash|max:32|unique:\\app\\model\\SystemDict,key_,,key_',
        'label|名称' => 'require',
    ];

    // 更新验证场景
    public function sceneUpdate()
    {
        return $this->remove('key_', 'unique');
    }
}
