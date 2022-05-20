<?php

namespace app\controller\admin;

use app\model\SystemTable as SystemTableModel;
use think\db\exception\ModelNotFoundException;

/**
 * CRUD基础控制器类
 * @apiDefine ICRUD CRUD
 */
class Crud extends Base
{
    /**
     * @api {GET} /suggest/:table suggest数据源
     * @apiVersion 1.0.0
     * @apiGroup ICRUD
     * @apiHeader {string} Authorization Token
     * @apiParam {string} keyword 关键字
     * @apiParam {number} pageSize 页大小
     * @apiSuccess {Object[]} data 数据列表
     */
    public function suggest()
    {
        $pageSize = $this->request->get('pageSize/d', 100);
        $search = $this->request->only(['keyword'], 'get');

        $objs = $this->model->withSearch(array_keys($search), $search)->limit($pageSize)->select();
        $data = [];
        foreach ($objs as $obj) {
            $data[] = [
                'label' => $obj[$this->model->keyword_fields[0]],
                'value' => $obj[$this->model->keyword_pk]
            ];
        }

        return $this->success([
            'data'  => $data
        ]);
    }

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
        $table = SystemTableModel::find(parse_name(string_remove_prefix($this->request->controller(), 'admin.'), 0));
        $current = $this->request->get('current/d', 1);
        $pageSize = $this->request->get('pageSize/d', 10);
        $search = $this->request->only(array_merge(
            $table->cols->filter(fn($col) => empty($col->hide_in_search))->column('data_index'),
            ['filter', 'sorter']
        ), 'get');

        $total = $this->model->withSearch(array_keys($search), $search)->count();
        $objs = $this->model->withSearch(array_keys($search), $search)->with(
            $table->cols->filter(fn($col) => empty($col->hide_in_table) && !empty($col->relation_name))->column('relation_name') ?: []
        )->page($current, $pageSize)->select();
        $data = [];
        foreach ($objs as $obj) {
            $data[] = array_merge_recursive($obj->visible(array_merge([$this->model->getPk()], $table->crud_index_cols))->toArray(), $this->mergeIndex($obj));
        }

        return $this->success([
            'total' => $total,
            'data'  => $data
        ]);
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
     * @api {POST} /crud/:table 创建
     * @apiVersion 1.0.0
     * @apiGroup ICRUD
     * @apiHeader {string} Authorization Token
     * @apiParam {string} :field 字段值
     */
    public function create()
    {
        $table = SystemTableModel::find(parse_name(string_remove_prefix($this->request->controller(), 'admin.'), 0));
        $data = $this->request->post($table->cols->filter(fn($col) => empty($col->hide_in_form))->column('data_index'));

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
        $table = SystemTableModel::find(parse_name(string_remove_prefix($this->request->controller(), 'admin.'), 0));
        $obj = $this->model->find($id);
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
     * @api {PUT} /crud/:table/:id 更新
     * @apiVersion 1.0.0
     * @apiGroup ICRUD
     * @apiHeader {string} Authorization Token
     * @apiParam {string} :field 字段值
     */
    public function update($id)
    {
        $table = SystemTableModel::find(parse_name(string_remove_prefix($this->request->controller(), 'admin.'), 0));
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
