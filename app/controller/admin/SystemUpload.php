<?php

namespace app\controller\admin;

/**
 * @apiDefine ISYSUPLOAD 系统-存储及上传
 */
class SystemUpload extends Base
{
    // 文件命名规则
    public const FILESYSTEM_HASH_NAME_RULE = 'sha1';

    /**
     * @api {post} /upload/token/:name 创建七牛云直传token
     * @apiGroup ISYSUPLOAD
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
            'action'    => 'http://upload.qiniup.com/',
            'token'     => $this->app->filesystem->getAdapter()->getUploadToken(null, 3600, $policy)
        ]);
    }

    /**
     * @api {post} /upload/image/:name 上传图片
     * @apiGroup ISYSUPLOAD
     * @apiHeader {String} Authorization Token
     * @apiParam {String} name 文件类名
     * @apiBody {String} file 二进制文件
     * @apiSuccess {String} status 上传状态(done:完成)
     * @apiSuccess {String} url 图片URL
     */
    public function image($name)
    {
        $this->validate($this->request->file(), 'Upload.image');

        $savename = $this->app->filesystem->putFile($name, $this->request->file('file'), self::FILESYSTEM_HASH_NAME_RULE);
        return $this->success([
            'status'    => 'done',
            'url'       => $this->app->filesystem->getAdapter()->url($savename)
        ]);
    }

    /**
     * @api {post} /upload/attachment/:name 上传附件
     * @apiGroup ISYSUPLOAD
     * @apiHeader {String} Authorization Token
     * @apiParam {String} name 文件类名
     * @apiBody {String} file 二进制文件
     * @apiSuccess {String} status 上传状态(done:完成)
     * @apiSuccess {String} url 附件URL
     */
    public function attachment($name)
    {
        $this->validate($this->request->file(), 'Upload.attachment');

        $savename = $this->app->filesystem->putFile($name, $this->request->file('file'), self::FILESYSTEM_HASH_NAME_RULE);
        return $this->success([
            'status'    => 'done',
            'url'       => $this->app->filesystem->getAdapter()->url($savename)
        ]);
    }
}
