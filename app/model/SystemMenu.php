<?php

namespace app\model;

use think\model\concern\SoftDelete;

class SystemMenu extends Base
{
    use SoftDelete;

    public function searchNameAttr($query, $value, $data)
    {
        $value && $query->where('name', 'like', '%' . $value . '%');
    }
    public function searchPathAttr($query, $value, $data)
    {
        $value && $query->where('path', 'like', '%' . $value . '%');
    }
}
