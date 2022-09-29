<?php

namespace app\model;

use think\facade\Db;
use think\Model;

class Base extends Model
{
    // 系统表格列操作符类型映射
    public const TABLE_COL_OP_IN = 'in';
    public const TABLE_COL_OP_LIKE = 'like';
    public const TABLE_COL_OP_BETWEEN = 'between';
    public const TABLE_COL_OP_BETWEEN_TIME = 'between time';
    public const TABLE_COL_OP_MAP = [
        'radio' => self::TABLE_COL_OP_IN,
        'select' => self::TABLE_COL_OP_IN,
        'checkbox' => self::TABLE_COL_OP_IN,
        'text' => self::TABLE_COL_OP_LIKE,
        'textarea' => self::TABLE_COL_OP_LIKE,
        'code' => self::TABLE_COL_OP_LIKE,
        'jsonCode' => self::TABLE_COL_OP_LIKE,
        'customRichText' => self::TABLE_COL_OP_LIKE,
        'digitRange' => self::TABLE_COL_OP_BETWEEN,
        'dateRange' => self::TABLE_COL_OP_BETWEEN_TIME,
        'dateTimeRange' => self::TABLE_COL_OP_BETWEEN_TIME
    ];

    // 软删除字段的默认值设为0
    protected $defaultSoftDelete = 0;

    // 关联系统表格
    public $systemTable = null;

    // 关键字搜索主键字段
    public $keyword_fields = ['name'];
    // 关键字搜索主键字段
    public $keyword_pk = 'id';
    // 关键字搜索器
    public function searchKeywordAttr($query, $value, $data)
    {
        if (!empty($value)) {
            if (is_numeric($value)) {
                $query->where(function ($sq) use ($value) {
                    $sq->whereOr([[implode('|', $this->keyword_fields), 'like', '%' . $value . '%'], [$this->keyword_pk, '=', $value]]);
                });
            } else {
                $query->where(implode('|', $this->keyword_fields), 'like', '%' . $value . '%');
            }
        }
    }
    // ProTable的filter搜索器
    public function searchFilterAttr($query, $value, $data)
    {
        $query->where(pt_filter2where($value));
    }
    // ProTable的sorter搜索器
    public function searchSorterAttr($query, $value, $data)
    {
        $query->order(pt_sorter2order($value) ?: [$this->pk => 'desc']);
    }

