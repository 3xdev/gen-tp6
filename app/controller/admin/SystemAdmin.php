<?php

namespace app\controller\admin;

use app\model\SystemAdmin as SelfModel;
use thans\jwt\facade\JWTAuth;

/**
 * @apiDefine IADMIN 管理员
 */
class SystemAdmin extends Base
{
    /**
     * @api {POST} /token 管理员登录(新建token)
     * @apiVersion 1.0.0
     * @apiGroup IADMIN
     * @apiParam {string} type 登录类型(password:密码,captcha:验证码)
     * @apiParam {string} [username] 帐号/手机号
     * @apiParam {string} [password] 密码
     * @apiParam {string} [mobile] 手机号
     * @apiParam {string} [captcha] 验证码
     * @apiSuccess {string} token Token
     */
    public function login()
    {
        $data = $this->request->post();
        $this->validate($data, 'SystemAdmin.login');

        switch ($data['type']) {
            case SelfModel::LOGIN_TYPE_PASSWORD:
                $this->request->admin = SelfModel::loginByPassword($data['username'], $data['password']);
                break;
            case SelfModel::LOGIN_TYPE_CAPTCHA:
                $this->request->admin = SelfModel::loginByCaptcha($data['mobile'], $data['captcha']);
                break;
        }
        if (!$this->request->admin) {
            return $this->error(SelfModel::getErrorMsg());
        }

        // 签发token
        $token = JWTAuth::builder([
            'id' => $this->request->admin->id,
            'username' => $this->request->admin->username,
            'nickname' => $this->request->admin->nickname
        ]);

        return $this->success([
            'token' => $token
        ]);
    }

    /**
     * @api {DELETE} /token 管理员退出(销毁token)
     * @apiVersion 1.0.0
     * @apiGroup IADMIN
     * @apiHeader {string} Authorization Token
     */
    public function logout()
    {
        // 拉黑token
        JWTAuth::invalidate(JWTAuth::token());

        return $this->success();
    }

    /**
     * @api {GET} /profile 读取管理员个人信息
     * @apiVersion 1.0.1
     * @apiGroup IADMIN
     * @apiHeader {string} Authorization Token
     * @apiSuccess {number} id 管理员ID
     * @apiSuccess {string} username 帐号
     * @apiSuccess {string} nickname 昵称
     * @apiSuccess {string} avatar 头像
     * @apiSuccess {string} mobile 手机号
     * @apiSuccess {string} login_time 最近登录时间
     * @apiSuccess {string} create_time 创建时间
     */
    public function profile()
    {
        return $this->success(array_merge($this->request->admin->visible([
            'id', 'username', 'nickname', 'avatar', 'mobile', 'login_time', 'create_time'
        ])->toArray(), ['access' => 'admin']));
    }

    /**
     * @api {PUT} /profile 更新管理员个人信息
     * @apiVersion 1.0.0
     * @apiGroup IADMIN
     * @apiHeader {string} Authorization Token
     * @apiParam {string} nickname 昵称
     * @apiParam {string} avatar 头像
     * @apiParam {string} mobile 手机号
     * @apiParam {string} password 密码
     */
    public function updateProfile()
    {
        $data = $this->request->post(['nickname', 'avatar', 'mobile', 'password']);
        $this->validate($data, 'SystemAdmin.update');

        // 更新管理员
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $this->request->admin->save($data);

        return $this->success();
    }

    /**
     * @api {POST} /admins 创建管理员
     * @apiVersion 1.0.0
     * @apiGroup IADMIN
     * @apiHeader {string} Authorization Token
     * @apiParam {string} username 帐号
     * @apiParam {string} mobile 手机号
     * @apiParam {string} password 密码
     * @apiParam {string} avatar 头像
     */
    public function create()
    {
        $data = $this->request->post(['mobile', 'username', 'nickname', 'password', 'avatar']);
        $data['delete_time'] = 0;
        $this->validate($data, 'SystemAdmin');

        // 创建管理员
        SelfModel::create($data);

        return $this->success();
    }

