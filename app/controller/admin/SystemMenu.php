<?php

namespace app\controller\admin;

use app\model\SystemMenu as SelfModel;
use tauthz\facade\Enforcer;

class SystemMenu extends Base
{
    /**
     * @api {POST} /menu 创建菜单
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiParam {number} parent_id 父ID
     * @apiParam {string} name 名称
     * @apiParam {string} path 访问路由
     * @apiParam {string} icon 图标
     * @apiParam {number} sort 排序
     */
    public function create()
    {
        $data = $this->request->post(['parent_id', 'name', 'path', 'table_code', 'icon', 'sort']);
        $this->validate($data, 'SystemMenu');

        SelfModel::create($data);

        return $this->success();
    }

    /**
     * @api {PUT} /menu/:id 更新菜单
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiParam {string} name 名称
     * @apiParam {string} path 访问路由
     * @apiParam {string} icon 图标
     * @apiParam {number} sort 排序
     * @apiParam {number} status 状态(0=禁用,1=正常)
     */
    public function update($id)
    {
        $data = $this->request->post(['name', 'path', 'table_code', 'icon', 'sort', 'status']);

        $model = SelfModel::find($id);
        if (!$model) {
            return $this->error('不存在', 404);
        }

        $this->validate($data, 'SystemMenu.update');
        $model->save($data);

        return $this->success();
    }

    /**
     * @api {DELETE} /menu/:ids 删除菜单
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     */
    public function delete($ids)
    {
        $models = SelfModel::where('id', 'in', explode(',', $ids))->select();
        if (!$models) {
            return $this->error('不存在', 404);
        }

        foreach ($models as $model) {
            $model->delete();
        }
        return $this->success();
    }

    /**
     * @api {GET} /menu 菜单列表
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiParam {string} name 名称
     * @apiParam {string} path 访问路由
     * @apiParam {number} parent_id 父ID
     * @apiParam {number} status 状态(0=禁用,1=正常)
     * @apiSuccess {Object[]} data 菜单列表
     * @apiSuccess {number} data.id 菜单ID
     * @apiSuccess {string} data.name 名称
     * @apiSuccess {number} data.parent_id 父ID
     * @apiSuccess {string} data.path 访问路由
     * @apiSuccess {string} data.table_code 关联表格
     * @apiSuccess {string} data.icon 图标
     * @apiSuccess {number} data.sort 排序
     * @apiSuccess {number} data.status 状态(0=禁用,1=正常)
     * @apiSuccess {string} data.create_time 创建时间
     */
    public function index()
    {
        $search = $this->request->only(['name', 'path', 'parent_id'], 'get');

        $list = SelfModel::withSearch(array_keys($search), $search)->order('sort')->select();

        return $this->success([
            'data' => new \BlueM\Tree(
                $list->visible(['id', 'name', 'parent_id', 'path', 'table_code', 'icon', 'sort', 'status', 'create_time'])->toArray(),
                ['parent' => 'parent_id', 'jsonSerializer' => new \BlueM\Tree\Serializer\HierarchicalTreeJsonSerializer()]
            ),
        ]);
    }

    /**
     * @api {GET} /menu/:id 菜单信息
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiSuccess {number} id 菜单ID
     * @apiSuccess {string} name 名称
     * @apiSuccess {number} parent_id 父ID
     * @apiSuccess {string} path 访问路由
     * @apiSuccess {string} icon 图标
     * @apiSuccess {number} sort 排序
     * @apiSuccess {number} status 状态(0=禁用,1=正常)
     * @apiSuccess {string} create_time 创建时间
     */
    public function read($id)
    {
        $menu = SelfModel::find($id);
        if (!$menu) {
            return $this->error('不存在', 404);
        }

        return $this->success($menu->visible([
            'id', 'name', 'parent_id', 'path', 'icon', 'sort', 'status', 'create_time'
        ])->toArray());
    }
}
