<?php

namespace app\controller\admin;

/**
 * 系统字典条目管理控制器
 */
class SystemDictItem extends Crud
{
    protected function initialize()
    {
        $this->model = new \app\model\SystemDictItem();
    }
}
