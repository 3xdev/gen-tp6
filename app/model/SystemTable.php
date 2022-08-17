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
            switch ($option['type']) {
                case 'add':
                    $kv['create'] = '新建';
                    break;
                case 'edit':
                    $kv['update'] = '编辑';
                    break;
                case 'delete':
                case 'bdelete':
                    $kv['delete'] = '删除';
                    break;
                case 'view':
                case 'export':
                    break;
                default:
                    $kv[strtolower($option['key'])] = $option['title'];
            }
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
        $schema['columns'] = $this->cols->column('pro_schema');
        $schema['options'] = [
            'columns' => [],
            'toolbar' => [],
            'batch' => [],
        ];
        $options = $this->options->visible(['group', 'type', 'key', 'title', 'path', 'body'])->append(['request'])->toArray();
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
