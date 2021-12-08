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

        $properties["tabs"] = [
            "type" => "void",
            "x-component" => "FormTab",
            "x-component-props" => [
                "type" => "card"
            ],
            "properties" => []
        ];
        $config_tab = DictModel::find('config_tab');
        foreach ($config_tab->items as $tab) {
            $properties["tabs"]["properties"][$tab->key_] = [
                "type" => "void",
                "x-component" => "FormTab.TabPane",
                "x-component-props" => [
                    "tab" => $tab->label
                ],
                "properties" => []
            ];
        }
        $configs = ConfigModel::select();
        foreach ($configs as $config) {
            $properties["tabs"]["properties"][$config->tab]['properties'][$config->code] = [
                "required" => true,
                "type" => "string",
                "title" => $config->title,
                "description" => $config->description,
                "enum" => $config->dict ? $config->dict->items->map(fn($item) => [
                            'label' => $item['label'],
                            'value' => $item['key_'],
                          ]) : [],
                "x-component" => $config->component,
                "x-decorator" => "FormItem"
            ];
        }

        return $this->success([
            'type' => "object",
            'properties' => $properties
        ]);
    }
}
