<?php

namespace app\validate;

use think\Validate;

class SystemConfig extends Validate
{
    protected $rule = [
        'tab|分组' => 'require',
        'component|组件' => 'require',
        'code|编码' => 'require|alphaDash|max:32|unique:\\app\\model\\SystemConfig,code^delete_time',
        'title|标题' => 'require',
    ];

    // 更新验证场景
    public function sceneUpdate()
    {
        return $this->remove('code', 'unique');
    }
}
