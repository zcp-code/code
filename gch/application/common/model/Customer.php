<?php

namespace app\common\model;

use think\Model;

/**
 * 客户模型
 */
class Customer extends Model
{
    protected $name = 'customer';

    // 自动写入时间戳字段（FastAdmin 使用 int 类型时间戳）
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 关联经纪人
    public function agent()
    {
        return $this->belongsTo('Agent', 'agent_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function user() {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
