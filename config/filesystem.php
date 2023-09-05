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
            'type'      => 'qiniu',
            'accessKey' => env('qiniu.access_key'),
            'secretKey' => env('qiniu.secret_key'),
            'bucket'    => env('qiniu.bucket'),
            'domain'    => env('qiniu.domain'),
        ],
        // 阿里云OSS
        'aliyun'  => [
            'type'          => 'aliyun',
            'access_id'     => env('aliyun.access_key_id'),
            'access_secret' => env('aliyun.access_key_secret'),
            'endpoint'      => env('aliyun.oss_endpoint'),
            'bucket'        => env('aliyun.oss_bucket'),
            'isCName'       => false,
        ],
        // 更多的磁盘配置信息
    ],
];
