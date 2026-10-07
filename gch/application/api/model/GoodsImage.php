<?php
namespace app\api\model;

use think\Model;

class GoodsImage extends Model
{
    protected $name = 'goods_image';
    protected $pk = 'id';

    /**
     * 根据 goods_id 获取图片
     */
    public static function getByGoods($goodsId)
    {
        return self::where('goods_id', $goodsId)->order('sort asc')->select();
    }
}
