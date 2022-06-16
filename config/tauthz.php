<?php

return [
    /*
     *Default Tauthz enforcer
     */
    'default' => 'basic',

    'log' => [
        // changes whether Lauthz will log messages to the Logger.
        'enabled' => false,
        // Casbin Logger, Supported: \Psr\Log\LoggerInterface|string
        'logger' => 'log',
    ],

    'enforcers' => [
        'basic' => [
            // Model设置
            'model' => [
                'config_type' => 'file',
                'config_file_path' => config_path() . 'tauthz-rbac-model.conf',
                'config_text' => '',
            ],
            // 适配器设置
            'adapter' => tauthz\adapter\DatabaseAdapter::class,
            // 数据库设置
            'database' => [
                'connection' => '',
                'rules_table' => '',
                'rules_name' => 'system_policy',
            ],
        ],
    ],
];
