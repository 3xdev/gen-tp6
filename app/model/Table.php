<?php

namespace app\model;

class Table extends Base
{
    // 设置主键
    protected $pk = 'code';

    // 设置json类型字段
    protected $json = ['props', 'options'];
    // 设置json数据返回数组
    protected $jsonAssoc = true;

    public function searchNameAttr($query, $value, $data)
    {
        $value && $query->where('name', 'like', '%' . $value . '%');
    }
    public function searchCodeAttr($query, $value, $data)
    {
        $value && $query->where('code', 'like', '%' . $value . '%');
    }

    public function setPropsStringAttr($value)
    {
        is_object(json_decode($value)) && $this->set('props', json_decode($value, true));
    }

    public function getProSchemaAttr($value, $data)
    {
        $schema = $data['props'];
        $schema['options'] = $data['options'];
        $schema['columns'] = $this->cols->column('pro_schema');

        return $schema;
    }

    public function getFormilySchemaAttr($value, $data)
    {
        return array2map($this->cols->where('hide_in_form', 0)->column('formily_schema'), 'name');
    }

    public function cols()
    {
        return $this->hasMany(Col::class, 'table_code')->order('sort');
    }
}
