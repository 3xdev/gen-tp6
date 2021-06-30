<?php
namespace app\middleware;

/**
 * 跨域请求支持
 */
class AllowCrossDomain extends \think\middleware\AllowCrossDomain
{
    protected $header = [
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age'           => 1800,
        'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers'     => '*',
    ];
}
