<?php

namespace app\controller\admin;

use app\model\SystemTable as SelfModel;

class SystemTable extends Base
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
        $data['delete_time'] = 0;
        $this->validate($data, 'SystemTable');

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
        $data['delete_time'] = 0;
        $options = $this->request->post('options/a');
        $cols = $this->request->post('cols/a');
        foreach ($cols as $index => &$col) {
            $col['sort'] = $index;
        }

        $model = SelfModel::where('code', $name)->find();
        if (!$model) {
            return $this->error('不存在', 404);
        }

        if ($model->code == $data['code']) {
            $this->validate($data, 'SystemTable.update');
        } else {
            $this->validate($data, 'SystemTable');
        }

        $data['options'] = $options;
        $model->save($data);
        $model->cols->delete();
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
        $models = SelfModel::whereIn('code', explode(',', $names))->select();
        if (!$models) {
            return $this->error('不存在', 404);
        }

        foreach ($models as $model) {
            $model->cols->delete();
            $model->delete();
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
        $search = $this->request->only(['code', 'name', 'status', 'filter'], 'get');
        $lsearch = $this->request->only(['code', 'name', 'status', 'filter', 'sorter'], 'get');

        $total = SelfModel::withSearch(array_keys($search), $search)->count();
        $list = SelfModel::withSearch(array_keys($lsearch), $lsearch)->page($current, $pageSize)->select();

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
        $table = SelfModel::with(['cols'])->where('code', $name)->find();
        if (!$table) {
            return $this->error('不存在', 404);
        }

        return $this->success($table->visible([
            'code', 'name', 'props', 'options', 'status', 'create_time',
            'cols' => ['data_index', 'value_type', 'value_enum_rel', 'title', 'tip',
                        'required', 'default_value', 'validator',
                        'ellipsis', 'copyable', 'filters', 'sorter', 'col_size',
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
        $table = SelfModel::where('code', $name)->find();
        if (!$table) {
            return $this->error('数据未找到');
        }

        return $this->success($table->pro_schema);
    }


    /**
     * @api {GET} /schema/formily/table/:name 获取系统表格(Formily)的schema描述
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiSuccess {string} type
     * @apiSuccess {object} properties
     */
    public function formily($name)
    {
        $table = SelfModel::where('code', $name)->find();

        return $this->success([
            'type' => 'object',
            'properties' => $table ? $table->formily_schema : []
        ]);
    }
}
