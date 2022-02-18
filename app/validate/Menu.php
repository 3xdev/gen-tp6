<?php

namespace app\validate;

use think\Validate;

class Menu extends Validate
{
    protected $rule = [
        'name|名称' => 'require|max:100',
        'path|访问路由' => 'max:100',
        'parent_id|上级菜单' => 'require|number',
        'table_code|关联表格' => 'alphaDash|max:32',
    ];

    // 更新验证场景
    public function sceneUpdate()
    {
        return $this->remove('parent_id', true);
    }
}
