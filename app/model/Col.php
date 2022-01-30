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

    public function btable()
    {
        return $this->belongsTo(Table::class, 'table_code');
    }
}
