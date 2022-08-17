<?php

namespace app\middleware;

use tauthz\exception\Unauthorized;
use tauthz\facade\Enforcer;
use think\Request;

/**
 * 管理员授权
 */
class SystemAdminAuthz
{
    public function handle($request, \Closure $next)
    {
        if ($request->isOptions()) {
            return $next($request);
        }

        $authzIdentifier = $this->getAuthzIdentifier($request);
        if (!$authzIdentifier) {
            throw new Unauthorized();
        }

        $roles = Enforcer::getRolesForUser($authzIdentifier);
        $obj = \think\helper\Str::snake(string_remove_prefix($request->controller(), 'admin.'));
        $act = $request->isGet() ? 'get' : $request->action();

        if (!in_array('role_1', $roles) && !Enforcer::enforce($authzIdentifier, $obj, $act)) {
            throw new Unauthorized();
        }

        return $next($request);
    }

    public function getAuthzIdentifier(Request $request)
    {
        return $request->admin ? 'admin_' . $request->admin->id : '';
    }
}
