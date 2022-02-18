<?php

// 应用公共文件

if (!function_exists('name_class')) {
    /**
     * 类命名(大驼峰法)
     * @param  string   $string     字符串
     * @return string
     */
    function name_class($string)
    {
        return \think\helper\Str::studly(strtolower($string));
    }
}

if (!function_exists('name_relation')) {
    /**
     * 关联命名(驼峰法)
     * @param  string   $string     字符串
     * @param  bool     $many       多关联
     * @return string
     */
    function name_relation($string, $many = false)
    {
        return \think\helper\Str::camel(strtolower($string)) . ($many ? 's' : '');
    }
}

if (!function_exists('string_remove_prefix')) {
    /**
     * 字符串移除前缀
     * @param  string   $string     字符串
     * @param  string   $prefix     前缀
     * @return array
     */
    function string_remove_prefix($string, $prefix)
    {
        if (empty($prefix)) {
            return $string;
        }

        return substr($string, stripos($string, $prefix) === 0 ? strlen($prefix) : 0);
    }
}

if (!function_exists('array2map')) {
    /**
     * 数组转映射
     * @param  array    $array      数组
     * @param  string   $key        映射键
     * @return array
     */
    function array2map($array, $key)
    {
        $map = [];
        foreach ($array as $value) {
            $map[$value[$key]] = $value;
        }
        return $map;
    }
}

if (!function_exists('map_array_value')) {
    /**
     * 获取映射的数组对应值
     * @param  array    $map        映射
     * @param  array    $array      数组
     * @param  string   $value      映射值
     * @return mixed
     */
    function map_array_value($map, $array, $value)
    {
        $index = array_search($value, $map);
        return $index === false ? null : $array[$index];
    }
}

if (!function_exists('system_config')) {
    /**
     * 获取系统配置
     * @param  string   $code   配置编码
     * @return mixed
     */
    function system_config($code)
    {
        return \app\model\Config::fetchCache($code);
    }
}

if (!function_exists('system_dict')) {
    /**
     * 获取系统字典
     * @param  string   $key_   字典代码
     * @return array
     */
    function system_dict($key_)
    {
        return \app\model\Dict::fetchCache($key_);
    }
}

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
