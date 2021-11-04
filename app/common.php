<?php

// 应用公共文件

if (!function_exists('pt_filter2where')) {
    /**
     * ProTable中filter转化为查询数组
     * @param  string   $filter     filter值
     * @return array
     */
    function pt_filter2where($filter)
    {
        $map = [];
        $array = json_decode($filter, true);
        array_walk($array, function ($val, $key) use (&$map) {

            is_array($val) && $map[] = [$key, 'in', $val];
        });
        return $map;
    }
}

if (!function_exists('pt_sorter2order')) {
    /**
     * ProTable的sorter转化为排序数组
     * @param  string   $sorter     sorter值
     * @return array
     */
    function pt_sorter2order($sorter)
    {
        return array_map(function ($val) {
            return preg_replace('/end$/', '', $val);
        }, json_decode($sorter, true));
    }
}

if (!function_exists('common_response')) {
    /**
     * 通用的响应生成
     * @param  mixed $data 输出数据
     * @param  int $code 状态码
     * @return \think\Response
     */
    function common_response($data, $code = 200)
    {
        return \think\Response::create(request()->header('X-Exception-Return') == 'body' ? [
            'code'      => $code == 200 ? 0 : $code,
            'message'   => is_string($data) ? $data : '',
            'data'      => is_string($data) ? [] : $data,
        ] : (is_string($data) ? ['message' => $data] : $data), 'json', request()->header('X-Exception-Return') == 'body' ? 200 : $code);
    }
}
