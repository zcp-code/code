<?php
namespace app\api\model;

use extend\gch\Inventory;
use think\Db;
use think\Model;

class Reservation extends Model
{
    protected $name = 'reservation';
    protected $pk = 'id';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // === 关联:用于 detail() 取店铺/批发商/买家信息 ===
    public function shop()       { return $this->belongsTo('Shop',       'shop_id',       'id'); }
    public function wholesaler() { return $this->belongsTo('Wholesaler', 'wholesaler_id', 'id'); }
    public function buyer()      { return $this->belongsTo('Buyer',      'buyer_id',      'id'); }

    /**
     * 创建预订（带库存锁）
     *
     * @param int    $goodsId
     * @param int    $buyerId
     * @param int    $quantity
     * @return array [success, msg, reservation]
     */
    public static function createOrder($goodsId, $buyerId, $quantity)
    {
        if ($quantity <= 0) return [false, '数量必须大于 0', null];

        return Inventory::lock('goods_' . $goodsId, function () use ($goodsId, $buyerId, $quantity) {
            $goods = Goods::get($goodsId);
            if (!$goods) return [false, '货盘不存在', null];
            if ($goods->status != 1) return [false, '货盘已下架', null];

            $available = Inventory::available($goods->total_stock, $goods->reserved_quantity);
            if ($quantity > $available) {
                return [false, '库存不足，可预订数量：' . $available, null];
            }

            Db::startTrans();
            try {
                // 锁定库存
                $goods->reserved_quantity = $goods->reserved_quantity + $quantity;
                $goods->save();

                // 创建预订
                $order = self::create([
                    'reservation_no' => self::genNo(),
                    'goods_id'       => $goodsId,
                    'buyer_id'       => $buyerId,
                    'wholesaler_id'  => $goods->wholesaler_id,
                    'shop_id'        => $goods->shop_id,
                    'goods_name'     => $goods->name,
                    'price'          => $goods->price,
                    'unit'           => $goods->unit,
                    'quantity'       => $quantity,
                    'status'         => 'pending',
                    'reserve_time'   => time(),
                ]);

                Db::commit();
                return [true, 'ok', $order];
            } catch (\Exception $e) {
                Db::rollback();
                return [false, '系统异常：' . $e->getMessage(), null];
            }
        });
    }

    /**
     * 确认预订
     */
    public function confirm()
    {
        if ($this->status != 'pending') {
            return [false, '当前状态不可确认'];
        }
        $this->status = 'confirmed';
        $this->confirm_time = time();
        $this->save();
        return [true, 'ok'];
    }

    /**
     * 取消预订（释放库存）
     */
    public function cancel($reason = '', $role = 'buyer')
    {
        if ($this->status == 'cancelled') {
            return [false, '已取消'];
        }
        if ($this->status == 'confirmed') {
            return [false, '已确认的预订不可取消'];
        }

        return Inventory::lock('goods_' . $this->goods_id, function () use ($reason, $role) {
            Db::startTrans();
            try {
                // 释放库存
                $goods = Goods::get($this->goods_id);
                if ($goods) {
                    $goods->reserved_quantity = max(0, $goods->reserved_quantity - $this->quantity);
                    $goods->save();
                }
                $this->status = 'cancelled';
                $this->cancel_time = time();
                $this->cancel_reason = $reason;
                $this->cancel_role = $role;
                $this->save();
                Db::commit();
                return [true, 'ok'];
            } catch (\Exception $e) {
                Db::rollback();
                return [false, $e->getMessage()];
            }
        });
    }

    /**
     * 生成预订编号
     */
    private static function genNo()
    {
        return 'R' . date('YmdHis') . str_pad((string)mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * 列表（带商品信息）
     */
    public static function getList($filters, $page = 1, $limit = 20)
    {
        $where = [];
        if (!empty($filters['buyer_id'])) {
            $where['r.buyer_id'] = $filters['buyer_id'];
        }
        if (!empty($filters['wholesaler_id'])) {
            $where['r.wholesaler_id'] = $filters['wholesaler_id'];
        }
        if (!empty($filters['status'])) {
            $where['r.status'] = $filters['status'];
        }
        if (!empty($filters['keyword'])) {
            $where['r.goods_name'] = ['like', '%' . $filters['keyword'] . '%'];
        }
        $prefix = config('database.prefix');
        $join    = [
            [$prefix.'shop s',       's.id = r.shop_id',       'LEFT'],
            [$prefix.'wholesaler w', 'w.id = r.wholesaler_id', 'LEFT'],
            [$prefix.'buyer b',      'b.id = r.buyer_id',      'LEFT'],
        ];
        $list = self::alias('r')
            ->join($join)
            ->where($where)
            ->order('r.id desc')
            ->page($page, $limit)
            ->field('r.*, s.name as shop_name, w.real_name as wholesaler_name, b.real_name as buyer_name')
            ->select();
        $total = self::alias('r')->join($join)->where($where)->count();
        return ['list' => $list, 'total' => $total];
    }
}
