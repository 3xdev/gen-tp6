<?php

namespace app\validate;

use think\Validate;

class Upload extends Validate
{
    // 图片验证场景
    public function sceneImage()
    {
        return $this->append('file', 'fileSize:10240000|fileExt:bmp,gif,jpeg,jpg,png');
    }

    // 附件验证场景
    public function sceneAttachment()
    {
        return $this->append('file', 'fileSize:102400000|fileExt:txt,md,bmp,gif,jpeg,jpg,png,pdf,doc,docx,xls,xlsx,ppt,zip,rar,7z');
    }
}
