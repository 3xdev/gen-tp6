<?php

namespace app\model;

use think\model\concern\SoftDelete;

class SystemTableOption extends Base
{
    use SoftDelete;

    public function btable()
    {
        return $this->belongsTo(SystemTable::class, 'table_code', 'code');
    }
}
