<?php

namespace app\model;

use think\facade\Cache;
use think\model\concern\SoftDelete;

/**
 * 配置模型
 */
class SystemConfig extends Base
{
    use SoftDelete;

    public const CACHE_PREFIX = 'config:';

    // 关键字搜索主键字段
    public $keyword_fields = ['title', 'code', 'value'];
    public function searchTitleAttr($query, $value, $data)
    {
        $value && $query->where('title', 'like', '%' . $value . '%');
    }
    public function searchCodeAttr($query, $value, $data)
    {
        $value && $query->where('code', 'like', '%' . $value . '%');
    }
    public function searchValueAttr($query, $value, $data)
    {
        $value && $query->where('value', 'like', '%' . $value . '%');
    }

    // 值字段获取器及修改器
    public function getValueAttr($value)
    {
        return unserialize($value);
    }
    public function setValueAttr($value)
    {
        return serialize($value);
    }

    // Schema获取器
    public function getSchemaAttr($value, $data)
    {
        $mapType = [
            'dateRange' => 'string[]',
            'dateTimeRange' => 'string[]',
            'timeRange' => 'string[]',
        ];
        $mapComponent = [
            'text' => 'Input',
            'select' => 'Select',
            'switch' => 'Switch',
            'digit' => 'NumberPicker',
            'money' => 'NumberPicker',
            'password' => 'Password',
            'treeSelect' => 'Select',
            'cascader' => 'Cascader',
            'textarea' => 'Input.TextArea',
            'code' => 'Input.TextArea',
            'jsonCode' => 'Input.TextArea',
            'radio' => 'Radio.Group',
            'checkbox' => 'Checkbox.Group',
            'rate' => 'Rate',
            'percent' => 'Slider',
            'progress' => 'Slider',
            'avatar' => 'CustomImageUpload',
            'image' => 'CustomImageUpload',
            //'color' => '',
            'date' => 'DatePicker',
            'dateTime' => 'DatePicker',
            'dateWeek' => 'DatePicker',
            'dateMonth' => 'DatePicker',
            'dateQuarter' => 'DatePicker',
            'dateYear' => 'DatePicker',
            'dateRange' => 'DatePicker.RangePicker',
            'dateTimeRange' => 'DatePicker.RangePicker',
            'time' => 'TimePicker',
            'timeRange' => 'TimePicker.RangePicker',
            //'second' => '',
            //'fromNow' => '',
            'customImages' => 'CustomImageUpload',
            'customRichText' => 'CustomRichText',
            //'customRelationPickup' => '',
        ];

        $schema = [
            'name' => $data['code'],
            'type' => $mapType[$data['component']] ?? 'string',
            'title' => $data['title'],
            'x-decorator' => 'FormItem',
            'x-component' => $mapComponent[$data['component']] ?? 'Input',
        ];
        // 必填
        $schema['required'] = true;
        // 默认值
        $schema['default'] = system_config($data['code']);
        // 关联字典
        if (!empty($data['dict_key'])) {
            $schema['enum'] = [];
            $kvs = system_dict($data['dict_key']);
            foreach ($kvs as $k => $v) {
                $schema['enum'][] = ['value' => $k, 'label' => $v];
            }
        }
        if ($data['component'] == 'dateTime') {
            $schema['x-component-props'] = [
                'showTime' => true
            ];
        }
        if ($data['component'] == 'dateWeek') {
            $schema['x-component-props'] = [
                'picker' => 'week'
            ];
        }
        if ($data['component'] == 'dateMonth') {
            $schema['x-component-props'] = [
                'picker' => 'month'
            ];
        }
        if ($data['component'] == 'dateQuarter') {
            $schema['x-component-props'] = [
                'picker' => 'quarter'
            ];
        }
        if ($data['component'] == 'dateYear') {
            $schema['x-component-props'] = [
                'picker' => 'year'
            ];
        }
        if ($data['component'] == 'avatar' || $data['component'] == 'image') {
            $schema['x-component-props'] = [
                'multiple' => false,
                'maxCount' => 1,
            ];
        }
        if ($data['component'] == 'customImages') {
            $schema['x-component-props'] = [
                'multiple' => true,
                'maxCount' => 5,
            ];
        }
        // x-component处理
        if ($schema['x-component'] == 'Select') {
            $schema['x-component-props']['allowClear'] = false;
        }

        return $schema;
    }

    // 获取缓存
    public static function fetchCache($code)
    {
        $cache = Cache::get(self::CACHE_PREFIX . $code);
        return is_null($cache) ? self::refreshCache($code) : $cache;
    }

    // 刷新缓存
    public static function refreshCache($code = null)
    {
        if (is_null($code)) {
            self::select()->each(function ($model) {
                Cache::set(self::CACHE_PREFIX . $model->code, $model->value);
            });
            return true;
        }

        $model = self::where('code', $code)->find();
        $cache = $model ? $model->value : '';
        Cache::set(self::CACHE_PREFIX . $code, $cache);
        return $cache;
    }

    // 刷新缓存
    public static function removeCache($code)
    {
        return Cache::delete(self::CACHE_PREFIX . $code);
        ;
    }

    // 模型事件
    public static function onAfterWrite($config)
    {
        self::refreshCache($config->code);
    }
    public static function onAfterDelete($config)
    {
        self::removeCache($config->code);
    }

    // 关联字典
    public function dict()
    {
        return $this->belongsTo(SystemDict::class, 'dict_key');
    }
}
