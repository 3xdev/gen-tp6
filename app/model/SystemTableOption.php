<?php

namespace app\model;

use think\model\concern\SoftDelete;

class SystemTableOption extends Base
{
    use SoftDelete;

    public function setActionAttr($value, $data)
    {
        if (!empty($value)) {
            return $value;
        }
        switch ($data['type']) {
            case 'add':
                $value = 'create';
                break;
            case 'edit':
                $value = 'update';
                break;
            case 'delete':
            case 'bdelete':
                $value = 'delete';
                break;
            default:
                break;
        }
        return $value;
    }

    public function getRequestAttr($value, $data)
    {
        if (!in_array($data['type'], ['modal', 'request'])) {
            return [];
        }

        $path_crud = '/api/admin/crud/' . $this->btable->code;
        $map_crud = [
            'create' => ['method' => 'post', 'url' => $path_crud],
            'update' => ['method' => 'put', 'url' => $path_crud . '/{{ids}}'],
            'delete' => ['method' => 'delete', 'url' => $path_crud . '/{{ids}}'],
        ];
        if (isset($map_crud[$data['action']])) {
            return $map_crud[$data['action']];
        }

        $path_rest = '/api/admin/rest/' . $this->btable->code;
        return preg_match('/^(post|put|delete)(\w+)$/', $data['action'], $matches) ? [
            'method' => $matches[1],
            'url' => $path_rest . '/' . strtolower($matches[2]) . '/{{ids}}'
        ] : [];
    }

    public function btable()
    {
        return $this->belongsTo(SystemTable::class, 'table_code', 'code');
    }
}
