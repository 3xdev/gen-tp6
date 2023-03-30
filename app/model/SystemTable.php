<?php

namespace app\model;

use think\model\concern\SoftDelete;

class SystemTable extends Base
{
    use SoftDelete;

    // 设置json类型字段
    protected $json = ['props'];
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

    // 角色权限：表格关联操作
    public function getActionsAttr($value, $data)
    {
        $kv = [
            'get' => '读取'
        ];
        foreach ($this->options as $option) {
            if (in_array($option['type'], ['view', 'export'])) {
                continue;
            }
            $kv[$option['action']] = isset($kv[$option['action']]) ? $kv[$option['action']] . '/' . $option['title'] : $option['title'];
        }

        return kv2data($kv, 'value', 'label');
    }

    public function setPropsStringAttr($value)
    {
        is_object(json_decode($value)) && $this->set('props', json_decode($value, true));
    }

    // CRUD 列表展示字段
    public function getCrudIndexColsAttr($value, $data)
    {
        return $this->cols->filter(fn($col) => empty($col->hide_in_table) || !in_array($col->value_type, ['customRichText', 'textarea', 'code', 'jsonCode']))->column('data_index');
    }
    // CRUD 读取展示字段
    public function getCrudReadColsAttr($value, $data)
    {
        return $this->cols->column('data_index');
    }

    public function getProComponentsSchemaAttr($value, $data)
    {
        $schema = $data['props'];
        $schema['columns'] = $this->cols->column('pro_components_schema');
        $schema['options'] = [
            'columns' => [],
            'toolbar' => [],
            'batch' => [],
        ];
        $options = $this->options->visible(['group', 'type', 'action', 'title', 'target', 'body'])->append(['request'])->toArray();
        foreach ($options as $option) {
            $option['body'] = json_decode($option['body']) ?: [];
            isset($schema['options'][$option['group']]) && $schema['options'][$option['group']][] = $option;
        }
        return $schema;
    }

    public function getFormilySchemaAttr($value, $data)
    {
        return array2map($this->cols->where('hide_in_form', 0)->column('formily_schema'), 'name');
    }

    public function cols()
    {
        return $this->hasMany(SystemTableCol::class, 'table_code', 'code')->order('sort');
    }

    public function options()
    {
        return $this->hasMany(SystemTableOption::class, 'table_code', 'code')->order('sort');
    }
}
