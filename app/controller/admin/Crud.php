<?php

namespace app\controller\admin;

use app\model\SystemTable as SystemTableModel;
use OpenSpout\Writer\Common\Creator\WriterEntityFactory;
use think\db\exception\ModelNotFoundException;

/**
 * CRUD基础控制器类
 * @apiDefine ICRUD CRUD
 */
class Crud extends Base
{
    /**
     * @api {get} /suggest/:table suggest数据源
     * @apiGroup ICRUD
     * @apiHeader {String} Authorization Token
     * @apiParam {String} table 表格代码
     * @apiQuery {String} [keyword] 关键字
     * @apiQuery {Number} [pageSize] 页大小
     * @apiSuccess {Object[]} data 数据列表
     */
    public function suggest()
    {
        $pageSize = $this->request->get('pageSize/d', 100);
        $search = $this->request->except(['pageSize'], 'get');

        $objs = $this->model->scope($this->model_scope)->withSearch(array_keys($search), $search)->limit($pageSize)->select();
        $data = [];
        foreach ($objs as $obj) {
            $data[] = [
                'label' => implode('|', array_filter($obj->visible($this->model->keyword_fields)->toArray())),
                'value' => $obj[$this->model->keyword_pk]
            ];
        }

        return $this->success([
            'data'  => $data
        ]);
    }

    /**
     * @api {get} /enum/:table enum数据源
     * @apiGroup ICRUD
     * @apiHeader {String} Authorization Token
     * @apiParam {String} table 表格代码
     * @apiQuery {String} [values] 值串
     * @apiQuery {String} [labelCol] 标签字段
     * @apiQuery {String} [valueCol] 值字段
     * @apiSuccess {Object[]} data 数据列表
     */
    public function enum()
    {
        $values = $this->request->get('values', '0');
        $valueCol = $this->request->get('valueCol', $this->model->getPk());
        $labelCol = $this->request->get('labelCol', $this->model->getPk());
        $objs = $this->model->scope($this->model_scope)->where($valueCol, 'in', explode(',', $values))->select();
        $data = [];
        foreach ($objs as $obj) {
            $data[] = [
                'label' => $obj[$labelCol],
                'value' => $obj[$valueCol]
            ];
        }

        return $this->success([
            'data'  => $data
        ]);
    }

    /**
     * @api {get} /crud/:table 获取列表
     * @apiGroup ICRUD
     * @apiHeader {String} Authorization Token
     * @apiParam {String} table 表格代码
     * @apiQuery {String} [:search] 查询键值对
     * @apiQuery {Number} [current] 当前页
     * @apiQuery {Number} [pageSize] 页大小
     * @apiQuery {String} [filter] ProTable的filter
     * @apiQuery {String} [sorter] ProTable的sorter
     * @apiSuccess {Number} total 数据总计
     * @apiSuccess {Object[]} data 数据列表
     * @apiSuccess {String} data.:field 字段值
     */
    public function index()
    {
        $table = SystemTableModel::where('code', parse_name(string_remove_prefix($this->request->controller(), 'admin.'), 0))->find();
        $this->model->systemTable = $table;
        $current = $this->request->get('current/d', 1);
        $pageSize = $this->request->get('pageSize/d', 10);
        $search = $this->request->only(array_merge(
            pt_search4col($table->cols->column('data_index')),
            ['filter']
        ), 'get');
        $ignore = function ($val) {
            if ($val === '') {
                return false;
            } else {
                return true;
            }
        };
        $search = array_filter($search, $ignore);
        $lsearch = $this->request->only(array_merge(
            pt_search4col($table->cols->column('data_index')),
            ['filter', 'sorter']
        ), 'get');
        $lsearch = array_filter($lsearch, $ignore);
        $total = $this->model->scope($this->model_scope)->where($this->whereIndex())->withSearch(array_keys($search), $search)->count();
        $objs = $this->model->scope($this->model_scope)->where($this->whereIndex())->withSearch(array_keys($lsearch), $lsearch)->with(
            $table->cols->filter(fn($col) => empty($col->hide_in_table) && !empty($col->relation_name))->column('relation_name') ?: []
        )->page($current, $pageSize)->select();
        $data = [];
        $visible = array_filter($table->crud_index_cols, fn($col) => $this->model->isTableField($col));
        $append = array_diff($table->crud_index_cols, $visible);
        foreach ($objs as $obj) {
            $data[] = array_replace_recursive(
                $obj->visible(array_merge([$this->model->getPk()], $visible))->append($append)->toArray(),
                $this->mergeIndex($obj)
            );
        }

        return $this->success([
            'total' => $total,
            'data'  => $data
        ]);
    }
    /**
     * @api {get} /export/:table 导出列表
     * @apiGroup ICRUD
     * @apiHeader {String} Authorization Token
     * @apiParam {String} table 表格代码
     * @apiQuery {String} [:search] 查询键值对
     * @apiQuery {String} [filter] ProTable的filter
     * @apiQuery {String} [sorter] ProTable的sorter
     */
    public function export()
    {
        $table = SystemTableModel::where('code', parse_name(string_remove_prefix($this->request->controller(), 'admin.'), 0))->find();
        $this->model->systemTable = $table;
        $search = $this->request->only(array_merge(
            pt_search4col($table->cols->column('data_index')),
            ['filter']
        ), 'get');
        $ignore = function ($val) {
            if ($val === '') {
                return false;
            } else {
                return true;
            }
        };
        $search = array_filter($search, $ignore);
        $lsearch = $this->request->only(array_merge(
            pt_search4col($table->cols->column('data_index')),
            ['filter', 'sorter']
        ), 'get');
        $lsearch = array_filter($lsearch, $ignore);
        $objs = $this->model->scope($this->model_scope)->where($this->whereIndex())->withSearch(array_keys($lsearch), $lsearch)->with(
            $table->cols->filter(fn($col) => empty($col->hide_in_table) && !empty($col->relation_name))->column('relation_name') ?: []
        )->select();
        $data = [];
        $visible = array_filter($table->crud_index_cols, fn($col) => $this->model->isTableField($col));
        $append = array_diff($table->crud_index_cols, $visible);
        foreach ($objs as $obj) {
            $data[] = array_replace_recursive(
                $obj->visible(array_merge([$this->model->getPk()], $visible))->append($append)->toArray(),
                $this->mergeIndex($obj)
            );
        }

        $cols = $table->cols->filter(fn($col) => empty($col->hide_in_table));
        $dataRows = [];
        foreach ($data as $d) {
            $array = [];
            foreach ($cols as $col) {
                $array[] = value2string($d[$col->data_index] ?? \think\helper\Arr::get($d, $col->data_index, ''), $col->value_enum);
            }
            $dataRows[] = WriterEntityFactory::createRowFromArray($array);
        }

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToBrowser('export.xlsx')
               ->addRow(WriterEntityFactory::createRowFromArray($cols->column('title')))
               ->addRows($dataRows)
               ->close();
        exit();
    }
    /**
     * 列表的条件限制
     * @access public
     * @return array
     */
    public function whereIndex()
    {
        return [];
    }
    /**
     * 列表的合并数据(扩展列表返回)
     * @access public
     * @param  \think\Model  $obj  模型对象
     * @return array
     */
    public function mergeIndex($obj)
    {
        return [];
    }

