<?php

namespace app\common\model;

use think\Model;


class Customerlog extends Model
{

    

    

    // 表名
    protected $name = 'customer_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'submit_time_text'
    ];
    

    



    public function getSubmitTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['submit_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setSubmitTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function customer()
    {
        return $this->belongsTo('Customer', 'customer_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    // 关联经纪人
    public function agent()
    {
        return $this->belongsTo('Agent', 'agent_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function user() {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
