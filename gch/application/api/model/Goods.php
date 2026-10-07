<?php
namespace app\api\model;

use extend\gch\Inventory;
use think\Model;

class Goods extends Model
{
    protected $name = 'goods';
    protected $pk = 'id';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 可预订数量
     */
    public function getAvailableAttr($value, $data)
    {
        return Inventory::available($data['total_stock'], $data['reserved_quantity']);
    }

    /**
     * 关联图片
     */
    public function images()
    {
        return $this->hasMany('GoodsImage', 'goods_id', 'id')->order('sort asc');
    }

    /**
     * 关联店铺
     */
    public function shop()
    {
        return $this->belongsTo('Shop', 'shop_id', 'id');
    }

    /**
     * 关联批发商
     */
    public function wholesaler()
    {
        return $this->belongsTo('Wholesaler', 'wholesaler_id', 'id');
    }

    /**
     * 关联分类
     */
    public function category()
    {
        return $this->belongsTo('ProductCategory', 'category_id', 'id');
    }

    /**
     * 详情（带图片和店铺）
     */
    public function getDetail($id)
    {
        $goods = self::with(['images', 'shop'])->where('status', 1)->find($id);
        return $goods;
    }

    /**
     * 分页列表
     */
    public static function getList($params = [], $page = 1, $limit = 20)
    {
        $where = ['g.status' => 1];
        if (!empty($params['category_id'])) {
            $where['g.category_id'] = $params['category_id'];
        }
        if (!empty($params['shop_id'])) {
            $where['g.shop_id'] = $params['shop_id'];
        }
        if (!empty($params['keyword'])) {
            $where['g.name'] = ['like', '%' . $params['keyword'] . '%'];
        }
        $alias = 'g';
        $join  = [
            ['shop s', 's.id = g.shop_id', 'LEFT'],
        ];
        $field = 'g.*, s.name as shop_name';

        $list  = self::alias($alias)->join($join)
            ->where($where)
            ->order('g.publish_time desc, g.id desc')
            ->page($page, $limit)
            ->field($field)
            ->select();
        $total = self::alias($alias)->join($join)->where($where)->count();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 生成货盘编号
     */
    public static function genNo()
    {
        return 'G' . date('YmdHis') . str_pad((string)mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}
