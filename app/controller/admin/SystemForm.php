<?php

namespace app\controller\admin;

use app\model\SystemConfig as SystemConfigModel;
use app\model\SystemDict as SystemDictModel;
use app\model\SystemForm as SelfModel;

class SystemForm extends Base
{
    /**
     * @api {POST} /form 创建表单
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiParam {string} code 代码
     * @apiParam {string} name 名称
     * @apiParam {string} schema_string Schema字符串
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
     * @api {PUT} /form/:name 更新表单
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiParam {string} code 代码
     * @apiParam {string} name 名称
     * @apiParam {string} schema_string Schema字符串
     * @apiParam {number} status 状态(0=禁用,1=正常)
     */
    public function update($name)
    {
        $data = $this->request->post(['code', 'name', 'schema_string', 'status']);
        $data['delete_time'] = 0;

        $model = SelfModel::find($name);
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
     * @api {DELETE} /form/:names 删除表单
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
            $model->delete();
        }
        return $this->success();
    }

    /**
     * @api {GET} /form 表单列表
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
     * @apiSuccess {object} data.schema 属性
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
            'code', 'name', 'schema', 'status', 'create_time'
        ];
        return $this->success([
            'total' => $total,
            'data' => $list->visible($visible)->toArray()
        ]);
    }

    /**
     * @api {GET} /form/:name 表单信息
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiSuccess {string} code 代码
     * @apiSuccess {string} name 名称
     * @apiSuccess {object} schema 属性
     * @apiSuccess {number} status 状态(0=禁用,1=正常)
     * @apiSuccess {string} create_time 创建时间
     */
    public function read($name)
    {
        $form = SelfModel::find($name);
        if (!$form) {
            return $this->error('不存在', 404);
        }

        return $this->success($form->visible([
            'code', 'name', 'schema', 'status', 'create_time',
        ])->toArray());
    }



    /**
     * @api {GET} /schema/formily/form/:name 获取系统表单(Formily)的schema描述
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiSuccess {string} type
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
                $form = SelfModel::find($name);
                $form && $schema = $form->schema;
                break;
        }

        return $this->success($schema);
    }

    // 构建配置
    private function buildSetting()
    {
        $json = [];
        $dict = SystemDictModel::find('config_tab');
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
