<?php

namespace app\model;

use think\model\concern\SoftDelete;
use think\facade\Event;
use tauthz\facade\Enforcer;

/**
 * 用户模型
 */
class User extends Base
{
    use SoftDelete;

    // 登录类型定义
    public const LOGIN_TYPE_PASSWORD = 'password';
    public const LOGIN_TYPE_CAPTCHA = 'captcha';
    public static function getLoginTypeList()
    {
        return [self::LOGIN_TYPE_PASSWORD => '密码', self::LOGIN_TYPE_CAPTCHA => '验证码'];
    }

    // 关键字搜索主键字段
    public $keyword_fields = ['username','mobile','nickname','email'];
    public function searchUsernameAttr($query, $value, $data)
    {
        $value && $query->where('username', 'like', '%' . $value . '%');
    }
    public function searchMobileAttr($query, $value, $data)
    {
        $value && $query->where('mobile', 'like', '%' . $value . '%');
    }
    public function searchNicknameAttr($query, $value, $data)
    {
        $value && $query->where('nickname', 'like', '%' . $value . '%');
    }
    public function searchEmailAttr($query, $value, $data)
    {
        $value && $query->where('email', 'like', '%' . $value . '%');
    }
    public function searchLoginTimeAttr($query, $value, $data)
    {
        $value && $query->whereBetweenTime('login_time', $value[0], $value[1]);
    }

    public function getLoginTimeAttr($value, $data)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function setPasswordAttr($value)
    {
        return $value ? password_hash($value, PASSWORD_DEFAULT) : '';
    }

    /**
     * 密码登录
     */
    public static function loginByPassword($username, $password)
    {
        $user = self::where('status', 1)->where(function ($query) use ($username) {
            $query->whereOr([
                [
                    ['username', '=', $username],
                ],
                [
                    ['mobile', '<>', ''],
                    ['mobile', '=', $username],
                ],
            ]);
        })->find();
        if (!$user) {
            // 用户不存在
            self::setErrorMsg('帐号或密码错误');
            return null;
        }

        // 密码比对
        if (!password_verify($password, $user->password)) {
            self::setErrorMsg('帐号或密码错误');
            return null;
        }

        return $user->doLogin();
    }

    /**
     * 验证码登录
     */
    public static function loginByCaptcha($mobile, $captcha)
    {
        $user = self::where(['mobile' => $mobile])->find();
        if (!$user) {
            // 用户不存在
            self::setErrorMsg('手机号或验证码错误');
            return null;
        }

        // todo:验证码比对
        if ($captcha == $captcha) {
            self::setErrorMsg('验证码错误');
            return null;
        }

        return $user->doLogin();
    }

    /**
     * 执行登录
     */
    protected function doLogin()
    {
        if ($this->getAttr('status') == 0) {
            // 用户已禁用
            self::setErrorMsg('用户已被禁用');
            return null;
        }

        // 更新用户信息
        $this->setAttr('login_time', time());
        $this->save();
        // 触发用户登录成功事件
        Event::trigger('UserLogin', $this);
        return $this;
    }
}
