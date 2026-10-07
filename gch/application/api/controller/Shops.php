<?php
namespace app\api\controller;

use app\api\model\Shop as ShopModel;

/**
 * 店铺（公开浏览）
 */
class Shops extends ApiBase
{
    protected $requireLogin = false;

    /**
     * 列表
     * GET /api/shops
     */
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

        // 今日到货数（各店铺今日发布货盘数）
        $todayStart = strtotime(date('Y-m-d'));
        foreach ($list as &$shop) {
            $shop->today_count = \think\Db::name('goods')
                ->where('shop_id', $shop->id)
                ->where('publish_time', '>=', $todayStart)
                ->where('status', 1)
                ->count();
        }

        return $this->success(['list' => $list, 'total' => $total]);
    }

    /**
     * 详情
     * GET /api/shop/{id}  ← 前端 uniapp 用的路径
     * GET /api/shops/{id} ← 兼容 path 解析(被 pathinfo 解析为 Shops::shop(id))
     */
    public function detail($id = 0)
    {
        return $this->doDetail($id);
    }
    public function shop($id = 0)
    {
        return $this->doDetail($id);
    }
    private function doDetail($id)
    {
        $id = (int)$id;
        $shop = ShopModel::getDetail($id);
        if (!$shop) return $this->error('店铺不存在', 404);
        return $this->success($shop);
    }

    /**
     * 兜底:/api/shop/{id} 或 /api/shops/{id} 被 pathinfo 解析为 action=数字
     */
    public function _empty($name)
    {
        if (ctype_digit((string)$name)) {
            return $this->doDetail((int)$name);
        }
        return $this->error('method not exists: ' . $name, 404);
    }
}
