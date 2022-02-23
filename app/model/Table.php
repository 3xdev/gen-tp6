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

    // CRUD 列表展示字段
    public function getCrudIndexColsAttr($value, $data)
    {
        $result = [];
        $cols = $this->cols->filter(fn($col) => empty($col->hide_in_table) || empty($col->hide_in_descriptions))->map(fn($col) => string_dot_array($col->data_index))->toArray();
        foreach ($cols as $col) {
            $result = array_merge_recursive($result, $col);
        }
        return $result;
    }
    // CRUD 读取展示字段
    public function getCrudReadColsAttr($value, $data)
    {
        $result = [];
        $cols = $this->cols->filter(fn($col) => empty($col->hide_in_table) || empty($col->hide_in_form) || empty($col->hide_in_descriptions))->map(fn($col) => string_dot_array($col->data_index))->toArray();
        foreach ($cols as $col) {
            $result = array_merge_recursive($result, $col);
        }
        return $result;
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
