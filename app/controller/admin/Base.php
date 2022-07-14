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
     * @api {get} /rest/:table/:option/:ids GET操作
     * @apiGroup IREST
     * @apiParam {String} table 表格代码
     * @apiParam {String} option 操作
     * @apiParam {String} ids ID串
     * @apiHeader {String} Authorization Token
     */

    /**
     * @api {post} /rest/:table/:option/:ids POST操作
     * @apiGroup IREST
     * @apiParam {String} table 表格代码
     * @apiParam {String} option 操作
     * @apiParam {String} ids ID串
     * @apiHeader {String} Authorization Token
     * @apiBody {String} :kv 键值对
     */

    /**
     * @api {put} /rest/:table/:option/:ids PUT操作
     * @apiGroup IREST
     * @apiParam {String} table 表格代码
     * @apiParam {String} option 操作
     * @apiParam {String} ids ID串
     * @apiHeader {String} Authorization Token
     * @apiBody {String} :kv 键值对
     */

    /**
     * @api {delete} /rest/:table/:option/:ids DELETE操作
     * @apiGroup IREST
     * @apiParam {String} table 表格代码
     * @apiParam {String} option 操作
     * @apiParam {String} ids ID串
     * @apiHeader {String} Authorization Token
     */
}
