<?php
namespace app\api\controller;

use app\api\model\FavoriteGoods;
use app\api\model\FavoriteShop;

/**
 * 收藏（采购商）
 */
class Favorites extends ApiBase
{
    protected $requireRole = 'buyer';

    /**
     * 收藏/取消货品
     * POST /api/favorite/goods
     * 入参：goods_id
     */
    public function goods()
    {
        $goodsId = (int)$this->request->param('goods_id', 0);
        if (!$goodsId) return $this->error('缺少 goods_id');

        list($favored, $msg) = FavoriteGoods::toggle($this->user['user_id'], $goodsId);
        return $this->success(['favored' => $favored], $msg);
    }

    /**
     * 收藏/取消店铺
     * POST /api/favorite/shop
     */
    public function shop()
    {
        $shopId = (int)$this->request->param('shop_id', 0);
        if (!$shopId) return $this->error('缺少 shop_id');

        list($favored, $msg) = FavoriteShop::toggle($this->user['user_id'], $shopId);
        return $this->success(['favored' => $favored], $msg);
    }

    /**
     * 我的收藏列表
     * GET /api/favorites?type=goods|shop
     */
    public function index()
    {
        $type = $this->request->param('type', 'goods');
        $page  = (int)$this->request->param('page', 1);
        $limit = (int)$this->request->param('limit', 20);

        if ($type == 'shop') {
            $list = \think\Db::name('favorite_shop')
                ->alias('fs')
                ->join('shop s', 's.id = fs.shop_id')
                ->where('fs.buyer_id', $this->user['user_id'])
                ->field('fs.createtime as favorite_time, s.*')
                ->order('fs.id desc')
                ->page($page, $limit)
                ->select();
            $total = \think\Db::name('favorite_shop')->where('buyer_id', $this->user['user_id'])->count();
        } else {
            $list = \think\Db::name('favorite_goods')
                ->alias('fg')
                ->join('goods g', 'g.id = fg.goods_id')
                ->join('shop s', 's.id = g.shop_id')
                ->where('fg.buyer_id', $this->user['user_id'])
                ->field('fg.createtime as favorite_time, g.*, s.name as shop_name')
                ->order('fg.id desc')
                ->page($page, $limit)
                ->select();
            $total = \think\Db::name('favorite_goods')->where('buyer_id', $this->user['user_id'])->count();
        }
        return $this->success(['list' => $list, 'total' => $total]);
    }
}
