<?php

namespace app\controller\admin;

use app\model\SystemConfig as SystemConfigModel;
use app\model\SystemDict as SystemDictModel;
use app\model\SystemForm as SelfModel;

class SystemForm extends Base
{
    /**
     * @api {post} /form 创建表单
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiBody {String} code 代码
     * @apiBody {String} name 名称
     * @apiBody {String} schema_string Schema字符串
     */
    public function create()
    {
        $data = $this->request->post(['name', 'code', 'schema_string']);
        $data['delete_time'] = 0;

        $this->validate($data, 'SystemForm');

        SelfModel::create($data);

        return $this->success();
    }

    /**
     * @api {put} /form/:name 更新表单
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiParam {string} name 表单代码
     * @apiBody {String} code 代码
     * @apiBody {String} name 名称
     * @apiBody {String} schema_string Schema字符串
     * @apiBody {Number} status 状态(0=禁用,1=正常)
     */
    public function update($name)
    {
        $data = $this->request->post(['code', 'name', 'schema_string', 'status']);
        $data['delete_time'] = 0;

        $model = SelfModel::where('code', $name)->find();
        if (!$model) {
            return $this->error('不存在', 404);
        }

        if ($model->code == $data['code']) {
            $this->validate($data, 'SystemForm.update');
        } else {
            $this->validate($data, 'SystemForm');
        }

        $model->save($data);

        return $this->success();
    }

    /**
     * @api {delete} /form/:names 删除表单
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiParam {string} names 表单代码串
     */
    public function delete($names)
    {
        $models = SelfModel::whereIn('code', explode(',', $names))->select();
        if (!$models) {
            return $this->error('不存在', 404);
        }

        foreach ($models as $model) {
            $model->delete();
        }
        return $this->success();
    }

    /**
     * @api {get} /form 表单列表
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
     * @apiSuccess {object} data.schema 属性
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
            'code', 'name', 'schema', 'status', 'create_time'
        ];
        return $this->success([
            'total' => $total,
            'data' => $list->visible($visible)->toArray()
        ]);
    }

    /**
     * @api {get} /form/:name 表单信息
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiParam {string} name 表单代码
     * @apiSuccess {String} code 代码
     * @apiSuccess {String} name 名称
     * @apiSuccess {object} schema 属性
     * @apiSuccess {Number} status 状态(0=禁用,1=正常)
     * @apiSuccess {String} create_time 创建时间
     */
    public function read($name)
    {
        $form = SelfModel::where('code', $name)->find();
        if (!$form) {
            return $this->error('不存在', 404);
        }

        return $this->success($form->visible([
            'code', 'name', 'schema', 'status', 'create_time',
        ])->toArray());
    }



    /**
     * @api {get} /schema/formily/form/:name 获取系统表单(Formily)的schema描述
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiParam {string} name 表单代码
     * @apiSuccess {String} type
     * @apiSuccess {object} properties
     */
    public function formily($name)
    {
        $schema = [
            'type' => 'object',
            'properties' => []
        ];

        switch ($name) {
            // 系统配置
            case 'setting':
                $schema['properties'] = $this->buildSetting();
                break;
            default:
                $form = SelfModel::where('code', $name)->find();
                $form && $schema = $form->schema;
                break;
        }

        return $this->success($schema);
    }

    // 构建配置
    private function buildSetting()
    {
        $json = [];
        $dict = SystemDictModel::where('key_', 'config_tab')->find();
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
        $configs = SystemConfigModel::select();
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