    /**
     * @api {PUT} /admins/:id 更新管理员
     * @apiVersion 1.0.0
     * @apiGroup IADMIN
     * @apiHeader {string} Authorization Token
     * @apiParam {string} username 账号
     * @apiParam {string} mobile 手机号
     * @apiParam {string} password 密码
     * @apiParam {string} avatar 头像
     * @apiParam {number} status 状态(0=禁用,1=正常)
     */
    public function update($id)
    {
        $data = $this->request->post(['username', 'mobile', 'password', 'nickname', 'avatar', 'status']);
        $data['delete_time'] = 0;

        $model = SelfModel::find($id);
        if (!$model) {
            return $this->error();
        }
        if ($model->username == $data['username']) {
            $this->validate($data, 'SystemAdmin.update');
        } else {
            $this->validate($data, 'SystemAdmin');
        }

        // 更新管理员
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $model->save($data);
        return $this->success();
    }

    /**
     * @api {DELETE} /admins/:ids 删除管理员
     * @apiVersion 1.0.0
     * @apiGroup IADMIN
     * @apiHeader {string} Authorization Token
     */
    public function delete($ids)
    {
        $admins = SelfModel::where('id', 'in', explode(',', $ids))->select();
        if (!$admins) {
            return $this->error();
        }

        foreach ($admins as $admin) {
            $admin->delete();
        }
        return $this->success();
    }

    /**
     * @api {GET} /admins 管理员列表
     * @apiVersion 1.0.0
     * @apiGroup IADMIN
     * @apiHeader {string} Authorization Token
     * @apiParam {string} username 帐号
     * @apiParam {string} mobile 手机号
     * @apiParam {string} nickname 昵称
     * @apiParam {number} current 当前页
     * @apiParam {number} pageSize 页大小
     * @apiParam {string} filter ProTable的filter
     * @apiParam {string} sorter ProTable的sorter
     * @apiSuccess {number} total 数据总计
     * @apiSuccess {Object[]} data 数据列表
     * @apiSuccess {number} data.id 管理员ID
     * @apiSuccess {string} data.username 帐号
     * @apiSuccess {string} data.mobile 手机号
     * @apiSuccess {string} data.avatar 头像
     * @apiSuccess {string} data.login_time 最近登录时间
     * @apiSuccess {string} data.create_time 创建时间
     */
    public function index()
    {
        $current = $this->request->get('current/d', 1);
        $pageSize = $this->request->get('pageSize/d', 10);
        $search = $this->request->only(['username', 'mobile', 'nickname', 'filter', 'sorter'], 'get');

        $total = SelfModel::withSearch(array_keys($search), $search)->count();
        $list = SelfModel::withSearch(array_keys($search), $search)->page($current, $pageSize)->select();

        return $this->success([
            'total' => $total,
            'data' => $list->visible(['id', 'username', 'nickname', 'status', 'mobile', 'avatar', 'login_time', 'create_time'])->toArray()
        ]);
    }

    /**
     * @api {GET} /admins/:id 管理员信息
     * @apiVersion 1.0.0
     * @apiGroup IADMIN
     * @apiHeader {string} Authorization Token
     * @apiSuccess {number} id 管理员ID
     * @apiSuccess {string} username 帐号
     * @apiSuccess {string} mobile 手机号
     * @apiSuccess {string} nickname 昵称
     * @apiSuccess {string} avatar 头像
     * @apiSuccess {string} email 邮箱
     * @apiSuccess {number} status 状态(0=禁用,1=正常)
     * @apiSuccess {string} login_time 最近登录时间
     * @apiSuccess {string} create_time 创建时间
     */
    public function read($id)
    {
        $admin = SelfModel::find($id);
        if (!$admin) {
            return $this->error();
        }

        return $this->success($admin->visible([
            'id', 'username', 'mobile', 'nickname', 'avatar', 'email', 'status', 'login_time', 'create_time'
        ])->toArray());
    }
}
