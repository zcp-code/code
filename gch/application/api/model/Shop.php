<?php
namespace app\api\model;

use think\Model;

class Shop extends Model
{
    protected $name = 'shop';
    protected $pk = 'id';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 详情（含货盘列表）
     */
    public static function getDetail($id)
    {
        $shop = self::get($id);
        if (!$shop || $shop->status != 1) return null;
        $goods = Goods::where('shop_id', $id)
            ->where('status', 1)
            ->order('publish_time desc')
            ->select();
        $shop->goods = $goods;
        return $shop;
    }
}
