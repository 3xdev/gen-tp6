<?php

namespace app\validate;

use think\Validate;

class SystemCol extends Validate
{
    protected $rule = [
        'table_code|关联表格' => 'require|alphaDash|max:32',
        'data_index|映射字段' => 'require|alphaDash|max:32',
        'title|标题' => 'require|max:100',
    ];

    // 更新验证场景
    public function sceneUpdate()
    {
        return $this->remove('table_code', true);
    }
}
