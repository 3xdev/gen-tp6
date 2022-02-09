<?php

namespace app\model;

class Col extends Base
{
    public function searchTitleAttr($query, $value, $data)
    {
        $value && $query->where('title', 'like', '%' . $value . '%');
    }
    public function searchDataIndexAttr($query, $value, $data)
    {
        $value && $query->where('data_index', 'like', '%' . $value . '%');
    }

    public function getProSchemaAttr($value, $data)
    {
        $schema = [
            'title' => $data['title'],
            'dataIndex' => $data['data_index'],
        ];
        !empty($data['tip']) && $schema['tooltip'] = $data['tip'];
        !empty($data['value_type']) && $schema['valueType'] = $data['value_type'];
        !empty($data['value_enum_dict_key']) && $schema['valueEnum'] = system_dict($data['value_enum_dict_key']);
        $data['col_size'] > 1 && $schema['colSize'] = $data['col_size'];
        $data['filters'] && $schema['filters'] = true;
        $data['ellipsis'] && $schema['ellipsis'] = true;
        $data['copyable'] && $schema['copyable'] = true;
        $data['hide_in_form'] && $schema['hideInForm'] = true;
        $data['hide_in_table'] && $schema['hideInTable'] = true;
        $data['hide_in_search'] && $schema['hideInSearch'] = true;
        $data['hide_in_descriptions'] && $schema['hideInDescriptions'] = true;

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
            'select' => 'Select',
            'textarea' => 'Input.TextArea',
            'password' => 'Password',
            'money' => 'NumberPicker',
            'dateRange' => 'DatePicker.RangePicker',
            'dateTimeRange' => 'DatePicker.RangePicker',
            'timeRange' => 'TimePicker.RangePicker',
        ];

        $schema = [
            'name' => $data['data_index'],
            'type' => $mapType[$data['value_type']] ?? 'string',
            'title' => $data['title'],
            //'required' => true,
            'x-decorator' => 'FormItem',
            'x-component' => $mapComponent[$data['value_type']] ?? 'Input',
        ];
        !empty($data['value_enum_dict_key']) && $schema['enum'] = array_map(
            fn($key, $value) => ['value' => $key, 'label' => $value],
            array_keys(system_dict($data['value_enum_dict_key'])),
            array_values(system_dict($data['value_enum_dict_key']))
        );
        return $schema;
    }

    public function btable()
    {
        return $this->belongsTo(Table::class, 'table_code');
    }
}
