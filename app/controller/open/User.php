<?php

namespace app\controller\open;

use app\BaseController;
use app\model\User as UserModel;
use thans\jwt\facade\JWTAuth;

/**
 * @apiDefine IUSER 用户
 */
class User extends BaseController
{
    /**
     * @api {post} /token 用户登录(新建token)
     * @apiGroup IUSER
     * @apiBody {String} type 登录类型(password:密码,captcha:验证码)
     * @apiBody {String} [username] 帐号/手机号
     * @apiBody {String} [password] 密码
     * @apiBody {String} [mobile] 手机号
     * @apiBody {String} [captcha] 验证码
     * @apiSuccess {String} token Token
     */
    public function login()
    {
        $data = $this->request->post();

        switch ($data['type']) {
            case UserModel::LOGIN_TYPE_PASSWORD:
                $this->request->user = UserModel::loginByPassword($data['username'], $data['password']);
                break;
            case UserModel::LOGIN_TYPE_CAPTCHA:
                $this->request->user = UserModel::loginByCaptcha($data['mobile'], $data['captcha']);
                break;
        }
        if (!$this->request->user) {
            return $this->error(UserModel::getErrorMsg());
        }

        // 签发token
        $token = JWTAuth::builder([
            'avatar' => $this->request->user->avatar,
            'mobile' => $this->request->user->mobile,
            'user_id' => $this->request->user->id,
            'username' => $this->request->user->username,
            'nickname' => $this->request->user->nickname,
        ]);

        return $this->success([
            'token' => $token
        ]);
    }

    /**
     * @api {delete} /token 用户退出(销毁token)
     * @apiGroup IUSER
     * @apiHeader {String} Authorization Token
     */
    public function logout()
    {
        // 拉黑token
        JWTAuth::invalidate(JWTAuth::token());

        return $this->success();
    }
}
