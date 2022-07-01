<?php

namespace app\model;

/**
 * 字典条目模型
 */
class SystemDictItem extends Base
{
    // 字典
    public function dict()
    {
        return $this->belongsTo(SystemDict::class, 'dict_key', 'key_');
    }
}
