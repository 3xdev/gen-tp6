<?php

namespace app\model;

class Menu extends Base
{
    public function searchNameAttr($query, $value, $data)
    {
        $value && $query->where('name', 'like', '%' . $value . '%');
    }
    public function searchPathAttr($query, $value, $data)
    {
        $value && $query->where('path', 'like', '%' . $value . '%');
    }
}
