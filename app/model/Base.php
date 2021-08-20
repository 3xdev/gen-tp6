<?php

namespace app\model;

use think\facade\Db;
use think\Model;

class Base extends Model
{
    // 软删除字段的默认值设为0
    protected $defaultSoftDelete = 0;
    // 错误信息
    protected static $errorMsg;
    // 关键字搜索主键字段
    protected $keyword_fields = ['name'];
    // 关键字搜索主键字段
    protected $keyword_pk = 'id';
    // 关键字搜索器
    public function searchKeywordAttr($query, $value, $data)
    {
        if (!empty($value)) {
            if (is_int($value)) {
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

    // 创建时间搜索器
    public function searchCreateTimeAttr($query, $value, $data)
    {
        if (!empty($value)) {
            $query->whereBetweenTime('create_time', $value[0], $value[1]);
        }
    }

    /**
     * 获取模型(自动新增及更新)
     * @access  public
     * @param   array   $map        条件数据
     * @param   array   $data       保存数据
     * @return  Model
     */
    public static function fetchWithSave(array $map, array $data = [])
    {
        $model = self::where($map)->findOrEmpty();
        if ($model->isEmpty()) {
        // 新增
            $model = self::create(array_merge($data, $map));
        } else {
        // 更新
            empty($data) || $model->save($data);
        }

        return $model;
    }

    /**
     * 设置错误信息
     * @param string $errorMsg
     * @param boolean $rollback
     * @return bool
     */
    protected static function setErrorMsg($errorMsg = '', $rollback = false)
    {
        if ($rollback) {
            self::rollbackTrans();
        }

        self::$errorMsg = $errorMsg;
        return false;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public static function getErrorMsg()
    {
        return self::$errorMsg ?: '操作失败！';
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
