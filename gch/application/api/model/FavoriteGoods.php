<?php
namespace app\api\model;

use think\Model;

class FavoriteGoods extends Model
{
    protected $name = 'favorite_goods';
    protected $pk = 'id';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = false;  // fy_favorite_goods 表无 update_time 字段,显式关闭

    public static function toggle($buyerId, $goodsId)
    {
        $row = self::where('buyer_id', $buyerId)->where('goods_id', $goodsId)->find();
        if ($row) {
            $row->delete();
            return [false, '已取消收藏'];
        }
        self::create(['buyer_id' => $buyerId, 'goods_id' => $goodsId]);
        return [true, '收藏成功'];
    }
}
