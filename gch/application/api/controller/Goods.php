<?php
namespace app\api\controller;

use app\api\model\Goods as GoodsModel;

/**
 * 货盘（公开浏览）
 */
class Goods extends ApiBase
{
    protected $requireLogin = false;

    /**
     * 列表
     * GET /api/goods
     */
    public function index()
    {
        $page  = (int)$this->request->param('page', 1);
        $limit = (int)$this->request->param('limit', 20);
        $params = [
            'category_id' => $this->request->param('category_id', ''),
            'shop_id'     => $this->request->param('shop_id', ''),
            'keyword'     => $this->request->param('keyword', ''),
        ];

        $data = GoodsModel::getList($params, $page, $limit);
        // 关联图片和计算可预订数量
        foreach ($data['list'] as &$g) {
            $g->available = max(0, $g->total_stock - $g->reserved_quantity);
            $images = \app\api\model\GoodsImage::getByGoods($g->id);
            $g->images = $images;
            $g->cover = $images ? $images[0]->url : '';
        }

        return $this->success($data);
    }

    /**
     * 详情
     * GET /api/goods/{id}
     */
    public function detail($id = 0)
    {
        $id = (int)$id;
        $goods = GoodsModel::with(['images', 'shop', 'category'])->find($id);
        if (!$goods || $goods->status != 1) return $this->error('货盘不存在', 404);

        $goods->available = max(0, $goods->total_stock - $goods->reserved_quantity);
        return $this->success($goods);
    }

    /**
     * 兜底:URL /api/goods/13 被 pathinfo 解析为 action=13
     * application/route.php 数组路由未生效时走这里
     * 数字 action → 转发到 detail
     */
    public function _empty($name)
    {
        if (ctype_digit((string)$name)) {
            return $this->detail((int)$name);
        }
        return $this->error('method not exists: ' . $name, 404);
    }
}
