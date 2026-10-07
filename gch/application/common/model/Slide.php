<?php

namespace app\common\model;

use think\Model;


class Slide extends Model
{

    

    

    // 表名
    protected $name = 'slide';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            if(empty($row[$pk])||$row[$pk]==0){
                $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
            }
        });
    }

    
    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function slidecat()
    {
        return $this->belongsTo('app\common\model\Slidecat', 'cat_id', 'cid', [], 'LEFT')->setEagerlyType(0);
    }

    public function getList($condition, $page = 1, $limit = 10, $order = 'weigh desc'){

        $data = collection(self::where($condition)
            ->where("status",1)
            ->page($page)
            ->limit($limit)
            ->order($order)
            ->select())->toArray();
        foreach ($data as &$val){
            if(!empty($val['image'])){
                $val['image'] = cdnurl($val['image'], true);
//                if(!empty($val['url'])){
//                    if(substr($val['url'],0,4) == "http"){
//                        $val['wap_url'] = $val['url'];
//                    } else {
//                        $urlItem = changeUrl($val['url']);
//                        $val['wap_url'] = $urlItem['wapUrl'];
//                    }
//                }
            }
        }

        return $data;
    }
}
