<?php

namespace app\controller\admin;

/**
 * 系统列管理控制器
 */
class SystemCol extends Crud
{
    protected function initialize()
    {
        $this->model = new \app\model\SystemCol();
    }
}
