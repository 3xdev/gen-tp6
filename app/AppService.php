<?php

declare(strict_types=1);

namespace app;

use think\Service;
use think\Validate;

/**
 * 应用服务类
 */
class AppService extends Service
{
    public function register()
    {
        // 服务注册
        // 全局注册验证规则
        Validate::maker(function ($validate) {
            $validate->extend('checkModelUnique', function ($value, $rule, array $data = [], string $field = '') {
                if (!isset($data[$field])) {
                    // 不存在不检查
                    return true;
                }
                is_string($rule) && $rule = explode(',', $rule);

                $model = new $rule[0]();
                $map = [];
                $fields = $rule[1] ?? $field;
                if (strpos($fields, '^')) {
                    // 支持多个字段验证
                    foreach (explode('^', $fields) as $key) {
                        if (isset($data[$key])) {
                            $map[] = [$key, '=', $data[$key]];
                        }
                    }
                } elseif (isset($data[$fields])) {
                    $map[] = [$fields, '=', $data[$fields]];
                }

                $pk = $model->getPk();
                isset($data[$pk]) && $map[] = [$pk, '<>', $data[$pk]];

                if ($model->where($map)->count()) {
                    return false;
                }
                return true;
            }, ':attribute has exists');
        });
    }

    public function boot()
    {
        // 服务启动
    }
}
