<?php

return [
    // 默认磁盘
    'default' => env('filesystem.driver', 'local'),
    // 磁盘列表
    'disks'   => [
        'local'  => [
            'type' => 'local',
            'root' => app()->getRuntimePath() . 'storage',
        ],
        'public' => [
            // 磁盘类型
            'type'       => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath() . 'public/storage',
            // 磁盘路径对应的外部URL路径
            'url'        => '/storage',
            // 可见性
            'visibility' => 'public',
        ],
        // 七牛云存储
        'qiniu'  => [
            'type'          => 'qiniu',
            'accessKey'    => env('filesystem.qiniu_access_key'),
            'secretKey'    => env('filesystem.qiniu_secret_key'),
            'bucket'        => env('filesystem.qiniu_bucket'),
            'domain'       => env('filesystem.qiniu_domain'),
        ],
        // 更多的磁盘配置信息
    ],
];
