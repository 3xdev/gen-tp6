<?php

namespace app\controller\admin;

use app\BaseController;

/**
 * 基础控制器类(支持REST)
 * @apiDefine IREST REST
 */
class Base extends BaseController
{
    /**
     * 模型实例
     * @var \app\model\Base
     */
    protected $model;

    /**
     * @api {GET} /rest/:table/:option/:ids GET操作
     * @apiVersion 1.0.0
     * @apiGroup IREST
     * @apiHeader {string} Authorization Token
     */

    /**
     * @api {POST} /rest/:table/:option/:ids POST操作
     * @apiVersion 1.0.0
     * @apiGroup IREST
     * @apiHeader {string} Authorization Token
     */

    /**
     * @api {PUT} /rest/:table/:option/:ids PUT操作
     * @apiVersion 1.0.0
     * @apiGroup IREST
     * @apiHeader {string} Authorization Token
     */

    /**
     * @api {DELETE} /rest/:table/:option/:ids DELETE操作
     * @apiVersion 1.0.0
     * @apiGroup IREST
     * @apiHeader {string} Authorization Token
     */
}
