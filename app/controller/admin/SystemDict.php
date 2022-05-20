<?php

namespace app\controller\admin;

use app\model\SystemDict as SelfModel;

class SystemDict extends Base
{
    /**
     * @api {POST} /dicts 创建字典
     * @apiVersion 1.0.0
     * @apiGroup ISYS
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
        $items = $this->request->post('items/a');
        foreach ($items as $index => &$item) {
            $item['sort_'] = $index;
        }
        $this->validate($data, 'SystemDict');

        // 创建字典
        $model = SelfModel::create($data);
        $model->items()->saveAll($items);
        SelfModel::refreshCache($model->key_);

        return $this->success();
    }

    /**
     * @api {PUT} /dicts/:name 更新字典
     * @apiVersion 1.0.0
     * @apiGroup ISYS
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
        $items = $this->request->post('items/a');
        foreach ($items as $index => &$item) {
            $item['sort_'] = $index;
        }

        $model = SelfModel::find($name);
        if (!$model) {
            return $this->error();
        }
        if ($model->key_ == $data['key_']) {
            $this->validate($data, 'SystemDict.update');
        } else {
            $this->validate($data, 'SystemDict');
        }

        // 更新字典
        $model->save($data);
        $model->items()->where('dict_key', $model->key_)->delete();
        $model->items()->saveAll($items);
        SelfModel::refreshCache($model->key_);

        return $this->success();
    }

    /**
     * @api {DELETE} /dicts/:names 删除字典
     * @apiVersion 1.0.0
     * @apiGroup ISYS
     * @apiHeader {string} Authorization Token
     */
    public function delete($names)
    {
        // 删除字典
        $models = SelfModel::select(explode(',', $names));
        $models->each(function ($model) {
            SelfModel::removeCache($model->key_);
            $model->items()->where('dict_key', $model->key_)->delete();
            $model->delete();
        });

        return $this->success();
    }

    /**
     * @api {GET} /dicts 获取字典列表
     * @apiVersion 1.0.0
     * @apiGroup ISYS
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
     * @apiGroup ISYS
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
                        'value' => is_numeric($item['key_']) ? $item['key_'] + 0 : $item['key_'],
                        ])
        ]);
    }
}
