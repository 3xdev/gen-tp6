<?php

namespace app\controller\admin;

/**
 * 用户管理控制器
 */
class User extends Crud
{
    protected function initialize()
    {
        $this->model = new \app\model\User();
    }
}
