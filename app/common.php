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

if (!function_exists('string_dot_array')) {
    /**
     * 层级字符串转层级数组
     * @param  string   $string     层级字符串
     * @param  string   $dot        分层字符串
     * @return array
     */
    function string_dot_array($string, $dot = '.')
    {
        $exploded = explode($dot, $string);
        $count = count($exploded);
        if ($count == 1) {
            return [$string];
        }

        $result = [$exploded[$count - 1]];
        for ($i = $count - 2; $i >= 0; $i--) {
            $result = [$exploded[$i] => $result];
        }
        return $result;
    }
}

if (!function_exists('string_remove_prefix')) {
    /**
     * 字符串移除前缀
     * @param  string   $string     字符串
     * @param  string   $prefix     前缀
     * @return string
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
     * system_config('logo') => 'http://'
     * system_config('login_retry') => '5'
     * @param  string   $code   配置编码
     * @return mixed
     */
    function system_config($code)
    {
        return \app\model\SystemConfig::fetchCache($code);
    }
}

if (!function_exists('system_dict_kv')) {
    /**
     * 获取系统字典
     * system_dict_kv('gender') => ['m' => '男', 'f' => '女']
     * system_dict_kv('product_spu_status') => [-1 => '已下架', 0 => '未上架', 1 => '已上架']
     * @param  string   $key_   字典代码
     * @return array
     */
    function system_dict_kv($key_)
    {
        return \app\model\SystemDict::fetchCache($key_);
    }
}

if (!function_exists('system_table_kv')) {
    /**
     * 获取系统表格的KeyValue
     * system_table_kv('product_brand') => [1 => '李宁', 2 => '安踏', 3 => '七匹狼']
     * @param  string   $table   表格代码
     * @return array
     */
    function system_table_kv($table)
    {
        $class = app()->parseClass('model', $table);

        return class_exists($class) ? app()->make($class)->fetchKeyValue() : [];
    }
}

if (!function_exists('system_col_rel_kv')) {
    /**
     * 获取系统表格列关联的KeyValue
     * system_col_rel_kv('dict:gender') => [1 => '李宁', 2 => '安踏', 3 => '七匹狼']
     * system_col_rel_kv('table:product_brand') => [1 => '李宁', 2 => '安踏', 3 => '七匹狼']
     * @param  string   $rel   关联代码
     * @return array
     */
    function system_col_rel_kv($rel)
    {
        if (empty($rel)) {
            return [];
        }

        $kvs = [];
        switch ($rel[0]) {
            case 'dict':
                $kvs = system_dict_kv($rel[1]);
                break;
            case 'table':
                $kvs = system_table_kv($rel[1]);
                break;
            default:
                break;
        }

        return $kvs;
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
