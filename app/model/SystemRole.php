<?php

namespace app\model;

use think\model\concern\SoftDelete;
use tauthz\facade\Enforcer;

/**
 * 角色模型
 */
class SystemRole extends Base
{
    use SoftDelete;

    // 模型事件
    public static function onBeforeDelete($role)
    {
        if ($role->id == 1) {
            return false;
        }

        // 删除角色的策略
        Enforcer::deleteRole('role_' . $role->id);
    }
}
