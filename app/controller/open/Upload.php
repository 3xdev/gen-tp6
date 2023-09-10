<?php

namespace app\controller\open;

use app\BaseController;

/**
 * @apiDefine IUPLOAD 云存储及上传
 */
class Upload extends BaseController
{
    // 文件命名规则
    public const FILESYSTEM_HASH_NAME_RULE = 'sha1';

    /**
     * @api {post} /upload/token/:name 创建云存储直传token
     * @apiGroup IUPLOAD
     * @apiHeader {String} Authorization Token
     * @apiParam {String} name 文件类名
     * @apiBody {String} [mime] 限定文件类型
     * @apiSuccess {String} action 上传地址
     * @apiSuccess {String} token 上传凭证
     */
    public function token($name)
    {
        $policy = [];
        $this->request->has('mime', 'post') && $policy['mimeLimit'] = $this->request->post('mime');

        return $this->success([
            'domain'    => 'http://' . config('filesystem.disks.qiniu.domain') . '/',
            'action'    => 'http://upload-z2.qiniup.com/',
            'token'     => $this->app->filesystem->getAdapter()->getUploadToken(null, 3600, $policy)
        ]);
    }
}
