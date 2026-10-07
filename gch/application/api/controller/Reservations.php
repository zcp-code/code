<?php
namespace app\api\controller;

use app\api\model\Reservation as ReservationModel;

/**
 * 预订（采购商下单）
 */
class Reservations extends ApiBase
{
    protected $requireRole = 'buyer';

    /**
     * 创建预订（一键预订）
     * POST /api/reservation
     * 入参：goods_id, quantity
     */
    public function create()
    {
        $goodsId  = (int)$this->request->param('goods_id', 0);
        $quantity = (int)$this->request->param('quantity', 1);

        if (!$goodsId) return $this->error('缺少 goods_id');
        if ($quantity <= 0) return $this->error('数量必须大于 0');

        list($ok, $msg, $order) = ReservationModel::createOrder($goodsId, $this->user['user_id'], $quantity);
        if (!$ok) return $this->error($msg);

        return $this->success([
            'id'             => $order->id,
            'reservation_no' => $order->reservation_no,
            'status'         => $order->status,
        ], '预订成功，等待批发商确认');
    }

    /**
     * 我的订单列表
     * GET /api/reservations
     */
    public function myList()
    {
        $page  = (int)$this->request->param('page', 1);
        $limit = (int)$this->request->param('limit', 20);
        $status = $this->request->param('status', '');

        $data = ReservationModel::getList([
            'buyer_id' => $this->user['user_id'],
            'status'   => $status,
        ], $page, $limit);

        return $this->success($data);
    }

    /**
     * 详情
     * GET /api/reservation/{id}
     */
    public function detail($id = 0)
    {
        $id = (int)$id;
        $r = ReservationModel::get($id);
        if (!$r) return $this->error('订单不存在', 404);

        // 权限：仅本人
        if ($r->buyer_id != $this->user['user_id']) {
            return $this->error('无权访问', 403);
        }

        $r->shop;
        return $this->success($r);
    }

    /**
     * 取消预订
     * POST /api/reservation/cancel
     * 入参：id, reason
     */
    public function cancel()
    {
        $id = (int)$this->request->param('id', 0);
        $reason = $this->request->param('reason', '');

        if (!$id) return $this->error('缺少 id');

        $r = ReservationModel::get($id);
        if (!$r) return $this->error('订单不存在', 404);
        if ($r->buyer_id != $this->user['user_id']) {
            return $this->error('无权操作', 403);
        }

        list($ok, $msg) = $r->cancel($reason, 'buyer');
        if (!$ok) return $this->error($msg);
        return $this->success(null, '已取消');
    }

    /**
     * 兜底:URL /api/reservation/* 被 pathinfo 解析为 action=*
     * application/route.php 数组路由未生效时走这里
     *  - /api/reservations       (GET) → myList
     *  - /api/reservation        (POST) → create
     *  - /api/reservation/13     → detail(13)
     *  - /api/reservation/cancel (POST) → cancel
     */
    public function _empty($name)
    {
        // POST /api/reservation/cancel
        if ($name === 'cancel' && $this->request->isPost()) {
            return $this->cancel();
        }
        // /api/reservation/13
        if (ctype_digit((string)$name)) {
            return $this->detail((int)$name);
        }
        // POST /api/reservation → 下单
        if ($this->request->isPost()) {
            return $this->create();
        }
        // GET /api/reservations → 我的预订列表
        return $this->myList();
    }
}
