<?php

namespace app\model;

use think\model\concern\SoftDelete;

/**
 * 配置模型
 */
class Config extends Base
{
    use SoftDelete;

    // 关键字搜索主键字段
    protected $keyword_fields = ['code','title','value'];
    public function searchCodeAttr($query, $value, $data)
    {
        $value && $query->where('code', 'like', '%' . $value . '%');
    }
    public function searchTitleAttr($query, $value, $data)
    {
        $value && $query->where('title', 'like', '%' . $value . '%');
    }
    public function searchValueAttr($query, $value, $data)
    {
        $value && $query->where('value', 'like', '%' . $value . '%');
    }

    // 值字段的获取器及修改器
    public function getValueAttr($value)
    {
        return unserialize($value);
    }
    public function setValueAttr($value)
    {
        return serialize($value);
    }

    // 关联字典
    public function dict()
    {
        return $this->belongsTo(Dict::class, 'dict_key');
    }
}
