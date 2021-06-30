<?php
// 应用公共文件
if (!function_exists('pt_filter2where')) {
    /**
     * ProTable中filter转化为查询数组
     * @access public
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
     * @access public
     * @param  string   $sorter     sorter值
     * @return array
     */
    function pt_sorter2order($sorter)
    {
        return array_map(function($val) {
            return preg_replace('/end$/','',$val);
        }, json_decode($sorter, true));
    }
}
