<?php

namespace app\controller\admin;

use app\model\Table as TableModel;
use think\db\exception\ModelNotFoundException;

/**
 * CRUD基础控制器类
 * @apiDefine ICRUD CRUD
 */
class Crud extends Base
{
    /**
     * @api {GET} /crud/:table 列表
     * @apiVersion 1.0.0
     * @apiGroup ICRUD
     * @apiHeader {string} Authorization Token
     * @apiParam {string} :search 查询值
     * @apiParam {number} current 当前页
     * @apiParam {number} pageSize 页大小
     * @apiParam {string} filter ProTable的filter
     * @apiParam {string} sorter ProTable的sorter
     * @apiSuccess {number} total 数据总计
     * @apiSuccess {Object[]} data 数据列表
     * @apiSuccess {string} data.:field 字段值
     */
    public function index()
    {
        $table = TableModel::find(parse_name(string_remove_prefix($this->request->controller(), 'admin.'), 0));
        $current = $this->request->get('current/d', 1);
        $pageSize = $this->request->get('pageSize/d', 10);
        $search = $this->request->only(array_merge(
            $table->cols->filter(fn($col) => empty($col->hide_in_search))->column('data_index'),
            ['filter', 'sorter']
        ), 'get');
        $total = $this->model->withSearch(array_keys($search), $search)->count();
        $list = $this->model->withSearch(array_keys($search), $search)->with(
            $table->cols->filter(fn($col) => empty($col->hide_in_table) && !empty($col->relation_name))->column('relation_name') ?: []
        )->page($current, $pageSize)->select();

        return $this->success([
            'total' => $total,
            'data'  => $list->visible(array_merge([$this->model->getPk()], $table->crud_index_cols))->toArray()
        ]);
    }

    /**
     * @api {POST} /crud/:table 创建
     * @apiVersion 1.0.0
     * @apiGroup ICRUD
     * @apiHeader {string} Authorization Token
     * @apiParam {string} :field 字段值
     */
    public function create()
    {
        $table = TableModel::find(parse_name(string_remove_prefix($this->request->controller(), 'admin.'), 0));
        $data = $this->request->post($table->cols->filter(fn($col) => empty($col->hide_in_form))->column('data_index'));

        // 创建
        $this->model->create($data);

        return $this->success();
    }

    /**
     * @api {GET} /crud/:table/:id 读取
     * @apiVersion 1.0.0
     * @apiGroup ICRUD
     * @apiHeader {string} Authorization Token
     * @apiSuccess {string} :field 字段值
     */
    public function read($id)
    {
        $table = TableModel::find(parse_name(string_remove_prefix($this->request->controller(), 'admin.'), 0));
        $obj = $this->model->find($id);
        if (!$obj) {
            throw new ModelNotFoundException('数据不存在');
        }

        return $this->success($obj->visible(array_merge([$this->model->getPk()], $table->crud_read_cols))->toArray());
    }

    /**
     * @api {PUT} /crud/:table/:id 更新
     * @apiVersion 1.0.0
     * @apiGroup ICRUD
     * @apiHeader {string} Authorization Token
     * @apiParam {string} :field 字段值
     */
    public function update($id)
    {
        $table = TableModel::find(parse_name(string_remove_prefix($this->request->controller(), 'admin.'), 0));
        $data = $this->request->post($table->cols->filter(fn($col) => empty($col->hide_in_form))->column('data_index'));

        $obj = $this->model->find($id);
        if (!$obj) {
            throw new ModelNotFoundException('数据不存在');
        }

        $obj->save($data);
        return $this->success();
    }

    /**
     * @api {DELETE} /crud/:table/:ids 删除
     * @apiVersion 1.0.0
     * @apiGroup ICRUD
     * @apiHeader {string} Authorization Token
     */
    public function delete($ids)
    {
        $objs = $this->model->where($this->model->getPk(), 'in', explode(',', $ids))->select();
        if ($objs->isEmpty()) {
            throw new ModelNotFoundException('数据不存在');
        }

        foreach ($objs as $obj) {
            $obj->delete();
        }
        return $this->success();
    }
}
