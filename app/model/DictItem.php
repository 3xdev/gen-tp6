<?php

namespace app\model;

/**
 * 字典条目模型
 */
class DictItem extends Base
{
    // 设置主键
    protected $pk = ['dict_key', 'key_'];

    // 字典
    public function dict()
    {
        return $this->belongsTo(Dict::class, 'dict_key');
    }
}
