<?php

namespace app\controller\admin;

/**
 * @apiDefine ICRUD CRUD
 */
class Crud extends Base
{
    /**
     * 模型实例
     * @var \app\model\Base
     */
    protected $model;

    /**
     * @api {POST} /crud/:name 创建
     * @apiVersion 1.0.0
     * @apiGroup ICRUD
     * @apiHeader {string} Authorization Token
     * @apiParam {string} :field 字段值
     */
    public function create()
    {
        $data = $this->request->post(['amount', 'tags', 'status']);
        $data['delete_time'] = 0;

        // 创建
        $this->model->create($data);

        return $this->success();
    }

    /**
     * @api {PUT} /crud/:name/:id 更新
     * @apiVersion 1.0.0
     * @apiGroup ICRUD
     * @apiHeader {string} Authorization Token
     * @apiParam {string} :field 字段值
     */
    public function update($id)
    {
        $data = $this->request->post(['amount', 'tags', 'status']);
        $data['delete_time'] = 0;

        $model = $this->model->find($id);
        if (!$model) {
            return $this->error();
        }

        $model->save($data);
        return $this->success();
    }

    /**
     * @api {DELETE} /crud/:name/:ids 删除
     * @apiVersion 1.0.0
     * @apiGroup ICRUD
     * @apiHeader {string} Authorization Token
     */
    public function delete($ids)
    {
        $admins = $this->model->where('id', 'in', explode(',', $ids))->select();
        if (!$admins) {
            return $this->error();
        }

        foreach ($admins as $admin) {
            $admin->delete();
        }
        return $this->success();
    }

    /**
     * @api {GET} /crud/:name 列表
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
        $current = $this->request->get('current/d', 1);
        $pageSize = $this->request->get('pageSize/d', 10);
        $search = $this->request->only(['amount', 'tags', 'create_time', 'filter', 'sorter'], 'get');

        $total = $this->model->withSearch(array_keys($search), $search)->count();
        $list = $this->model->with('admin')->withSearch(array_keys($search), $search)->page($current, $pageSize)->select();

        return $this->success([
            'total' => $total,
            'data' => $list->visible(['id', 'amount', 'tags', 'status', 'admin' => ['username', 'nickname', 'mobile'], 'create_time'])->toArray()
        ]);
    }

    /**
     * @api {GET} /crud/:name/:id 读取
     * @apiVersion 1.0.0
     * @apiGroup ICRUD
     * @apiHeader {string} Authorization Token
     * @apiSuccess {string} :field 字段值
     */
    public function read($id)
    {
        $admin = $this->model->find($id);
        if (!$admin) {
            return $this->error();
        }

        return $this->success($admin->visible([
            'id', 'amount', 'tags', 'create_time'
        ])->toArray());
    }
}
