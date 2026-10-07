<?php

namespace app\common\model;

use think\Model;


class House extends Model
{

    // 表名
    protected $name = 'house';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'status_text',
        'has_elevator_text',
        'verify_status_text',
        'verify_time_text',
        'publish_time_text',
        'is_recommend_text',
        'check_status_text'
    ];

    protected static function init()
    {
        // 新增房源：经纪人房源数 +1
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            // weigh 自增
            // if (!$row['weigh']) {
            //     $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
            // }
            // 更新经纪人房源数量
            if (!empty($row['agent_id'])) {
                \app\common\model\Agent::where('id', $row['agent_id'])->setInc('house_count');
            }
        });

        // 删除房源：经纪人房源数 -1
        self::afterDelete(function ($row) {
            if (!empty($row['agent_id'])) {
                $count = self::where('agent_id', $row['agent_id'])->count();
                \app\common\model\Agent::where('id', $row['agent_id'])->update(['house_count' => $count]);
            }
        });
    }

    
    public function getTypeList()
    {
        return ['sale' => '二手房', 'rent' => '租房', 'new' => '新房', 'parking' => '车位', 'shop' => '商铺'];
    }

    public function getStatusList()
    {
        return ['1' => '在线', '0' => '下架'];
    }

    public function getHasElevatorList()
    {
        return ['0' => '无', '1' => '有'];
    }

    public function getVerifyStatusList()
    {
        return ['0' => '未核验', '1' => '已核验', '2' => '未通过'];
    }

    public function getIsRecommendList()
    {
        return ['0' => '否', '1' => '是'];
    }

    public function getCheckStatusList()
    {
        return ['0' => '未审核', '1' => '审核通过', '2' => '审核不通过', '9' => '编辑审核'];
    }

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }


    public function getHasElevatorTextAttr($value, $data)
    {
        $value = $value ?: ($data['has_elevator'] ?? '');
        $list = $this->getHasElevatorList();
        return $list[$value] ?? '';
    }


    public function getVerifyStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['verify_status'] ?? '');
        $list = $this->getVerifyStatusList();
        return $list[$value] ?? '';
    }


    public function getVerifyTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['verify_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getPublishTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['publish_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getIsRecommendTextAttr($value, $data)
    {
        $value = $value ?: ($data['is_recommend'] ?? '');
        $list = $this->getIsRecommendList();
        return $list[$value] ?? '';
    }

    protected function setVerifyTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setPublishTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function getCheckStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['check_status'] ?? '');
        $list = $this->getCheckStatusList();
        return $list[$value] ?? '';
    }

    public function community()
    {
        return $this->belongsTo('Community', 'community_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function agent() {
        return $this->belongsTo('Agent', 'agent_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


}
