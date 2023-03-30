<?php

namespace app\model;

class SystemTableCol extends Base
{
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
    // 值枚举
    public function getValueEnumAttr($value, $data)
    {
        if (empty($data['value_enum_rel'])) {
            return [];
        }
        return system_col_rel_kv($data['value_enum_rel']);
    }
    public function getProComponentsSchemaAttr($value, $data)
    {
        return app()->pro_components->parseFieldSchema($data['data_index'], $data['title'], $data['value_type'], $data);
    }
    public function getFormilySchemaAttr($value, $data)
    {
        return app()->formily->parseFieldSchema($data['data_index'], $data['title'], $data['value_type'], $data);
    }

    public function btable()
    {
        return $this->belongsTo(SystemTable::class, 'table_code', 'code');
    }
}
