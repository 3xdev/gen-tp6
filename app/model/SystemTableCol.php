<?php

namespace app\model;

use think\model\concern\SoftDelete;

class SystemTableCol extends Base
{
    use SoftDelete;

    // 设置json类型字段
    protected $json = ['value_enum_rel'];
    // 设置json数据返回数组
    protected $jsonAssoc = true;

    public function searchTitleAttr($query, $value, $data)
    {
        $value && $query->where('title', 'like', '%' . $value . '%');
    }
    public function searchDataIndexAttr($query, $value, $data)
    {
        $value && $query->where('data_index', 'like', '%' . $value . '%');
    }

    public function setComponentPropsAttr($value, $data)
    {
        return json_decode($value) ? json_encode(json_decode($value)) : '{}';
    }
    public function setDecoratorPropsAttr($value, $data)
    {
        return json_decode($value) ? json_encode(json_decode($value)) : '{}';
    }
    public function setReactionsAttr($value, $data)
    {
        return json_decode($value) ? json_encode(json_decode($value)) : '{}';
    }
    public function setValidatorAttr($value, $data)
    {
        return json_decode($value) ? json_encode(json_decode($value)) : '{}';
    }

    // 关联模型定义名
    public function getRelationNameAttr($value, $data)
    {
        return strrpos($data['data_index'], '.') ? substr($data['data_index'], 0, strrpos($data['data_index'], '.')) : '';
    }
    public function getProSchemaAttr($value, $data)
    {
        $schema = [
            'title' => $data['title'],
            'dataIndex' => strpos($data['data_index'], '.') ? explode('.', $data['data_index']) : $data['data_index'],
        ];
        !empty($data['tip']) && $schema['tooltip'] = $data['tip'];
        !empty($data['value_type']) && $schema['valueType'] = $data['value_type'];
        !empty($data['template_text']) && $schema['templateText'] = $data['template_text'];
        !empty($data['template_link_to']) && $schema['templateLinkTo'] = $data['template_link_to'];
        if (!empty($data['value_enum_rel'])) {
            if ($data['value_enum_rel'][0] == 'suggest') {
                $schema['requestTable'] = $data['value_enum_rel'][1];
                //$schema['fieldProps']['debounceTime'] = 800;
                $schema['fieldProps']['showSearch'] = true;
            } else {
                $schema['valueEnum'] = system_col_rel_kv($data['value_enum_rel']);
            }
        }
        $data['width'] > 0 && $schema['width'] = $data['width'];
        $data['col_size'] > 1 && $schema['colSize'] = $data['col_size'];
        $data['filters'] && $schema['filters'] = true;
        $data['sorter'] && $schema['sorter'] = true;
        $data['ellipsis'] && $schema['ellipsis'] = true;
        $data['copyable'] && $schema['copyable'] = true;
        $data['hide_in_form'] && $schema['hideInForm'] = true;
        $data['hide_in_table'] && $schema['hideInTable'] = true;
        $data['hide_in_search'] && $schema['hideInSearch'] = true;
        $data['hide_in_descriptions'] && $schema['hideInDescriptions'] = true;
        // 合并组件属性
        !empty(json_decode($data['component_props'], true)) && $schema['fieldProps'] = array_merge($schema['fieldProps'] ?? [], json_decode($data['component_props'], true));
        return $schema;
    }
    public function getFormilySchemaAttr($value, $data)
    {
        $mapType = [
            'dateRange' => 'string[]',
            'dateTimeRange' => 'string[]',
            'timeRange' => 'string[]',
        ];
        $mapComponent = [
            'text' => 'Input',
            'select' => 'Select',
            'switch' => 'Switch',
            'digit' => 'NumberPicker',
            'money' => 'NumberPicker',
            'password' => 'Password',
            'treeSelect' => 'Select',
            'cascader' => 'Cascader',
            'textarea' => 'Input.TextArea',
            'code' => 'Input.TextArea',
            'jsonCode' => 'Input.TextArea',
            'radio' => 'Radio.Group',
            'checkbox' => 'Checkbox.Group',
            'rate' => 'Rate',
            'percent' => 'Slider',
            'progress' => 'Slider',
            'avatar' => 'CustomImageUpload',
            'image' => 'CustomImageUpload',
            //'color' => '',
            'date' => 'DatePicker',
            'dateTime' => 'DatePicker',
            'dateWeek' => 'DatePicker',
            'dateMonth' => 'DatePicker',
            'dateQuarter' => 'DatePicker',
            'dateYear' => 'DatePicker',
            'dateRange' => 'DatePicker.RangePicker',
            'dateTimeRange' => 'DatePicker.RangePicker',
            'time' => 'TimePicker',
            'timeRange' => 'TimePicker.RangePicker',
            //'second' => '',
            //'fromNow' => '',
            'customImages' => 'CustomImageUpload',
            'customAttachments' => 'CustomAttachmentUpload',
            'customRichText' => 'CustomRichText',
            //'customRelationPickup' => '',
        ];

        $schema = [
            'name' => $data['data_index'],
            'type' => $mapType[$data['value_type']] ?? 'string',
            'title' => $data['title'],
            'x-decorator' => 'FormItem',
            'x-component' => $mapComponent[$data['value_type']] ?? 'Input',
            'x-reactions' => json_decode($data['reactions']) ?: [],
            'x-validator' => json_decode($data['validator']) ?: [],
        ];
        is_array($schema['x-reactions']) || $schema['x-reactions'] = [$schema['x-reactions']];
        is_array($schema['x-validator']) || $schema['x-validator'] = [$schema['x-validator']];

        // 必填
        $data['required'] && $schema['required'] = true;
        // 默认值
        in_array($data['value_type'], ['textarea', 'code', 'jsonCode', 'customRichText']) && $schema['default'] = '';
        ($data['default_value'] != '' && $data['default_value'] != '[]' && $data['default_value'] != '{}') && $schema['default'] = is_numeric($data['default_value']) ? $data['default_value'] + 0 : $data['default_value'];
        // 关联
        if (!empty($data['value_enum_rel'])) {
            $schema['enum'] = [];
            $kvs = system_col_rel_kv($data['value_enum_rel']);
            foreach ($kvs as $k => $v) {
                $schema['enum'][] = ['value' => $k, 'label' => $v];
            }
            // 关联搜索
            if ($data['value_enum_rel'][0] == 'suggest') {
                $schema['x-component-props']['showSearch'] = true;
                $schema['x-component-props']['filterOption'] = false;
                $schema['x-reactions'][] = "{{useSuggestDataSource('" . $schema['name'] . "', '" . $data['value_enum_rel'][1] . "', fetchSuggestData)}}";
            }
        }
        if ($data['value_type'] == 'avatar' || $data['value_type'] == 'image') {
            $schema['x-component-props'] = [
                'multiple' => false,
                'maxCount' => 1,
            ];
        }
        if ($data['value_type'] == 'customImages' || $data['value_type'] == 'customAttachments') {
            $schema['x-component-props'] = [
                'multiple' => true,
                'maxCount' => 5,
            ];
        }
        // x-component处理
        if ($schema['x-component'] == 'Select') {
            $schema['x-component-props']['allowClear'] = $data['required'] ? false : true;
        }
        // 合并组件属性
        !empty(json_decode($data['component_props'], true)) && $schema['x-component-props'] = array_merge($schema['x-component-props'] ?? [], json_decode($data['component_props'], true));
        // 合并容器属性
        !empty(json_decode($data['decorator_props'], true)) && $schema['x-decorator-props'] = array_merge($schema['x-decorator-props'] ?? [], json_decode($data['decorator_props'], true));
        return $schema;
    }

    public function btable()
    {
        return $this->belongsTo(SystemTable::class, 'table_code', 'code');
    }
}
