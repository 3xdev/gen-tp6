<?php

namespace app\controller\admin;

use app\model\SystemDict as SelfModel;

class SystemDict extends Base
{
    /**
     * @api {post} /dicts 创建字典
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiBody {String} key_ 代码
     * @apiBody {String} label 名称
     * @apiBody {String} intro 说明
     * @apiBody {object[]} items 条目
     * @apiBody {String} items.key_ 条目代码
     * @apiBody {String} items.label 条目名称
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
     * @api {put} /dicts/:name 更新字典
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiParam {String} name 字典代码
     * @apiBody {String} key_ 代码
     * @apiBody {String} label 名称
     * @apiBody {String} intro 说明
     * @apiBody {object[]} items 条目
     * @apiBody {String} items.key_ 条目代码
     * @apiBody {String} items.label 条目名称
     */
    public function update($name)
    {
        $data = $this->request->post(['key_', 'label', 'intro']);
        $items = $this->request->post('items/a');
        foreach ($items as $index => &$item) {
            $item['sort_'] = $index;
        }

        $model = SelfModel::where('key_', $name)->find();
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
        $model->items->delete();
        $model->items()->saveAll($items);
        SelfModel::refreshCache($model->key_);

        return $this->success();
    }

    /**
     * @api {delete} /dicts/:names 删除字典
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiParam {String} names 字典代码串
     */
    public function delete($names)
    {
        // 删除字典
        $models = SelfModel::whereIn('key_', explode(',', $names))->select();
        $models->each(function ($model) {
            SelfModel::removeCache($model->key_);
            $model->items->delete();
            $model->delete();
        });

        return $this->success();
    }

    /**
     * @api {get} /dicts 获取字典列表
     * @apiGroup ISYS
     * @apiHeader {String} Authorization Token
     * @apiQuery {String} [key_] 代码
     * @apiQuery {String} [label] 名称
     * @apiQuery {String} [intro] 说明
     * @apiQuery {Number} [current] 当前页
     * @apiQuery {Number} [pageSize] 页大小
     * @apiQuery {String} [filter] ProTable的filter
     * @apiQuery {String} [sorter] ProTable的sorter
     * @apiSuccess {Number} total 数据总计
     * @apiSuccess {Object[]} data 数据列表
     * @apiSuccess {String} data.key_ 代码
     * @apiSuccess {String} data.label 名称
     * @apiSuccess {String} data.intro 说明
     * @apiSuccess {object[]} data.items 条目
     * @apiSuccess {String} data.items.key_ 条目代码
     * @apiSuccess {String} data.items.label 条目名称
     * @apiSuccess {String} data.items.intro 条目描述
     */
    public function index()
    {
        $current = $this->request->get('current/d', 1);
        $pageSize = $this->request->get('pageSize/d', 10);
        $search = $this->request->only(['key_', 'label', 'intro', 'filter'], 'get');
        $lsearch = $this->request->only(['key_', 'label', 'intro', 'filter', 'sorter'], 'get');

        $total = SelfModel::with('items')->withSearch(array_keys($search), $search)->count();
        $list = SelfModel::with('items')->withSearch(array_keys($lsearch), $lsearch)->page($current, $pageSize)->select();

        return $this->success([
            'total' => $total,
            'data' => $list->visible(['id', 'key_', 'label', 'intro', 'items' => ['key_', 'label', 'intro']])->toArray()
        ]);
    }

    /**
     * @api {get} /dicts/:name 获取字典条目
     * @apiGroup ISYS
     * @apiParam {String} name 字典代码
     * @apiSuccess {object[]} items 条目
     * @apiSuccess {String} items.value 条目代码
     * @apiSuccess {String} items.label 条目名称
     */
    public function read($name)
    {
        $model = SelfModel::with('items')->where('key_', $name)->find();
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
