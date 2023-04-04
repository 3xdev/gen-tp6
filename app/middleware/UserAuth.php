<?php

namespace app\middleware;

use thans\jwt\exception\TokenExpiredException;
use thans\jwt\exception\TokenBlacklistGracePeriodException;

/**
 * 用户认证
 */
class UserAuth extends \thans\jwt\middleware\BaseMiddleware
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
                $this->setUser($payload->get('user_id'), $request);
                return $this->setAuthentication($next($request), $token);
            } catch (TokenBlacklistGracePeriodException $e) {
                // 捕获黑名单宽限期
                $payload = $this->auth->auth(false);
            }
        } catch (TokenBlacklistGracePeriodException $e) {
            // 捕获黑名单宽限期
            $payload = $this->auth->auth(false);
        }

        $this->setUser($payload->get('user_id'), $request);
        return $next($request);
    }

    // 设置用员
    protected function setUser($id, $request)
    {
        // 用户信息
        $user = \app\model\User::find($id);
        if (!$user) {
            // 不存在
            abort(401, '用户不存在或己删除');
        }

        $request->user = $user;
    }
}
