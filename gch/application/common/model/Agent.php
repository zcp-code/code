<?php

namespace app\common\model;

use think\Model;

/**
 * 经纪人模型
 */
class Agent extends Model
{
    protected $name = 'agent';

    // 自动写入时间戳字段（FastAdmin 使用 int 类型时间戳）
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'status_text',
        'customer_auth_text',
        'house_auth_text',
    ];

    // 状态列表（tinyint）
    public function getStatusList()
    {
        return ['1' => '启用', '0' => '停用'];
    }

    // 状态值到 user 表 varchar 的映射
    public static function statusToUser($status)
    {
        $map = ['1' => 'normal', '0' => 'hidden'];
        return $map[$status] ?? 'normal';
    }

    // 状态文本获取器
    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[(string)$value] ?? '';
    }

    public function getCustomerAuthList()
    {
        return ['1' => '有', '0' => '无'];
    }

    public function getCustomerAuthTextAttr($value, $data)
    {
        $value = $value ?: ($data['customer_auth'] ?? '');
        $list = $this->getCustomerAuthList();
        return $list[(string)$value] ?? '';
    }

    public function getHouseAuthList()
    {
        return ['1' => '有', '0' => '无'];
    }

    public function getHouseAuthTextAttr($value, $data)
    {
        $value = $value ?: ($data['house_auth'] ?? '');
        $list = $this->getHouseAuthList();
        return $list[(string)$value] ?? '';
    }

    // 关联房源
    public function houses()
    {
        return $this->hasMany('House', 'agent_id', 'id');
    }

    // 关联用户
    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id');
    }
}
