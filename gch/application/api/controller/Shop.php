<?php
namespace app\api\controller;

use app\api\model\Shop as ShopModel;

/**
 * 店铺(单数 controller,匹配前端 /api/shop/:id URL)
 * 兼容 pathinfo 解析时 controller=shop(单数)的情况
 */
class Shop extends ApiBase
{
    protected $requireLogin = false;

    public function index()
    {
        $page  = (int)$this->request->param('page', 1);
        $limit = (int)$this->request->param('limit', 20);
        $keyword = $this->request->param('keyword', '');

        $where = ['status' => 1];
        if (!empty($keyword)) {
            $where['name'] = ['like', '%' . $keyword . '%'];
        }
        $list  = ShopModel::where($where)->order('id desc')->page($page, $limit)->select();
        $total = ShopModel::where($where)->count();

        $todayStart = strtotime(date('Y-m-d'));

        // 当前用户(buyer)已收藏的店铺 id 集合(用于列表标记 favorited)
        $favShopIds = [];
        $token = $this->request->header('Token', '');
        if (!empty($token)) {
            $info = \extend\gch\Token::check($token, 'buyer');
            if ($info && !empty($info['user_id'])) {
                $buyerId = (int)$info['user_id'];
                $favShopIds = \think\Db::name('favorite_shop')
                    ->where('buyer_id', $buyerId)
                    ->column('shop_id');
            }
        }

        foreach ($list as &$shop) {
            $shop->today_count = \think\Db::name('goods')
                ->where('shop_id', $shop->id)
                ->where('publish_time', '>=', $todayStart)
                ->where('status', 1)
                ->count();
            $shop->favorited = in_array($shop->id, $favShopIds) ? 1 : 0;
        }
        unset($shop);

        return $this->success(['list' => $list, 'total' => $total]);
    }

    public function detail($id = 0)
    {
        $id = (int)$id;
        $shop = ShopModel::getDetail($id);
        if (!$shop) return $this->error('店铺不存在', 404);
        return $this->success($shop);
    }

    /**
     * 兜底:/api/shop/:id 被 pathinfo 解析为 action=数字
     */
    public function _empty($name)
    {
        if (ctype_digit((string)$name)) {
            return $this->detail((int)$name);
        }
        return $this->error('method not exists: ' . $name, 404);
    }
}
