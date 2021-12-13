<?php

namespace app\model;

use think\facade\Cache;

/**
 * 字典模型
 */
class Dict extends Base
{
    // 设置主键
    protected $pk = 'key_';

    public const CACHE_PREFIX = 'dict:';

    // 获取缓存
    public static function fetchCache($key_)
    {
        $cache = Cache::get(self::CACHE_PREFIX . $key_);
        return is_null($cache) ? self::refreshCache($key_) : $cache;
    }

    // 刷新缓存
    public static function refreshCache($key_ = null)
    {
        if (is_null($key_)) {
            self::select()->each(function ($model) {
                Cache::set(self::CACHE_PREFIX . $model->key_, $model->items->visible(['key_', 'label'])->toArray());
            });
            return true;
        }

        $model = self::find($key_);
        $cache = $model ? $model->items->visible(['key_', 'label'])->toArray() : [];
        Cache::set(self::CACHE_PREFIX . $key_, $cache);
        return $cache;
    }

    // 刷新缓存
    public static function removeCache($key_)
    {
        return Cache::delete(self::CACHE_PREFIX . $key_);
        ;
    }

    // 条目
    public function items()
    {
        return $this->hasMany(DictItem::class, 'dict_key')->order('sort_');
    }
}
