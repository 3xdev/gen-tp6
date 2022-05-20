<?php

namespace app\validate;

use think\Validate;

class SystemAdmin extends Validate
{
    protected $rule = [
        'username|帐号' => 'require|alphaDash|max:32|unique:\\app\\model\\SystemAdmin,username^delete_time',
        'password|密码' => 'graph|min:6|max:20',
        'mobile|手机号' => 'mobile',
        'status|状态'   => 'in:0,1'
    ];

    // 登录验证场景
    public function sceneLogin()
    {
        return $this->remove('username', 'require|unique')
                ->append('type|登录类型', 'require|checkLoginType')
                ->append('captcha|验证码', 'number');
    }

    // 更新验证场景
    public function sceneUpdate()
    {
        return $this->remove('username', 'unique');
    }

    // 自定义验证规则
    // 验证登录类型值
    protected function checkLoginType($value, $rule)
    {
        $types = \app\model\SystemAdmin::getLoginTypeList();
        return isset($types[strtolower($value)]);
    }
}
