<?php

namespace app\service;

class Formily
{
    /**
     * 解析字段Schema
     */
    public function parseFieldSchema(string $name, string $title, string $type = 'text', array $props = [])
    {
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
            'formilyArrayItems' => 'ArrayItems',
            'formilyArrayTable' => 'ArrayTable',
            'customImages' => 'CustomImageUpload',
            'customAttachments' => 'CustomAttachmentUpload',
            'customRichText' => 'CustomRichText',
            'customRelationPickup' => 'CustomRelationPickup',
            'customRequestFetch' => 'CustomRequestFetch',
        ];

        $component = $mapComponent[$type] ?? 'Input';
        $componentProps = json_decode($props['component_props'] ?? '{}', true);
        $decoratorProps = json_decode($props['decorator_props'] ?? '{}', true);
        $schema = [
            'name'  => $name,
            'title' => $title,
            'type'  => in_array($component, ['DatePicker.RangePicker', 'TimePicker.RangePicker', 'ArrayItems', 'ArrayTable']) ? 'array' : 'string',
            'x-decorator'   => 'FormItem',
            'x-component'   => $component,
            'x-reactions'   => json_decode($props['reactions'] ?? '{}', true),
            'x-validator'   => json_decode($props['validator'] ?? '{}', true),
            'x-component-props'     => [],
        ];
        is_array($schema['x-reactions']) || $schema['x-reactions'] = empty($schema['x-reactions']) ? [] : [$schema['x-reactions']];
        is_array($schema['x-validator']) || $schema['x-validator'] = empty($schema['x-validator']) ? [] : [$schema['x-validator']];

        // 描述
        isset($props['tip']) && !empty($props['tip']) && $schema['description'] = $props['tip'];

        // 必填
        isset($props['required']) && !empty($props['required']) && $schema['required'] = true;

        // 默认值
        in_array($component, ['Input.TextArea', 'CustomRichText']) && $schema['default'] = '';
        isset($props['default_value']) && $props['default_value'] != '' && $schema['default'] = is_numeric($props['default_value']) ? $props['default_value'] + 0 : $props['default_value'];
        isset($props['default_value']) && $props['default_value'] == '[]' && $schema['default'] = [];

        // 关联
        if (isset($props['value_enum_rel']) && !empty($props['value_enum_rel'])) {
            $schema['type'] = 'array';
            $schema['enum'] = [];
            $kvs = system_col_rel_kv($props['value_enum_rel']);
            foreach ($kvs as $k => $v) {
                $schema['enum'][] = ['value' => $k, 'label' => $v];
            }
            // 关联搜索
            if ($component == 'Select' && $props['value_enum_rel'][0] == 'suggest') {
                $schema['x-component-props']['showSearch'] = true;
                $schema['x-component-props']['filterOption'] = false;
                $query = isset($componentProps['query']) ? json_encode($componentProps['query'], JSON_FORCE_OBJECT) : "{}";
                $schema['x-reactions'][] = "{{useSuggestDataSource('" . $schema['name'] . "', '" . $props['value_enum_rel'][1] . "', " . $query . ", fetchSuggestData)}}";
            }
            if ($component == 'CustomRelationPickup' && $props['value_enum_rel'][0] == 'suggest') {
                $schema['x-component-props']['table'] = $props['value_enum_rel'][1];
            }
        }

        // 类型处理
        if ($type == 'dateTime') {
            $schema['x-component-props']['showTime'] = true;
        }
        if ($type == 'dateWeek') {
            $schema['x-component-props']['picker'] = 'week';
        }
        if ($type == 'dateMonth') {
            $schema['x-component-props']['picker'] = 'month';
        }
        if ($type == 'dateQuarter') {
            $schema['x-component-props']['picker'] = 'quarter';
        }
        if ($type == 'dateYear') {
            $schema['x-component-props']['picker'] = 'year';
        }
        if ($type == 'avatar' || $type == 'image') {
            $schema['x-component-props']['multiple'] = false;
            $schema['x-component-props']['maxCount'] = 1;
        }
        if ($type == 'customImages' || $type == 'customAttachments') {
            $schema['x-component-props']['multiple'] = true;
            $schema['x-component-props']['maxCount'] = 5;
        }

        // 如果有设默认值且为数组，字段类型强制转为数组
        isset($schema['default']) && is_array($schema['default']) && $schema['type'] = 'array';

        // Array类组件处理
        if (in_array($component, ['ArrayItems', 'ArrayTable'])) {
            if (isset($componentProps['items'])) {
                $schema['items'] = $componentProps['items'];
                unset($componentProps['items']);
            }
            if (isset($componentProps['properties'])) {
                $schema['properties'] = $componentProps['properties'];
                unset($componentProps['properties']);
            }
        }

        // 合并组件属性
        empty($componentProps) || $schema['x-component-props'] = array_merge($schema['x-component-props'] ?? [], $componentProps);
        // 合并容器属性
        empty($decoratorProps) || $schema['x-decorator-props'] = array_merge($schema['x-decorator-props'] ?? [], $decoratorProps);
        // unset无意义
        if (empty($schema['description'])) {
            unset($schema['description']);
        }
        if (empty($schema['x-reactions'])) {
            unset($schema['x-reactions']);
        }
        if (empty($schema['x-validator'])) {
            unset($schema['x-validator']);
        }
        if (empty($schema['x-component-props'])) {
            unset($schema['x-component-props']);
        }
        return $schema;
    }
}
