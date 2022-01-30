<?php

namespace app\service;

use think\facade\Cache;
use Godruoyi\Snowflake\Snowflake;
use Godruoyi\Snowflake\RedisSequenceResolver;
use Godruoyi\Snowflake\RandomSequenceResolver;

class SnowflakeService extends \think\Service
{
    /**
     * 注册服务
     *
     * @return mixed
     */
    public function register()
    {
        $this->app->bind('snowflake', Snowflake::class);
    }

    /**
     * 执行服务
     *
     * @return mixed
     */
    public function boot()
    {
        $this->app->snowflake
            ->setStartTimeStamp(strtotime('2020-01-01') * 1000)
            ->setSequenceResolver(config('cache.default') == 'redis' ? new RedisSequenceResolver(Cache::store('redis')->handler()) : new RandomSequenceResolver());
    }
}
