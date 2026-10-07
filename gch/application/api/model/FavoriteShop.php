<?php
namespace app\api\model;

use think\Model;

class FavoriteShop extends Model
{
    protected $name = 'favorite_shop';
    protected $pk = 'id';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = false;  // fy_favorite_shop 表无 update_time 字段,显式关闭

    public static function toggle($buyerId, $shopId)
    {
        $row = self::where('buyer_id', $buyerId)->where('shop_id', $shopId)->find();
        if ($row) {
            $row->delete();
            return [false, '已取消收藏'];
        }
        self::create(['buyer_id' => $buyerId, 'shop_id' => $shopId]);
        return [true, '收藏成功'];
    }
}
