<?php

namespace app\model;

class Table extends Base
{
    // 设置主键
    protected $pk = 'code';

    public function searchNameAttr($query, $value, $data)
    {
        $value && $query->where('name', 'like', '%' . $value . '%');
    }
    public function searchCodeAttr($query, $value, $data)
    {
        $value && $query->where('code', 'like', '%' . $value . '%');
    }

    public function getProSchemaAttr($value, $data)
    {
        $schema = json_decode($data['props'], true);
        $schema['columns'] = $this->cols->column('pro_schema');

        return $schema;
    }

    public function cols()
    {
        return $this->hasMany(Col::class, 'table_code')->order('sort');
    }
}
