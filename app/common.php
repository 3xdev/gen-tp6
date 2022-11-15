<?php

// 应用公共文件

if (!function_exists('is_sql_reserve_word')) {
    /**
     * 是否SQL保留字
     * @param  string   $string     字符串
     * @return bool
     */
    function is_sql_reserve_word($string)
    {
        return in_array($string, file('sql_reserve_word', FILE_USE_INCLUDE_PATH | FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    }
}

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

if (!function_exists('string_remove_suffix')) {
    /**
     * 字符串移除后缀
     * @param  string   $string     字符串
     * @param  string   $suffix     后缀
     * @return string
     */
    function string_remove_suffix($string, $suffix)
    {
        if (empty($suffix)) {
            return $string;
        }

        return substr($string, 0, strripos($string, $suffix) === (strlen($string) - strlen($suffix)) ? strlen($string) - strlen($suffix) : strlen($string));
    }
}

if (!function_exists('format_datetime_range')) {
    /**
     * 格式化日期时间区间
     * @param  string|array     $range      日期时间区间
     * @return array
     */
    function format_datetime_range($range)
    {
        if (empty($range)) {
            return [];
        }

        is_array($range) || $range = [$range, $range];
        count($range) === 1 && $range[] = $range[0];
        list($start, $end) = $range;

        is_string($end) && \DateTime::createFromFormat('Y-m-d', $end) && $end .= ' 23:59:59';
        return [$start, $end];
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

if (!function_exists('system_dict')) {
    /**
     * 获取系统字典
     * system_dict('gender') => ['m' => '男', 'f' => '女']
     * system_dict('product_spu_status') => [-1 => '已下架', 0 => '未上架', 1 => '已上架']
     * @param  string   $key_   字典代码
     * @return array
     */
    function system_dict($key_)
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
     * system_col_rel_kv(['dict', 'gender']) => [1 => '李宁', 2 => '安踏', 3 => '七匹狼']
     * system_col_rel_kv(['table', 'product_brand']) => [1 => '李宁', 2 => '安踏', 3 => '七匹狼']
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
                $kvs = system_dict($rel[1]);
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

if (!function_exists('kv2data')) {
    /**
     * KeyValue转数据
     * kv2data(['get' => '读取'], 'value', 'label') => [['value' => 'get', 'label' => '读取']]
     * @param  array    $kv         KeyValue数组
     * @param  string   $map_key    key对应参数名
     * @param  string   $map_value  value对应参数名
     * @return array
     */
    function kv2data($kv, $map_key, $map_value)
    {
        $data = [];
        foreach ($kv as $k => $v) {
            $data[] = [$map_key => $k, $map_value => $v];
        }
        return $data;
    }
}

if (!function_exists('value2string')) {
    /**
     * 值转可读字符串
     * value2string(['角色一', '角色二']) => '角色一,角色二'
     * value2string('get', ['get' => '读取', 'post' => '新建']) => '读取'
     * value2string(['get', 'post'], ['get' => '读取', 'post' => '新建']) => '读取,新建'
     * @param  mixed    $value      value值
     * @param  array    $enum       枚举数组
     * @return string
     */
    function value2string($value, $enum = [])
    {
        if ($value === '') {
            return '';
        }
        if (!is_array($value)) {
            return $enum[$value] ?? $value;
        }

        foreach ($value as $val) {
            $array[] = $enum[$val] ?? $val;
        }
        return implode(',', array_unique($array));
    }
}

if (!function_exists('pt_search4col')) {
    /**
     * ProTable中search参数处理(取自列名数组)
     * @param  array   $cols     列名数组
     * @return array
     */
    function pt_search4col($cols)
    {
        return array_unique(array_map(fn($col) => preg_replace('/^(\w+)[\.\[].*$/', '${1}', $col), $cols));
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