    // 名称搜索器
    public function searchNameAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->whereLike('name', '%' . $value . '%');
        }
    }
    // 标题搜索器
    public function searchTitleAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->whereLike('title', '%' . $value . '%');
        }
    }
    // 创建时间搜索器
    public function searchCreateTimeAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->whereBetweenTime('create_time', $value[0], $value[1] . ' 23:59:59');
        }
    }

    /**
     * 是否表字段(支持查关联模型表字段)
     * @access  public
     * @param   array|string    $field  表字段名，支持数组形式的关联模型表字段('user.profile.email' 或 ['user', 'profile', 'email'])
     * @return  array
     */
    public function isTableField($field)
    {
        if (empty($field)) {
            return false;
        }

        $arr = is_array($field) ? $field : explode('.', $field);
        if (count($arr) === 1) {
            return in_array($arr[0], $this->db()->getTableFields());
        }

        $relation = $this->isRelationAttr(array_shift($arr));
        return $relation ? $this->$relation()->getModel()->isTableField($arr) : false;
    }

    /**
     * 解析查询字段(支持关联模型)
     * @access  public
     * @param   string          $field      查询字段名
     * @param   array|string    $value      查询值('哥' 或 ['no' => 'ET101', 'title' => 'XXX'])
     * @return  array
     */
    public function parseSearch($field, $value, $aliasMap = [])
    {
        if (is_array($value) && !empty(array_diff_key($value, array_values($value)))) {
            // 关联查询
            $map = [];
            foreach ($value as $key => $val) {
                $map = array_merge($map, $this->parseSearch($field . '.' . $key, $val, $aliasMap));
            }
            return $map;
        } else {
            return $this->parseSearchItem($field, $value, $aliasMap);
        }
    }
    public function parseSearchItem($key, $value, $aliasMap)
    {
        if (empty($value) && !($value === 0 || $value === '0')) {
            return [];
        }

        $op = '=';
        if ($this->systemTable) {
            $col = $this->systemTable->cols->where('data_index', $key)->shift();
            if ($col) {
                $op = self::TABLE_COL_OP_MAP[$col->value_type] ?? '=';
            }
        }

        $keyArray = explode('.', $key);
        $field = array_pop($keyArray);
        empty($keyArray) || $field = ($aliasMap[implode('.', $keyArray)] ?? array_pop($keyArray)) . '.' . $field;
        is_array($value) && $field = string_remove_suffix($field, '[]');
        $condition = $value;
        $op === self::TABLE_COL_OP_IN && $condition = is_array($value) ? $value : [$value];
        $op === self::TABLE_COL_OP_LIKE && $condition = '%' . $value . '%';
        $op === self::TABLE_COL_OP_BETWEEN_TIME && $condition = format_datetime_range($value);

        return [
            [$field, $op, $condition]
        ];
    }

    /**
     * 获取记录键值对
     * @access  public
     * @return  array
     */
    public static function fetchKeyValue()
    {
        $objs = self::select();

        $data = [];
        foreach ($objs as $obj) {
            $data[$obj[$obj->keyword_pk]] = $obj[$obj->keyword_fields[0]];
        }
        return $data;
    }

    // 获取附带操作事件
    public const FETCH_WITH_EVENT_INSERT = 'insert';
    public const FETCH_WITH_EVENT_UPDATE = 'update';

    /**
     * 获取(含自动新增及更新)
     * @access  public
     * @param   array   $map        条件数据
     * @param   array   $update     更新数据
     * @param   array   $create     新建数据
     * @return  Base
     */
    public static function fetchWithSave(array $map, array $update = [], array $create = [])
    {
        $model = self::where($map)->findOrEmpty();
        if ($model->isEmpty()) {
            // 新增
            try {
                $model = self::create(array_merge($update, $create, $map));
                $model->fetch_with_event = self::FETCH_WITH_EVENT_INSERT;
            } catch (\Exception $e) {
                // 并发情况下重复数据新增时，重新查询(1062 Duplicate entry)
                $model = self::where($map)->findOrEmpty();
                !$model->isEmpty() && !empty($update) && $model->save($update) && $model->fetch_with_event = self::FETCH_WITH_EVENT_UPDATE;
            }
        } else {
            // 更新
            !empty($update) && $model->save($update) && $model->fetch_with_event = self::FETCH_WITH_EVENT_UPDATE;
        }

        return $model;
    }


    // 错误信息
    protected static $errorMsg;
    public const DEFAULT_ERROR_MSG = '操作失败！';

    /**
     * 设置错误信息
     * @param string $errorMsg
     * @param boolean $rollback
     * @return bool
     */
    protected static function setErrorMsg($errorMsg = self::DEFAULT_ERROR_MSG, $rollback = false)
    {
        if ($rollback) {
            self::rollbackTrans();
        }

        return self::$errorMsg = $errorMsg;
    }

    /**
     * 读取错误信息
     * @param string $defaultMsg
     * @return string
     */
    public static function getErrorMsg($defaultMsg = self::DEFAULT_ERROR_MSG)
    {
        return !empty(self::$errorMsg) ? self::$errorMsg : $defaultMsg;
    }

    /**
     * 开启事务
     */
    public static function beginTrans()
    {
        Db::startTrans();
    }

    /**
     * 提交事务
     */
    public static function commitTrans()
    {
        Db::commit();
    }

    /**
     * 关闭事务
     */
    public static function rollbackTrans()
    {
        Db::rollback();
    }

    /**
     * 根据结果提交滚回事务
     * @param $res
     */
    public static function checkTrans($res)
    {
        if ($res) {
            self::commitTrans();
        } else {
            self::rollbackTrans();
        }
    }
}
