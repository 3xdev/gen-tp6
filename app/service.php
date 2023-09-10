<?php

use app\AppService;
use app\service\SnowflakeService;
use app\service\FormilyService;
use app\service\ProComponentsService;

// 系统服务定义文件
// 服务在完成全局初始化之后执行
return [
    AppService::class,
    SnowflakeService::class,
    FormilyService::class,
    ProComponentsService::class,
    tauthz\TauthzService::class,
];
