<?php

namespace app\controller\admin;

use app\model\SystemConfig as SelfModel;

/**
 * @apiDefine ISYS 系统
 */
class SystemConfig extends Base
{
    /**
     * @api {PUT} /setting 更新系统配置
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiParam {string} :code 系统配置
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
     * @api {POST} /configs 创建配置项
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiParam {string} tab 分组
     * @apiParam {string} component 组件
     * @apiParam {string} code 编码
     * @apiParam {string} title 标题
     * @apiParam {string} [description] 描述说明
     * @apiParam {string} [dict_key] 字典代码
     * @apiParam {string} [rule] 验证规则
     * @apiParam {string} [extend] 扩展属性
     */
    public function create()
    {
        $data = $this->request->post(['tab', 'component', 'code', 'title', 'description', 'dict_key', 'rule', 'extend']);
        $data['delete_time'] = 0;
        $this->validate($data, 'SystemConfig');

        // 创建配置项
        SelfModel::create($data);

        return $this->success();
    }

    /**
     * @api {PUT} /configs/:id 更新配置项
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiParam {string} tab 分组
     * @apiParam {string} component 组件
     * @apiParam {string} code 编码
     * @apiParam {string} title 标题
     * @apiParam {string} [description] 描述说明
     * @apiParam {string} [dict_key] 字典代码
     * @apiParam {string} [rule] 验证规则
     * @apiParam {string} [extend] 扩展属性
     */
    public function update($id)
    {
        $data = $this->request->post(['tab', 'component', 'code', 'title', 'description', 'dict_key', 'rule', 'extend']);
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
     * @api {DELETE} /configs/:ids 删除配置项
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     */
    public function delete($ids)
    {
        SelfModel::destroy(explode(',', $ids));
        return $this->success();
    }

    /**
     * @api {GET} /configs 配置项列表
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiParam {string} code 编码
     * @apiParam {string} title 标题
     * @apiParam {string} value 配置值
     * @apiParam {number} current 当前页
     * @apiParam {number} pageSize 页大小
     * @apiParam {string} filter ProTable的filter
     * @apiParam {string} sorter ProTable的sorter
     * @apiSuccess {number} total 数据总计
     * @apiSuccess {Object[]} data 数据列表
     * @apiSuccess {number} data.id 配置项ID
     * @apiSuccess {string} data.tab 分组
     * @apiSuccess {string} data.component 组件
     * @apiSuccess {string} data.code 编码
     * @apiSuccess {string} data.title 标题
     * @apiSuccess {string} data.description 描述说明
     * @apiSuccess {string} data.dict_key 字典代码
     * @apiSuccess {string} data.rule 验证规则
     * @apiSuccess {string} data.extend 扩展属性
     * @apiSuccess {string} data.value 配置值
     * @apiSuccess {string} data.create_time 创建时间
     * @apiSuccess {string} data.update_time 更新时间
     */
    public function index()
    {
        $current = $this->request->get('current/d', 1);
        $pageSize = $this->request->get('pageSize/d', 10);
        $search = $this->request->only(['code', 'title', 'value', 'filter', 'sorter'], 'get');

        $total = SelfModel::withSearch(array_keys($search), $search)->count();
        $list = SelfModel::withSearch(array_keys($search), $search)->page($current, $pageSize)->select();

        return $this->success([
            'total' => $total,
            'data' => $list->visible(['id', 'tab', 'component', 'code', 'title', 'description', 'dict_key', 'rule', 'extend', 'value', 'create_time', 'update_time'])->toArray()
        ]);
    }

    /**
     * @api {GET} /configs/:id 配置项信息
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiSuccess {number} id 配置项ID
     * @apiSuccess {string} tab 分组
     * @apiSuccess {string} component 组件
     * @apiSuccess {string} code 编码
     * @apiSuccess {string} title 标题
     * @apiSuccess {string} description 描述说明
     * @apiSuccess {string} dict_key 字典代码
     * @apiSuccess {string} rule 验证规则
     * @apiSuccess {string} extend 扩展属性
     * @apiSuccess {string} value 配置值
     * @apiSuccess {string} create_time 创建时间
     * @apiSuccess {string} update_time 更新时间
     */
    public function read($id)
    {
        $model = SelfModel::find($id);
        if (!$model) {
            return $this->error();
        }

        return $this->success($model->visible([
            'id', 'tab', 'component', 'code', 'title', 'description', 'dict_key', 'rule', 'extend', 'value', 'create_time', 'update_time'
        ])->toArray());
    }
}