    /**
     * @api {post} /crud/:table 创建
     * @apiGroup ICRUD
     * @apiHeader {String} Authorization Token
     * @apiParam {String} table 表格代码
     * @apiBody {String} :field 字段值
     */
    public function create()
    {
        $table = SystemTableModel::where('code', parse_name(string_remove_prefix($this->request->controller(), 'admin.'), 0))->find();
        $data = array_merge($this->request->post($table->cols->column('data_index')), $this->mergeCreate());
        $this->validate($data, parse_name(string_remove_prefix($this->request->controller(), 'admin.')));
        $this->model->create($data);

        return $this->success();
    }
    /**
     * 创建的合并数据(扩展创建)
     * @access public
     * @return array
     */
    public function mergeCreate()
    {
        return [];
    }

    /**
     * @api {get} /crud/:table/:id 读取
     * @apiGroup ICRUD
     * @apiHeader {String} Authorization Token
     * @apiParam {String} table 表格代码
     * @apiParam {Number} id ID
     * @apiSuccess {String} :field 字段值
     */
    public function read($id)
    {
        $table = SystemTableModel::where('code', parse_name(string_remove_prefix($this->request->controller(), 'admin.'), 0))->find();
        $obj = $this->model->scope($this->model_scope)->find($id);
        if (!$obj) {
            throw new ModelNotFoundException('数据不存在');
        }

        return $this->success(
            array_merge_recursive($obj->visible(array_merge([$this->model->getPk()], $table->crud_read_cols))->toArray(), $this->mergeRead($obj))
        );
    }
    /**
     * 读取的合并数据(扩展读取返回)
     * @access public
     * @param  \think\Model  $obj  模型对象
     * @return array
     */
    public function mergeRead($obj)
    {
        return [];
    }

    /**
     * @api {put} /crud/:table/:id 更新
     * @apiGroup ICRUD
     * @apiHeader {String} Authorization Token
     * @apiParam {String} table 表格代码
     * @apiParam {Number} id ID
     * @apiBody {String} :field 字段值
     */
    public function update($id)
    {
        $table = SystemTableModel::where('code', parse_name(string_remove_prefix($this->request->controller(), 'admin.'), 0))->find();
        $obj = $this->model->scope($this->model_scope)->find($id);
        if (!$obj) {
            throw new ModelNotFoundException('数据不存在');
        }
        $data = $this->request->post($table->cols->filter(fn($col) => empty($col->hide_in_form))->column('data_index'));
        $this->validate(
            array_merge($obj->visible(array_merge($this->validate_field_append, [$obj->getPk()]))->toArray(), $data),
            parse_name(string_remove_prefix($this->request->controller(), 'admin.'))
        );

        $original_data = $obj->toArray();
        $original_data  = json_encode($original_data);
        $this->request->original_data = $original_data;

        $obj->save($data);
        return $this->success();
    }

    /**
     * @api {delete} /crud/:table/:ids 删除
     * @apiGroup ICRUD
     * @apiHeader {String} Authorization Token
     * @apiParam {String} table 表格代码
     * @apiParam {String} ids ID串
     */
    public function delete($ids)
    {
        $objs = $this->model->scope($this->model_scope)->where($this->model->getPk(), 'in', explode(',', $ids))->select();
        if ($objs->isEmpty()) {
            throw new ModelNotFoundException('数据不存在');
        }

        foreach ($objs as $obj) {
            $this->eachDelete($obj);
        }

        return $this->success();
    }
    /**
     * 删除的模型处理(扩展删除操作)
     * @access public
     * @param  \think\Model  $obj  模型对象
     */
    public function eachDelete($obj)
    {
        $obj->delete();
    }
}
