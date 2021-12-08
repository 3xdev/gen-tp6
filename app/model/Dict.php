<?php

namespace app\model;

/**
 * 字典模型
 */
class Dict extends Base
{
    // 设置完整数据表名及主键
    protected $table = 'sys_dict';
    protected $pk = 'key_';

    // 条目
    public function items()
    {
        return $this->hasMany(DictItem::class, 'dict_key')->order('sort_');
    }
}
