<?php

namespace app\controller\admin;

use app\model\Dict as SelfModel;

/**
 * @apiDefine IDICT 字典
 */
class Dict extends Base
{
    /**
     * @api {POST} /dicts 创建字典
     * @apiVersion 1.0.0
     * @apiGroup IDICT
     * @apiHeader {string} Authorization Token
     * @apiParam {string} key_ 代码
     * @apiParam {string} label 名称
     * @apiParam {string} intro 说明
     * @apiParam {object[]} items 条目
     * @apiParam {string} items.key_ 条目代码
     * @apiParam {string} items.label 条目名称
     */
    public function create()
    {
        $data = $this->request->post(['key_', 'label', 'intro']);
        $this->validate($data, 'Dict');

        // 创建字典
        $model = SelfModel::create($data);
        $model->items()->saveAll($this->request->post('items/a'));

        return $this->success();
    }

    /**
     * @api {PUT} /dicts/:name 更新字典
     * @apiVersion 1.0.0
     * @apiGroup IDICT
     * @apiHeader {string} Authorization Token
     * @apiParam {string} key_ 代码
     * @apiParam {string} label 名称
     * @apiParam {string} intro 说明
     * @apiParam {object[]} items 条目
     * @apiParam {string} items.key_ 条目代码
     * @apiParam {string} items.label 条目名称
     */
    public function update($name)
    {
        $data = $this->request->post(['key_', 'label', 'intro']);

        $model = SelfModel::find($name);
        if (!$model) {
            return $this->error();
        }
        if ($model->key_ == $data['key_']) {
            $this->validate($data, 'Dict.update');
        } else {
            $this->validate($data, 'Dict');
        }

        $model->save($data);
        $model->items()->delete();
        $model->items()->saveAll($this->request->post('items/a'));
        return $this->success();
    }

    /**
     * @api {DELETE} /dicts/:name 删除字典
     * @apiVersion 1.0.0
     * @apiGroup IDICT
     * @apiHeader {string} Authorization Token
     */
    public function delete($name)
    {
        SelfModel::select(explode(',', $name))->each(function ($model) {
            $model->items()->delete();
            $model->delete();
        });

        return $this->success();
    }

    /**
     * @api {GET} /dicts 获取字典列表
     * @apiVersion 1.0.0
     * @apiGroup IDICT
     * @apiHeader {string} Authorization Token
     * @apiParam {string} key_ 代码
     * @apiParam {string} label 名称
     * @apiParam {string} intro 说明
     * @apiParam {number} current 当前页
     * @apiParam {number} pageSize 页大小
     * @apiParam {string} filter ProTable的filter
     * @apiParam {string} sorter ProTable的sorter
     * @apiSuccess {number} total 数据总计
     * @apiSuccess {Object[]} data 数据列表
     * @apiSuccess {string} data.key_ 代码
     * @apiSuccess {string} data.label 名称
     * @apiSuccess {string} data.intro 说明
     * @apiSuccess {object[]} data.items 条目
     * @apiSuccess {string} data.items.key_ 条目代码
     * @apiSuccess {string} data.items.label 条目名称
     * @apiSuccess {string} data.items.intro 条目描述
     */
    public function index()
    {
        $current = $this->request->get('current/d', 1);
        $pageSize = $this->request->get('pageSize/d', 10);
        $search = $this->request->only(['key_', 'label', 'intro', 'filter', 'sorter'], 'get');

        $total = SelfModel::with('items')->withSearch(array_keys($search), $search)->count();
        $list = SelfModel::with('items')->withSearch(array_keys($search), $search)->page($current, $pageSize)->select();

        return $this->success([
            'total' => $total,
            'data' => $list->visible(['key_', 'label', 'intro', 'items' => ['key_', 'label', 'intro']])->toArray()
        ]);
    }

    /**
     * @api {GET} /dicts/:name 获取字典条目
     * @apiVersion 1.0.0
     * @apiGroup IDICT
     * @apiSuccess {object[]} items 条目
     * @apiSuccess {string} items.value 条目代码
     * @apiSuccess {string} items.label 条目名称
     */
    public function read($name)
    {
        $model = SelfModel::with('items')->find($name);
        if (!$model) {
            return $this->error();
        }

        return $this->success([
            'items' => $model->items->map(fn($item) => [
                        'label' => $item['label'],
                        'value' => $item['key_'],
                        ])
        ]);
    }
}
