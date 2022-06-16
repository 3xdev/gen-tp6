<?php

namespace app\controller\admin;

use app\model\SystemRole as SelfModel;
use tauthz\facade\Enforcer;

/**
 * @apiDefine ISYSTEM 系统
 */
class SystemRole extends Base
{
    /**
     * @api {POST} /system_role 创建系统角色
     * @apiVersion 1.0.0
     * @apiGroup ISYSTEM
     * @apiHeader {string} Authorization Token
     * @apiParam {string} name 名称
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
     * @api {PUT} /system_role/:id 更新系统角色
     * @apiVersion 1.0.0
     * @apiGroup ISYSTEM
     * @apiHeader {string} Authorization Token
     * @apiParam {string} name 名称
     * @apiParam {number} status 状态(0=禁用,1=正常)
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
     * @api {GET} /system_role/permission/:ids 获取系统角色权限
     * @apiVersion 1.0.0
     * @apiGroup ISYSTEM
     * @apiHeader {string} Authorization Token
     */
    public function getPermission($ids)
    {
        // 获取角色权限
        $policy = Enforcer::getPermissionsForUser('role_' . $ids);

        $data = [];
        array_walk($policy, function ($val) use (&$data) {
            $data[$val[1]][] = $val[2];
        });
        return $this->success($data);
    }

    /**
     * @api {PUT} /system_role/permission/:ids 更新系统角色权限
     * @apiVersion 1.0.0
     * @apiGroup ISYSTEM
     * @apiHeader {string} Authorization Token
     */
    public function putPermission($ids)
    {
        $data = $this->request->post();

        // 删除角色权限
        Enforcer::deletePermissionsForUser('role_' . $ids);

        // 添加角色权限
        foreach (array_filter($data) as $obj => $acts) {
            if (is_array($acts)) {
                foreach ($acts as $act) {
                    Enforcer::AddPermissionForUser('role_' . $ids, $obj, $act);
                }
            }
        }

        return $this->success();
    }

    /**
     * @api {DELETE} /system_role/:ids 删除系统角色
     * @apiVersion 1.0.0
     * @apiGroup ISYSTEM
     * @apiHeader {string} Authorization Token
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
     * @api {GET} /system_role 系统角色列表
     * @apiVersion 1.0.0
     * @apiGroup ISYSTEM
     * @apiHeader {string} Authorization Token
     * @apiParam {string} name 帐号
     * @apiParam {number} current 当前页
     * @apiParam {number} pageSize 页大小
     * @apiParam {string} filter ProTable的filter
     * @apiParam {string} sorter ProTable的sorter
     * @apiSuccess {number} total 数据总计
     * @apiSuccess {Object[]} data 数据列表
     * @apiSuccess {number} data.id 系统角色ID
     * @apiSuccess {string} data.name 名称
     * @apiSuccess {number} data.status 状态(0=禁用,1=正常)
     * @apiSuccess {string} data.create_time 创建时间
     */
    public function index()
    {
        $current = $this->request->get('current/d', 1);
        $pageSize = $this->request->get('pageSize/d', 10);
        $search = $this->request->only(['name', 'filter', 'sorter'], 'get');

        $total = SelfModel::withSearch(array_keys($search), $search)->count();
        $list = SelfModel::withSearch(array_keys($search), $search)->page($current, $pageSize)->select();

        return $this->success([
            'total' => $total,
            'data' => $list->visible(['id', 'name', 'status', 'create_time'])->toArray()
        ]);
    }

    /**
     * @api {GET} /system_role/:id 系统角色信息
     * @apiVersion 1.0.0
     * @apiGroup ISYSTEM
     * @apiHeader {string} Authorization Token
     * @apiSuccess {number} id 系统角色ID
     * @apiSuccess {string} name 名称
     * @apiSuccess {number} status 状态(0=禁用,1=正常)
     * @apiSuccess {string} create_time 创建时间
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
