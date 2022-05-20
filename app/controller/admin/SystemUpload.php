<?php

namespace app\controller\admin;

/**
 * @apiDefine IUPLOAD 上传
 */
class SystemUpload extends Base
{
    // 文件命名规则
    public const FILESYSTEM_HASH_NAME_RULE = 'sha1';

    /**
     * @api {POST} /upload/token/:name 创建七牛云直传token
     * @apiVersion 1.0.0
     * @apiGroup IUPLOAD
     * @apiHeader {string} Authorization Token
     * @apiParam {string} [mime] 限定文件类型
     * @apiSuccess {string} action 上传地址
     * @apiSuccess {string} token 上传凭证
     */
    public function token($name)
    {
        $policy = [];
        $this->request->has('mime', 'post') && $policy['mimeLimit'] = $this->request->post('mime');

        return $this->success([
            'action'    => 'http://upload.qiniup.com/',
            'token'     => $this->app->filesystem->getAdapter()->getUploadToken(null, 3600, $policy)
        ]);
    }

    /**
     * @api {POST} /upload/image/:name 上传图片
     * @apiVersion 1.0.0
     * @apiGroup IUPLOAD
     * @apiHeader {string} Authorization Token
     * @apiParam {string} file 二进制文件
     * @apiSuccess {string} status 上传状态(done:完成)
     * @apiSuccess {string} url 图片URL
     */
    public function image($name)
    {
        $this->validate($this->request->file(), 'Upload.image');

        $savename = $this->app->filesystem->putFile($name, $this->request->file('file'), self::FILESYSTEM_HASH_NAME_RULE);
        return $this->success([
            'status'    => 'done',
            'url'       => $this->app->filesystem->getAdapter()->getUrl($savename)
        ]);
    }

    /**
     * @api {POST} /upload/attachment/:name 上传附件
     * @apiVersion 1.0.0
     * @apiGroup IUPLOAD
     * @apiHeader {string} Authorization Token
     * @apiParam {string} file 二进制文件
     * @apiSuccess {string} status 上传状态(done:完成)
     * @apiSuccess {string} url 附件URL
     */
    public function attachment($name)
    {
        $this->validate($this->request->file(), 'Upload.attachment');

        $savename = $this->app->filesystem->putFile($name, $this->request->file('file'), self::FILESYSTEM_HASH_NAME_RULE);
        return $this->success([
            'status'    => 'done',
            'url'       => $this->app->filesystem->getAdapter()->getUrl($savename)
        ]);
    }
}
