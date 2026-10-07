<?php
namespace app\api\controller;

use app\api\model\ProductCategory;

/**
 * 货品分类（公开）
 */
class Categories extends ApiBase
{
    protected $requireLogin = false;

    /**
     * 列表（含每个分类下的资源数）
     * GET /api/categories
     */
    public function index()
    {
        $list = ProductCategory::listWithCount();
        return $this->success($list);
    }
}
