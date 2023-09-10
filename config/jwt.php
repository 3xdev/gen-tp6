<?php

return [
    // 加密算法
    'algo'        => env('JWT_ALGO', 'HS256'),
    'secret'      => env('JWT_SECRET'),
    // Time To Live(秒)
    'ttl'         => env('JWT_TTL', 1800),
    // Refresh Time To Live(分)
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),
    // Token获取方式，靠前优先
    'token_mode'    => ['header', 'param'],
    // 黑名单宽限期(秒)
    'blacklist_grace_period' => env('BLACKLIST_GRACE_PERIOD', 10),
];
