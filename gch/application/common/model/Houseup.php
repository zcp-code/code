<?php

namespace app\common\model;

use think\Model;


class Houseup extends Model
{

    

    

    // 表名
    protected $name = 'house_up';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'has_elevator_text',
        'verify_time_text',
        'check_status_text',
        'publish_time_text'
    ];
    

    
    public function getTypeList()
    {
        return ['sale' => __('Type sale'), 'rent' => __('Type rent'), 'new' => __('Type new')];
    }

    public function getHasElevatorList()
    {
        return ['0' => __('Has_elevator 0'), '1' => __('Has_elevator 1')];
    }

    public function getCheckStatusList()
    {
        return ['0' => __('Check_status 0'), '1' => __('Check_status 1'), '2' => __('Check_status 2')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
    }


    public function getHasElevatorTextAttr($value, $data)
    {
        $value = $value ?: ($data['has_elevator'] ?? '');
        $list = $this->getHasElevatorList();
        return $list[$value] ?? '';
    }


    public function getVerifyTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['verify_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCheckStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['check_status'] ?? '');
        $list = $this->getCheckStatusList();
        return $list[$value] ?? '';
    }


    public function getPublishTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['publish_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setVerifyTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setPublishTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function house()
    {
        return $this->belongsTo('House', 'house_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function community()
    {
        return $this->belongsTo('Community', 'community_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function agent()
    {
        return $this->belongsTo('Agent', 'agent_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
