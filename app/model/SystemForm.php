<?php

namespace app\model;

use think\model\concern\SoftDelete;

/**
 * 系统表单模型
 */
class SystemForm extends Base
{
    use SoftDelete;

    protected $pk = 'code';

    // 设置json类型字段
    protected $json = ['schema'];
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

    public function setSchemaStringAttr($value)
    {
        is_object(json_decode($value)) && $this->set('schema', json_decode($value, true));
    }
}
