<?php

namespace app\model;

use think\model\concern\SoftDelete;
use think\facade\Event;
use app\model\AdminOperation as AdminOperationModel;

/**
 * 管理员模型
 */
class Admin extends Base
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
    protected $keyword_fields = ['username','nickname','email'];
    public function searchUsernameAttr($query, $value, $data)
    {
        $value && $query->where('username', 'like', '%' . $value . '%');
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
     * @return Admin
     */
    public static function loginByPassword($username, $password, $ip = '')
    {
        $admin = self::where(['username' => $username])->find();
        if (!$admin) {
        // 管理员不存在
            return self::setErrorMsg('帐号或密码错误');
        }

        // 密码比对
        if (!password_verify($password, $admin->password)) {
            return self::setErrorMsg('帐号或密码错误');
        }

        return $admin->doLogin($ip) ? $admin : null;
    }

    /**
     * 验证码登录
     * @return Admin
     */
    public static function loginByCaptcha($mobile, $captcha, $ip = '')
    {
        $admin = self::where(['mobile' => $mobile])->find();
        if (!$admin) {
        // 管理员不存在
            return self::setErrorMsg('手机号或验证码错误');
        }

        // todo:验证码比对
        if ($captcha == $captcha) {
            return self::setErrorMsg('验证码错误');
        }

        return $admin->doLogin($ip) ? $admin : null;
    }

    /**
     * 执行登录
     * @return boolean
     */
    protected function doLogin($ip = '')
    {
        if ($this->getAttr('status') == 0) {
// 管理员已禁用
            return self::setErrorMsg('用户已被禁用');
        }

        // 更新管理员信息
        $this->setAttr('login_ip', $ip);
        $this->setAttr('login_time', time());
        $this->save();
// 触发管理员登录成功事件
        Event::trigger('AdminLogin', $this);
        return true;
    }

    // 所属日志
    public function adminLog()
    {
        return $this->hasMany(AdminOperationModel::class);
    }
}
