<?php

namespace app\controller\admin;

use app\model\SystemTable as SelfModel;
use tauthz\facade\Enforcer;

class SystemTable extends Base
{
    /**
     * @api {post} /table 创建表格
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiBody {String} code 代码
     * @apiBody {String} name 名称
     * @apiBody {String} props_string 属性字符串
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
     * @api {put} /table/:name 更新表格
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiParam {String} name 表格代码
     * @apiBody {String} code 代码
     * @apiBody {String} name 名称
     * @apiBody {String} props_string 属性字符串
     * @apiBody {Number} status 状态(0=禁用,1=正常)
     */
    public function update($name)
    {
        $data = $this->request->post(['code', 'name', 'props_string', 'status']);
        $data['delete_time'] = 0;
        $data_cols = $this->request->post('cols/a');
        foreach ($data_cols as $index => &$col) {
            $col['sort'] = $index;
        }
        $data_options = [];
        $options = $this->request->post('options/a');
        foreach ($options as $gkey => $group) {
            foreach ($group as $index => $option) {
                $data_options[] = array_merge($option, ['group' => $gkey, 'sort' => $index]);
            }
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

        $model->save($data);
        $model->cols->delete();
        $model->cols()->saveAll($data_cols);
        $model->options->delete();
        $model->options()->saveAll($data_options);

        return $this->success();
    }

    /**
     * @api {delete} /table/:names 删除表格
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiParam {String} names 表格代码串
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
     * @api {get} /table 表格列表
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiQuery {String} [code] 代码
     * @apiQuery {String} [name] 名称
     * @apiQuery {Number} [status] 状态
     * @apiQuery {Number} [current] 当前页
     * @apiQuery {Number} [pageSize] 页大小
     * @apiQuery {String} [filter] ProTable的filter
     * @apiQuery {String} [sorter] ProTable的sorter
     * @apiSuccess {Number} total 数据总计
     * @apiSuccess {object[]} data 数据列表
     * @apiSuccess {String} data.code 代码
     * @apiSuccess {String} data.name 名称
     * @apiSuccess {object} data.props 属性
     * @apiSuccess {Number} data.status 状态(0=禁用,1=正常)
     * @apiSuccess {String} data.create_time 创建时间
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
     * @api {get} /table/:name 表格信息
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiParam {String} name 表格代码
     * @apiSuccess {String} code 代码
     * @apiSuccess {String} name 名称
     * @apiSuccess {object} props 属性
     * @apiSuccess {Number} status 状态(0=禁用,1=正常)
     * @apiSuccess {String} create_time 创建时间
     */
    public function read($name)
    {
        $table = SelfModel::with(['cols', 'options'])->where('code', $name)->find();
        if (!$table) {
            return $this->error('不存在', 404);
        }

        $data = $table->visible([
            'code', 'name', 'props', 'status', 'create_time',
            'cols' => ['data_index', 'value_type', 'value_enum_rel', 'title', 'tip', 'template_text', 'template_link_to',
                        'required', 'default_value', 'component_props', 'decorator_props', 'reactions', 'validator',
                        'ellipsis', 'copyable', 'filters', 'sorter', 'width', 'col_size',
                        'hide_in_search', 'hide_in_table', 'hide_in_form', 'hide_in_descriptions'],
            'options' => ['group', 'type', 'action', 'title', 'target', 'body']
        ])->toArray();
        $options = [
            'columns' => [],
            'toolbar' => [],
            'batch' => [],
        ];
        foreach ($data['options'] as $option) {
            isset($options[$option['group']]) && $options[$option['group']][] = $option;
        }
        $data['options'] = $options;
        return $this->success($data);
    }

    /**
     * @api {get} /schema/protable/:name 获取高级表格(ProTable)的schema描述
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiParam {String} name 表格代码
     * @apiSuccess {object} columns 列定义
     */
    public function protable($name)
    {
        $table = SelfModel::where('code', $name)->find();
        if (!$table) {
            return $this->error('数据未找到');
        }

        $schema = $table->pro_components_schema;

        $authzIdentifier = $this->request->admin ? 'admin_' . $this->request->admin->id : '';
        $roles = Enforcer::getRolesForUser($authzIdentifier);
        foreach ($schema['options'] as $gkey => $gvalue) {
            foreach ($gvalue as $key => $value) {
                $act = in_array($value['type'], ['view', 'export']) ? 'get' : $value['action'];
                if (!in_array('role_1', $roles) && !Enforcer::enforce($authzIdentifier, $name, $act)) {
                    unset($schema['options'][$gkey][$key]);
                }
            }
        }
        $schema['options']['columns'] = array_values($schema['options']['columns']);
        $schema['options']['toolbar'] = array_values($schema['options']['toolbar']);
        $schema['options']['batch'] = array_values($schema['options']['batch']);

        return $this->success($schema);
    }


    /**
     * @api {get} /schema/formily/table/:name 获取系统表格(Formily)的schema描述
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiParam {String} name 表格代码
     * @apiSuccess {String} type
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
