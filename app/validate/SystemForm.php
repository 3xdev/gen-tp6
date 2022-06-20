<?php

namespace app\validate;

use think\Validate;

/**
 * 系统表单验证器
 */
class SystemForm extends Validate
{
    protected $rule = [
        'code|代码' => 'require|alphaDash|max:32|checkIsSystem|unique:\\app\\model\\SystemForm,code^delete_time',
        'name|名称' => 'require|max:100',
        'schema_string|Schema' => 'require|checkIsJson',
    ];

    // 更新验证场景
    public function sceneUpdate()
    {
        return $this->remove('code', true);
    }

    // 自定义验证规则
    // 验证是否系统表单代码
    protected function checkIsSystem($value, $rule)
    {
        return in_array($value, ['setting']) ? '不能使用保留代码' : true;
    }
    // 验证是否Json
    protected function checkIsJson($value, $rule)
    {
        return is_object(json_decode($value)) ? true : '不是Json格式';
    }
}
