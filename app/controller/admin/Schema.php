<?php

namespace app\controller\admin;

use app\model\Dict as DictModel;
use app\model\Config as ConfigModel;

/**
 * @apiDefine ISYS 系统
 */
class Schema extends Base
{
    /**
     * @api {GET} /schemas/:name 获取Formily的schema描述
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     * @apiSuccess {string} type
     * @apiSuccess {object} properties
     */
    public function read($name)
    {
        $properties = [];

        switch (strtolower($name)) {
            case 'setting':
                $properties = $this->buildSetting();
                break;
            default:
                $properties = $this->buildCURD();
                break;
        }

        return $this->success([
            'type' => "object",
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
                "type" => "void",
                "x-component" => "FormTab.TabPane",
                "x-component-props" => [
                    "tab" => $value
                ],
                "properties" => []
            ];
        }
        $configs = ConfigModel::select();
        foreach ($configs as $config) {
            $json[$config->tab ?: 'default']['properties'][$config->code] = $config->schema;
        }

        return [
            "tabs" => [
                "type" => "void",
                "x-component" => "FormTab",
                "x-component-props" => [
                    "type" => "card"
                ],
                "properties" => $json
            ]
        ];
    }

    // 构建CURD
    private function buildCURD()
    {
        return [];
    }
}
