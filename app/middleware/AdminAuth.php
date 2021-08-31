<?php

namespace app\middleware;

use thans\jwt\exception\TokenExpiredException;
use thans\jwt\exception\TokenBlacklistGracePeriodException;

/**
 * 管理员授权验证
 */
class AdminAuth extends \thans\jwt\middleware\BaseMiddleware
{
    public function handle($request, \Closure $next)
    {
        if ($request->isOptions()) {
            return $next($request);
        }

        // 验证token
        try {
            $payload = $this->auth->auth();
        } catch (TokenExpiredException $e) {
            // 捕获token过期
            // 刷新token
            try {
                $token = $this->auth->refresh();
                $payload = $this->auth->auth(false);
                $this->setAdmin($payload['id']->getValue(), $request);
                return $this->setAuthentication($next($request), $token);
            } catch (TokenBlacklistGracePeriodException $e) {
                // 捕获黑名单宽限期
                $payload = $this->auth->auth(false);
            }
        } catch (TokenBlacklistGracePeriodException $e) {
            // 捕获黑名单宽限期
            $payload = $this->auth->auth(false);
        }

        $this->setAdmin($payload['id']->getValue(), $request);
        return $next($request);
    }

    // 设置管理员
    protected function setAdmin($id, $request)
    {
        $admin = \app\model\Admin::find($id);
        if (!$admin) {
        // 管理员不存在
            abort(401, '管理员不存在或己删除');
        }
        $request->admin = $admin;
    }
}
