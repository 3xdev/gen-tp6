<?php

namespace app\controller\admin;

use app\model\Config as ConfigModel;
use app\model\Dict as DictModel;
use app\model\Table as SelfModel;

class Table extends Base
{
    /**
     * @api {POST} /table 创建表格
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiParam {string} code 代码
     * @apiParam {string} name 名称
     * @apiParam {string} props_string 属性字符串
     */
    public function create()
    {
        $data = $this->request->post(['name', 'code', 'props_string']);
        $this->validate($data, 'Table');

        SelfModel::create($data);

        return $this->success();
    }

    /**
     * @api {PUT} /table/:name 更新表格
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiParam {string} code 代码
     * @apiParam {string} name 名称
     * @apiParam {string} props_string 属性字符串
     * @apiParam {number} status 状态(0=禁用,1=正常)
     */
    public function update($name)
    {
        $data = $this->request->post(['code', 'name', 'props_string', 'status']);
        $options = $this->request->post('options/a');
        $cols = $this->request->post('cols/a');
        foreach ($cols as $index => &$col) {
            $col['sort'] = $index;
        }

        $model = SelfModel::find($name);
        if (!$model) {
            return $this->error('不存在', 404);
        }

        if ($model->code == $data['code']) {
            $this->validate($data, 'Table.update');
        } else {
            $this->validate($data, 'Table');
        }

        $data['options'] = $options;
        $model->save($data);
        $model->cols()->delete();
        $model->cols()->saveAll($cols);

        return $this->success();
    }

    /**
     * @api {DELETE} /table/:names 删除表格
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     */
    public function delete($names)
    {
        $models = SelfModel::select(explode(',', $names));
        if (!$models) {
            return $this->error('不存在', 404);
        }

        foreach ($models as $model) {
            $model->together(['cols'])->delete();
        }
        return $this->success();
    }

    /**
     * @api {GET} /table 表格列表
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiParam {string} code 代码
     * @apiParam {string} name 名称
     * @apiParam {number} status 状态
     * @apiParam {number} current 当前页
     * @apiParam {number} pageSize 页大小
     * @apiParam {string} filter ProTable的filter
     * @apiParam {string} sorter ProTable的sorter
     * @apiSuccess {number} total 数据总计
     * @apiSuccess {object[]} data 数据列表
     * @apiSuccess {string} data.code 代码
     * @apiSuccess {string} data.name 名称
     * @apiSuccess {object} data.props 属性
     * @apiSuccess {number} data.status 状态(0=禁用,1=正常)
     * @apiSuccess {string} data.create_time 创建时间
     */
    public function index()
    {
        $current = $this->request->get('current/d', 1);
        $pageSize = $this->request->get('pageSize/d', 10);
        $search = $this->request->only(['code', 'name', 'status', 'filter', 'sorter'], 'get');

        $total = SelfModel::withSearch(array_keys($search), $search)->count();
        $list = SelfModel::withSearch(array_keys($search), $search)->page($current, $pageSize)->select();

        $visible = [
            'code', 'name', 'props', 'status', 'create_time'
        ];
        return $this->success([
            'total' => $total,
            'data' => $list->visible($visible)->toArray()
        ]);
    }

    /**
     * @api {GET} /table/:name 表格信息
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiSuccess {string} code 代码
     * @apiSuccess {string} name 名称
     * @apiSuccess {object} props 属性
     * @apiSuccess {number} status 状态(0=禁用,1=正常)
     * @apiSuccess {string} create_time 创建时间
     */
    public function read($name)
    {
        $table = SelfModel::with(['cols'])->find($name);
        if (!$table) {
            return $this->error('不存在', 404);
        }

        return $this->success($table->visible([
            'code', 'name', 'props', 'options', 'status', 'create_time',
            'cols' => ['data_index', 'value_type', 'value_enum_dict_key', 'title', 'tip',
                        'ellipsis', 'copyable', 'filters', 'col_size',
                        'hide_in_search', 'hide_in_table', 'hide_in_form', 'hide_in_descriptions']
        ])->toArray());
    }

    /**
     * @api {GET} /schema/protable/:name 获取高级表格(ProTable)的schema描述
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiSuccess {object} columns 列定义
     */
    public function protable($name)
    {
        $table = SelfModel::find($name);
        if (!$table) {
            return $this->error('数据未找到');
        }

        return $this->success($table->pro_schema);
    }


    /**
     * @api {GET} /schema/:name 获取Formily的schema描述
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiSuccess {string} type
     * @apiSuccess {object} properties
     */
    public function formily($name)
    {
        $properties = [];

        switch ($name) {
            case 'setting':
                $properties = $this->buildSetting();
                break;
            default:
                $table = SelfModel::find($name);
                $properties = $table ? $table->formily_schema : [];
                break;
        }

        return $this->success([
            'type' => 'object',
            'properties' => $properties
        ]);
    }

    // 构建配置
    private function buildSetting()
    {
        $json = [];
        $dict = DictModel::find('config_tab');
        $map = $dict ? $dict->items->column('label', 'key_') : [];
        empty($map) && $map = ['default' => '系统配置'];
        foreach ($map as $key => $value) {
            $json[$key] = [
                'type' => 'void',
                'x-component' => 'FormTab.TabPane',
                'x-component-props' => [
                    'tab' => $value
                ],
                'properties' => []
            ];
        }
        $configs = ConfigModel::select();
        foreach ($configs as $config) {
            $json[$config->tab ?: 'default']['properties'][$config->code] = $config->schema;
        }

        return [
            'tabs' => [
                'type' => 'void',
                'x-component' => 'FormTab',
                'x-component-props' => [
                    'type' => 'card'
                ],
                'properties' => $json
            ]
        ];
    }
}
