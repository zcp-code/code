<?php
namespace app\api\controller;

use app\api\model\CallLog;
use app\api\model\Shop;
use app\api\model\Wholesaler;

/**
 * 拨打电话（记录日志）
 */
class Call extends ApiBase
{
    protected $requireLogin = false;

    /**
     * 拨打批发商电话
     * POST /api/call
     * 入参：shop_id 或 wholesaler_id
     */
    public function dial()
    {
        $shopId       = (int)$this->request->param('shop_id', 0);
        $wholesalerId = (int)$this->request->param('wholesaler_id', 0);

        $phone = '';
        if ($shopId) {
            $shop = Shop::get($shopId);
            if (!$shop) return $this->error('店铺不存在');
            $phone = $shop->contact_phone;
        } elseif ($wholesalerId) {
            $wh = Wholesaler::get($wholesalerId);
            if (!$wh) return $this->error('批发商不存在');
            $phone = $wh->mobile ?: $wh->contact_phone;
        } else {
            return $this->error('缺少 shop_id 或 wholesaler_id');
        }

        if (empty($phone)) return $this->error('该对象未填写电话');

        // 记录日志
        $role = $this->user ? $this->user['role'] : 'guest';
        $userId = $this->user ? (int)$this->user['user_id'] : null;
        CallLog::record($role, $userId, $shopId, $wholesalerId, $phone);

        return $this->success(['phone' => $phone], '拨号成功');
    }
}
