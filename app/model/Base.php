<?php

namespace app\model;

use think\facade\Db;
use think\Model;

class Base extends Model
{
    // 软删除字段的默认值设为0
    protected $defaultSoftDelete = 0;

    // 关键字搜索主键字段
    public $keyword_fields = ['name'];
    // 关键字搜索主键字段
    public $keyword_pk = 'id';
    // 关键字搜索器
    public function searchKeywordAttr($query, $value, $data)
    {
        if (!empty($value)) {
            if (is_numeric($value)) {
                $query->whereOr(implode('|', $this->keyword_fields), 'like', '%' . $value . '%')->whereOr($this->keyword_pk, $value);
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
            $query->whereBetweenTime('create_time', $value[0], $value[1]);
        }
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
