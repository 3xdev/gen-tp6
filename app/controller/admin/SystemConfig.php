<?php

namespace app\controller\admin;

use app\model\SystemConfig as SelfModel;

/**
 * @apiDefine ISYS 系统
 */
class SystemConfig extends Base
{
    /**
     * @api {put} /setting 更新系统配置
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiBody {String} :code 系统配置
     * @apiParamExample {json} Request-Example:
     *   {
     *     "retry_max": 99,
     *     "site_name": "xxx"
     *   }
     */
    public function setting()
    {
        $data = $this->request->post();

        $configs = SelfModel::where('code', 'in', array_keys($data))->select();
        foreach ($configs as $config) {
            $config->value = $data[$config['code']];
            $config->save();
        }

        return $this->success();
    }

    /**
     * @api {post} /configs 创建配置项
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiBody {String} tab 分组
     * @apiBody {String} component 组件
     * @apiBody {String} code 编码
     * @apiBody {String} title 标题
     * @apiBody {String} [tip] 提示
     */
    public function create()
    {
        $data = $this->request->post(['tab', 'component', 'code', 'title', 'tip', 'value_enum_rel', 'reactions', 'component_props', 'decorator_props', 'validator']);
        $data['delete_time'] = 0;
        $this->validate($data, 'SystemConfig');

        // 创建配置项
        SelfModel::create($data);

        return $this->success();
    }

    /**
     * @api {put} /configs/:id 更新配置项
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiParam {Number} id ID
     * @apiBody {String} tab 分组
     * @apiBody {String} component 组件
     * @apiBody {String} code 编码
     * @apiBody {String} title 标题
     * @apiBody {String} [tip] 提示
     */
    public function update($id)
    {
        $data = $this->request->post(['tab', 'component', 'code', 'title', 'tip', 'value_enum_rel', 'reactions', 'component_props', 'decorator_props', 'validator']);
        $data['delete_time'] = 0;

        $model = SelfModel::find($id);
        if (!$model) {
            return $this->error();
        }
        if ($model->code == $data['code']) {
            $this->validate($data, 'SystemConfig.update');
        } else {
            $this->validate($data, 'SystemConfig');
        }

        $model->save($data);
        return $this->success();
    }

    /**
     * @api {delete} /configs/:ids 删除配置项
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiParam {String} ids ID串
     */
    public function delete($ids)
    {
        SelfModel::destroy(explode(',', $ids));
        return $this->success();
    }

    /**
     * @api {get} /configs 配置项列表
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiQuery {String} [code] 编码
     * @apiQuery {String} [title] 标题
     * @apiQuery {String} [value] 配置值
     * @apiQuery {Number} [current] 当前页
     * @apiQuery {Number} [pageSize] 页大小
     * @apiQuery {String} [filter] ProTable的filter
     * @apiQuery {String} [sorter] ProTable的sorter
     * @apiSuccess {Number} total 数据总计
     * @apiSuccess {Object[]} data 数据列表
     * @apiSuccess {Number} data.id 配置项ID
     * @apiSuccess {String} data.tab 分组
     * @apiSuccess {String} data.component 组件
     * @apiSuccess {String} data.code 编码
     * @apiSuccess {String} data.title 标题
     * @apiSuccess {String} data.tip 提示
     * @apiSuccess {String} data.value 配置值
     * @apiSuccess {String} data.create_time 创建时间
     * @apiSuccess {String} data.update_time 更新时间
     */
    public function index()
    {
        $current = $this->request->get('current/d', 1);
        $pageSize = $this->request->get('pageSize/d', 10);
        $search = $this->request->only(['code', 'title', 'value', 'filter'], 'get');
        $lsearch = $this->request->only(['code', 'title', 'value', 'filter', 'sorter'], 'get');

        $total = SelfModel::withSearch(array_keys($search), $search)->count();
        $list = SelfModel::withSearch(array_keys($lsearch), $lsearch)->page($current, $pageSize)->select();

        return $this->success([
            'total' => $total,
            'data' => $list->visible(['id', 'tab', 'component', 'code', 'title', 'tip', 'value_enum_rel', 'reactions', 'component_props', 'decorator_props', 'validator', 'value', 'create_time', 'update_time'])->toArray()
        ]);
    }

    /**
     * @api {get} /configs/:id 配置项信息
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiParam {Number} id ID
     * @apiSuccess {Number} id 配置项ID
     * @apiSuccess {String} tab 分组
     * @apiSuccess {String} component 组件
     * @apiSuccess {String} code 编码
     * @apiSuccess {String} title 标题
     * @apiSuccess {String} tip 提示
     * @apiSuccess {String} value 配置值
     * @apiSuccess {String} create_time 创建时间
     * @apiSuccess {String} update_time 更新时间
     */
    public function read($id)
    {
        $model = SelfModel::find($id);
        if (!$model) {
            return $this->error();
        }

        return $this->success($model->visible([
            'id', 'tab', 'component', 'code', 'title', 'tip', 'value_enum_rel', 'reactions', 'component_props', 'decorator_props', 'validator', 'value', 'create_time', 'update_time'
        ])->toArray());
    }
}
