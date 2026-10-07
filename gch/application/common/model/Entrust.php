<?php

namespace app\common\model;

use think\Model;

/**
 * 委托模型
 */
class Entrust extends Model
{
    protected $name = 'entrust';

    // 自动写入时间戳字段（FastAdmin 使用 int 类型时间戳）
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'type_text',
        'source_text',
    ];

    // 类型列表
    public function getTypeList()
    {
        return ['buy' => '买房', 'sell' => '卖房', 'rent' => '租房', 'rentto' => '出租'];
    }

    // 来源列表
    public function getSourceList()
    {
        return ['entrust' => '委托找房', 'booking' => '预约看房'];
    }

    // 类型文本获取器
    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
    }

    // 来源文本获取器
    public function getSourceTextAttr($value, $data)
    {
        $value = $value ?: ($data['source'] ?? '');
        $list = $this->getSourceList();
        return $list[$value] ?? '';
    }

    // 关联房源
    public function house()
    {
        return $this->belongsTo('House', 'house_id', 'id', [], 'LEFT')->setEagerlyType(0);
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
