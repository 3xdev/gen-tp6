<?php

namespace app\controller\admin;

use app\model\SystemAdmin as SelfModel;
use thans\jwt\facade\JWTAuth;
use tauthz\facade\Enforcer;

/**
 * @apiDefine ISYSADMIN 系统-角色及管理员
 */
class SystemAdmin extends Base
{
    /**
     * @api {post} /token 管理员登录(新建token)
     * @apiGroup ISYSADMIN
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
     * @api {delete} /token 管理员退出(销毁token)
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     */
    public function logout()
    {
        // 拉黑token
        JWTAuth::invalidate(JWTAuth::token());

        return $this->success();
    }

    /**
     * @api {get} /profile 读取管理员个人信息
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     * @apiSuccess {Number} id 管理员ID
     * @apiSuccess {String} username 帐号
     * @apiSuccess {String} nickname 昵称
     * @apiSuccess {String} avatar 头像
     * @apiSuccess {String} mobile 手机号
     * @apiSuccess {String} login_time 最近登录时间
     * @apiSuccess {String} create_time 创建时间
     */
    public function profile()
    {
        return $this->success(array_merge($this->request->admin->visible([
            'id', 'username', 'nickname', 'avatar', 'mobile', 'login_time', 'create_time'
        ])->toArray(), ['access' => 'admin']));
    }

    /**
     * @api {put} /profile 更新管理员个人信息
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     * @apiBody {String} nickname 昵称
     * @apiBody {String} avatar 头像
     * @apiBody {String} mobile 手机号
     * @apiBody {String} password 密码
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
     * @api {get} /menus 获取管理员可访问菜单
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     * @apiSuccess {Object[]} data 菜单列表
     * @apiSuccess {Number} data.id 菜单ID
     * @apiSuccess {String} data.name 名称
     * @apiSuccess {Number} data.parent_id 父ID
     * @apiSuccess {String} data.path 访问路由
     * @apiSuccess {String} data.icon 图标
     * @apiSuccess {Number} data.sort 排序
     */
    public function menus()
    {
        $list = \app\model\SystemMenu::where('status', 1)->order('sort')->select();

        $roles = Enforcer::getRolesForUser('admin_' . $this->request->admin->id);
        $menus = $list->filter(fn($menu) => empty($menu->table_code) || in_array('role_1', $roles) || Enforcer::enforce('admin_' . $this->request->admin->id, $menu->table_code, 'get'));
        return $this->success([
            'data' => new \BlueM\Tree(
                $menus->filter([$this, 'menusFilter'])->visible(['id', 'name', 'parent_id', 'path', 'icon', 'sort'])->toArray(),
                ['parent' => 'parent_id', 'jsonSerializer' => new \BlueM\Tree\Serializer\HierarchicalTreeJsonSerializer()]
            ),
        ]);
    }
    public function menusFilter($menu)
    {
        // 特殊处理
        if ($menu->table_code == 'XXXXXX') {
            // 符合条件 不展示
            return false;
        }

        return true;
    }



    /**
     * @api {get} /menus 获取管理员可访问表格
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     * @apiSuccess {string[]} data 表格列表
     */
    public function tables()
    {
        $list = \app\model\SystemTable::where('status', 1)->select();

        $roles = Enforcer::getRolesForUser('admin_' . $this->request->admin->id);
        $tables = $list->filter(fn($table) => in_array('role_1', $roles) || Enforcer::enforce('admin_' . $this->request->admin->id, $table->code, 'get'));
        return $this->success([
            'data' => $tables->column('code'),
        ]);
    }

    /**
     * @api {post} /admins 创建管理员
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     * @apiBody {String} username 帐号
     * @apiBody {String} mobile 手机号
     * @apiBody {String} password 密码
     * @apiBody {String} avatar 头像
     */
    public function create()
    {
        $data = $this->request->post(['username', 'mobile', 'password', 'nickname']);
        $roles = $this->request->post('roles/a');
        $data['delete_time'] = 0;
        $this->validate($data, 'SystemAdmin');

        // 创建管理员
        $model = SelfModel::create($data);

        // 新增管理员角色
        foreach ($roles as $role) {
            Enforcer::addRoleForUser('admin_' . $model->id, 'role_' . $role);
        }

        return $this->success();
    }

    /**
     * @api {put} /admins/:id 更新管理员
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     * @apiParam {Number} id ID
     * @apiBody {String} username 账号
     * @apiBody {String} mobile 手机号
     * @apiBody {String} password 密码
     * @apiBody {String} avatar 头像
     * @apiBody {Number} status 状态(0=禁用,1=正常)
     */
    public function update($id)
    {
        $data = $this->request->post(['username', 'roles', 'mobile', 'password', 'nickname', 'avatar', 'status']);
        $roles = $this->request->post('roles/a');
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

        // 更新管理员角色
        Enforcer::deleteRolesForUser('admin_' . $model->id);
        foreach ($roles as $role) {
            Enforcer::addRoleForUser('admin_' . $model->id, 'role_' . $role);
        }

        return $this->success();
    }

    /**
     * @api {delete} /admins/:ids 删除管理员
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     * @apiParam {String} ids ID串
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
     * @api {get} /admins 管理员列表
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     * @apiQuery {String} [username] 帐号
     * @apiQuery {String} [mobile] 手机号
     * @apiQuery {String} [nickname] 昵称
     * @apiQuery {Number} [current] 当前页
     * @apiQuery {Number} [pageSize] 页大小
     * @apiQuery {String} [filter] ProTable的filter
     * @apiQuery {String} [sorter] ProTable的sorter
     * @apiSuccess {Number} total 数据总计
     * @apiSuccess {Object[]} data 数据列表
     * @apiSuccess {Number} data.id 管理员ID
     * @apiSuccess {String} data.username 帐号
     * @apiSuccess {String} data.mobile 手机号
     * @apiSuccess {String} data.avatar 头像
     * @apiSuccess {String} data.login_time 最近登录时间
     * @apiSuccess {String} data.create_time 创建时间
     */
    public function index()
    {
        $current = $this->request->get('current/d', 1);
        $pageSize = $this->request->get('pageSize/d', 10);
        $search = $this->request->only(['username', 'nickname', 'mobile', 'roles', 'filter'], 'get');
        $lsearch = $this->request->only(['username', 'nickname', 'mobile', 'roles', 'filter', 'sorter'], 'get');

        $total = SelfModel::withSearch(array_keys($search), $search)->count();
        $list = SelfModel::withSearch(array_keys($lsearch), $lsearch)->page($current, $pageSize)->select();

        return $this->success([
            'total' => $total,
            'data' => $list->visible(['id', 'username', 'nickname', 'status', 'mobile', 'avatar', 'login_time', 'create_time'])->append(['roles'])->toArray()
        ]);
    }

    /**
     * @api {get} /admins/:id 管理员信息
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     * @apiParam {Number} id ID
     * @apiSuccess {Number} id 管理员ID
     * @apiSuccess {String} username 帐号
     * @apiSuccess {String} mobile 手机号
     * @apiSuccess {String} nickname 昵称
     * @apiSuccess {String} avatar 头像
     * @apiSuccess {String} email 邮箱
     * @apiSuccess {Number} status 状态(0=禁用,1=正常)
     * @apiSuccess {String} login_time 最近登录时间
     * @apiSuccess {String} create_time 创建时间
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
