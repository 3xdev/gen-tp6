<?php

namespace app\controller\admin;

use app\model\SystemRole as SelfModel;
use app\model\SystemTable as TableModel;
use tauthz\facade\Enforcer;

class SystemRole extends Base
{
    /**
     * @api {post} /system_role 创建系统角色
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     * @apiBody {String} name 名称
     */
    public function create()
    {
        $data = $this->request->post(['name']);
        $this->validate($data, 'SystemRole');

        // 创建
        SelfModel::create($data);

        return $this->success();
    }

    /**
     * @api {put} /system_role/:id 更新系统角色
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     * @apiParam {Number} id ID
     * @apiBody {String} name 名称
     * @apiBody {Number} status 状态(0=禁用,1=正常)
     */
    public function update($id)
    {
        $data = $this->request->post(['name', 'status']);
        $this->validate($data, 'SystemRole');

        $obj = SelfModel::find($id);
        if (!$obj) {
            return $this->error();
        }

        // 更新
        $obj->save($data);
        return $this->success();
    }

    /**
     * @api {get} /system_role/table 获取系统角色关联表格
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     * @apiSuccess {Object[]} data 数据列表
     * @apiSuccess {String} data.code 代码
     * @apiSuccess {String} data.name 名称
     * @apiSuccess {Object[]} data.action 操作列表
     * @apiSuccess {String} data.action.value 操作值
     * @apiSuccess {String} data.action.label 操作名
     */
    public function getTable()
    {
        $list = TableModel::with(['options'])->where('status', 1)->select();

        return $this->success([
            'data' => $list->visible(['code', 'name'])->append(['actions'])
        ]);
    }

    /**
     * @api {get} /system_role/permission/:id 获取系统角色权限
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     * @apiParam {String} ids ID串
     */
    public function getPermission($id)
    {
        // 获取角色权限
        $policy = Enforcer::getPermissionsForUser('role_' . $id);

        $data = [];
        array_walk($policy, function ($val) use (&$data) {
            $data[$val[1]][] = $val[2];
        });
        return $this->success($data);
    }

    /**
     * @api {put} /system_role/permission/:id 更新系统角色权限
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     * @apiParam {String} ids ID串
     */
    public function putPermission($id)
    {
        $data = $this->request->post();

        // 删除角色权限
        Enforcer::deletePermissionsForUser('role_' . $id);

        // 添加角色权限
        foreach (array_filter($data) as $obj => $acts) {
            if (is_array($acts)) {
                foreach ($acts as $act) {
                    Enforcer::AddPermissionForUser('role_' . $id, $obj, $act);
                }
            }
        }

        return $this->success();
    }

    /**
     * @api {delete} /system_role/:ids 删除系统角色
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     * @apiParam {String} ids ID串
     */
    public function delete($ids)
    {
        $objs = SelfModel::where('id', 'in', explode(',', $ids))->select();
        if (!$objs) {
            return $this->error();
        }

        foreach ($objs as $obj) {
            $obj->delete();
        }
        return $this->success();
    }

    /**
     * @api {get} /system_role 系统角色列表
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     * @apiQuery {String} [name] 帐号
     * @apiQuery {Number} [current] 当前页
     * @apiQuery {Number} [pageSize] 页大小
     * @apiQuery {String} [filter] ProTable的filter
     * @apiQuery {String} [sorter] ProTable的sorter
     * @apiSuccess {Number} total 数据总计
     * @apiSuccess {Object[]} data 数据列表
     * @apiSuccess {Number} data.id 系统角色ID
     * @apiSuccess {String} data.name 名称
     * @apiSuccess {Number} data.status 状态(0=禁用,1=正常)
     * @apiSuccess {String} data.create_time 创建时间
     */
    public function index()
    {
        $current = $this->request->get('current/d', 1);
        $pageSize = $this->request->get('pageSize/d', 10);
        $search = $this->request->only(['name', 'filter'], 'get');
        $lsearch = $this->request->only(['name', 'filter', 'sorter'], 'get');

        $total = SelfModel::withSearch(array_keys($search), $search)->count();
        $list = SelfModel::withSearch(array_keys($lsearch), $lsearch)->page($current, $pageSize)->select();

        return $this->success([
            'total' => $total,
            'data' => $list->visible(['id', 'name', 'status', 'create_time'])->toArray()
        ]);
    }

    /**
     * @api {get} /system_role/:id 系统角色信息
     * @apiGroup ISYSADMIN
     * @apiHeader {String} Authorization Token
     * @apiParam {Number} id ID
     * @apiSuccess {Number} id 系统角色ID
     * @apiSuccess {String} name 名称
     * @apiSuccess {Number} status 状态(0=禁用,1=正常)
     * @apiSuccess {String} create_time 创建时间
     */
    public function read($id)
    {
        $obj = SelfModel::find($id);
        if (!$obj) {
            return $this->error();
        }

        return $this->success($obj->visible([
            'id', 'name', 'status', 'create_time'
        ])->toArray());
    }
}
