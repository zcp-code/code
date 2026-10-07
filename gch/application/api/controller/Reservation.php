<?php
namespace app\api\controller;

use app\api\model\Reservation as ReservationModel;

/**
 * 预订(单数 controller,匹配前端 /api/reservation/* URL)
 * 兼容 pathinfo 解析时 controller=reservation(单数)的情况
 */
class Reservation extends ApiBase
{
    protected $requireRole = 'buyer';

    /**
     * GET /api/reservations — 买家自己的预订列表(规格 §6.4)
     * POST /api/reservation — 下单(route.php 规则未生效时 fallback 进来)
     */
    public function index()
    {
        // TP5.1 默认 action=index,POST 也会走这里;
        // route.php 规则不带 method 前缀只匹配 GET,所以 POST 必须在这里转发到 create()
        if ($this->request->isPost()) {
            return $this->create();
        }

        $page  = (int)$this->request->param('page', 1);
        $limit = (int)$this->request->param('limit', 20);
        $status = $this->request->param('status', '');

        $data = ReservationModel::getList([
            'buyer_id' => $this->user['user_id'],
            'status'    => $status,
        ], $page, $limit);

        return $this->success($data);
    }

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

    public function detail($id = 0)
    {
        $id = (int)$id;
        $r = ReservationModel::get($id);
        if (!$r) return $this->error('订单不存在', 404);
        if ($r->buyer_id != $this->user['user_id']) {
            return $this->error('无权访问', 403);
        }
        $r->shop;
        return $this->success($r);
    }

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
     * 兜底:
     *   - /api/reservation/13 (数字) → detail(13)
     *   - /api/reservation/cancel (POST) → cancel()
     *   - /api/reservation (POST,action=index 默认) → create()
     */
    public function _empty($name)
    {
        if (ctype_digit((string)$name)) {
            return $this->detail((int)$name);
        }
        if ($name === 'cancel' && $this->request->isPost()) {
            return $this->cancel();
        }
        // POST 默认 → create,GET 默认 → index(我的列表)
        if ($this->request->isPost()) {
            return $this->create();
        }
        return $this->index();
    }
}
