<?php

namespace app\controller\admin;

use app\model\SystemMenu as SelfModel;

class SystemMenu extends Base
{
    /**
     * @api {post} /menu 创建菜单
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiBody {Number} parent_id 父ID
     * @apiBody {String} name 名称
     * @apiBody {String} path 访问路由
     * @apiBody {String} icon 图标
     * @apiBody {Number} sort 排序
     */
    public function create()
    {
        $data = $this->request->post(['parent_id', 'name', 'path', 'table_code', 'icon', 'sort']);
        $this->validate($data, 'SystemMenu');

        SelfModel::create($data);

        return $this->success();
    }

    /**
     * @api {put} /menu/:id 更新菜单
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiParam {Number} id ID
     * @apiBody {String} name 名称
     * @apiBody {String} path 访问路由
     * @apiBody {String} icon 图标
     * @apiBody {Number} sort 排序
     * @apiBody {Number} status 状态(0=禁用,1=正常)
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
     * @api {delete} /menu/:ids 删除菜单
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiParam {String} ids ID串
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
     * @api {get} /menu 菜单列表
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiQuery {String} [name] 名称
     * @apiQuery {String} [path] 访问路由
     * @apiQuery {Number} [parent_id] 父ID
     * @apiQuery {Number} [status] 状态(0=禁用,1=正常)
     * @apiSuccess {Object[]} data 菜单列表
     * @apiSuccess {Number} data.id 菜单ID
     * @apiSuccess {String} data.name 名称
     * @apiSuccess {Number} data.parent_id 父ID
     * @apiSuccess {String} data.path 访问路由
     * @apiSuccess {String} data.table_code 关联表格
     * @apiSuccess {String} data.icon 图标
     * @apiSuccess {Number} data.sort 排序
     * @apiSuccess {Number} data.status 状态(0=禁用,1=正常)
     * @apiSuccess {String} data.create_time 创建时间
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
     * @api {get} /menu/:id 菜单信息
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiParam {Number} id ID
     * @apiSuccess {Number} id 菜单ID
     * @apiSuccess {String} name 名称
     * @apiSuccess {Number} parent_id 父ID
     * @apiSuccess {String} path 访问路由
     * @apiSuccess {String} icon 图标
     * @apiSuccess {Number} sort 排序
     * @apiSuccess {Number} status 状态(0=禁用,1=正常)
     * @apiSuccess {String} create_time 创建时间
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
