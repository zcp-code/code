<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Slide as SlideModel;
use app\common\model\Community as CommunityModel;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页数据
     */
    public function index()
    {
        // 轮播图（使用 slide 表，status=1 启用）
        $banners = SlideModel::where('status', 1)
            ->order('weigh', 'desc')
            ->order('id', 'desc')
            ->select();

        foreach ($banners as &$row) {
            if ($row['image']) {
                $row['image'] = cdnurl($row['image'], true);
            }
        }

        // 推荐二手房（type=sale, is_recommend=1）
        $saleHouses = \app\common\model\House::where('type', 'sale')
            ->where('is_recommend', 1)
            ->where('status', 1)
            ->where('check_status','in', [1,9])
            ->order('createtime', 'desc')
            ->limit(5)
            ->select();

        foreach ($saleHouses as &$house) {
            if ($house['cover_image']) {
                $house['cover_image'] = cdnurl($house['cover_image'], true);
            }
            if ($house['images']) {
                $images = explode(',', $house['images']);
                $house['cover_image_url'] = cdnurl($images[0] ?? '', true);
            }
        }

        // 推荐租房（type=rent, is_recommend=1）
        $rentHouses = \app\common\model\House::where('type', 'rent')
            ->where('is_recommend', 1)
            ->where('status', 1)
            ->where('check_status','in', [1,9])
            ->order('createtime', 'desc')
            ->limit(5)
            ->select();

        foreach ($rentHouses as &$house) {
            if ($house['cover_image']) {
                $house['cover_image'] = cdnurl($house['cover_image'], true);
            }
            if ($house['images']) {
                $images = explode(',', $house['images']);
                $house['cover_image_url'] = cdnurl($images[0] ?? '', true);
            }
        }

        // 推荐新房（type=new, is_recommend=1）
        $newHouses = \app\common\model\House::where('type', 'new')
            ->where('is_recommend', 1)
            ->where('status', 1)
            ->where('check_status','in', [1,9])
            ->order('createtime', 'desc')
            ->limit(5)
            ->select();

        foreach ($newHouses as &$house) {
            if ($house['cover_image']) {
                $house['cover_image'] = cdnurl($house['cover_image'], true);
            }
            if ($house['images']) {
                $images = explode(',', $house['images']);
                $house['cover_image_url'] = cdnurl($images[0] ?? '', true);
            }
        }

        // 推荐车位（type=parking, is_recommend=1）
        $parkingHouses = \app\common\model\House::where('type', 'parking')
            ->where('is_recommend', 1)
            ->where('status', 1)
            ->where('check_status','in', [1,9])
            ->order('createtime', 'desc')
            ->limit(5)
            ->select();

        foreach ($parkingHouses as &$house) {
            if ($house['cover_image']) {
                $house['cover_image'] = cdnurl($house['cover_image'], true);
            }
            if ($house['images']) {
                $images = explode(',', $house['images']);
                $house['cover_image_url'] = cdnurl($images[0] ?? '', true);
            }
        }

        // 推荐商铺（type=shop, is_recommend=1）
        $shopHouses = \app\common\model\House::where('type', 'shop')
            ->where('is_recommend', 1)
            ->where('status', 1)
            ->where('check_status','in', [1,9])
            ->order('createtime', 'desc')
            ->limit(5)
            ->select();

        foreach ($shopHouses as &$house) {
            if ($house['cover_image']) {
                $house['cover_image'] = cdnurl($house['cover_image'], true);
            }
            if ($house['images']) {
                $images = explode(',', $house['images']);
                $house['cover_image_url'] = cdnurl($images[0] ?? '', true);
            }
        }

        // 热门二手房（type=sale, 按 view_count 倒序，前6）
        $hotSaleHouses = \app\common\model\House::where('type', 'sale')
            ->where('status', 1)
            ->where('check_status','in', [1,9])
            ->order(['view_count'=>'desc','createtime'=>'desc'])
            ->limit(6)
            ->select();

        foreach ($hotSaleHouses as &$house) {
            if ($house['cover_image']) {
                $house['cover_image'] = cdnurl($house['cover_image'], true);
            }
            if ($house['images']) {
                $images = explode(',', $house['images']);
                $house['cover_image_url'] = cdnurl($images[0] ?? '', true);
            }
        }

        // 热门租房（type=rent, 按 view_count 倒序，前6）
        $hotRentHouses = \app\common\model\House::where('type', 'rent')
            ->where('status', 1)
            ->where('check_status','in', [1,9])
            ->order(['view_count'=>'desc','createtime'=>'desc'])
            ->limit(6)
            ->select();

        foreach ($hotRentHouses as &$house) {
            if ($house['cover_image']) {
                $house['cover_image_url'] = cdnurl($house['cover_image'], true);
            }
            if ($house['images']) {
                $images = explode(',', $house['images']);
                // $house['cover_image_url'] = cdnurl($images[0] ?? '', true);
            }
        }

        // 热门新房（type=new, 按 view_count 倒序，前6）
        $hotNewHouses = \app\common\model\House::where('type', 'new')
            ->where('status', 1)
            ->where('check_status','in', [1,9])
            ->order(['view_count'=>'desc','createtime'=>'desc'])
            ->limit(6)
            ->select();

        foreach ($hotNewHouses as &$house) {
            if ($house['cover_image']) {
                $house['cover_image_url'] = cdnurl($house['cover_image'], true);
            }
            if ($house['images']) {
                $images = explode(',', $house['images']);
                // $house['cover_image_url'] = cdnurl($images[0] ?? '', true);
            }
        }

        // 热门车位（type=parking, 按 view_count 倒序，前6）
        $hotParkingHouses = \app\common\model\House::where('type', 'parking')
            ->where('status', 1)
            ->where('check_status','in', [1,9])
            ->order(['view_count'=>'desc','createtime'=>'desc'])
            ->limit(6)
            ->select();

        foreach ($hotParkingHouses as &$house) {
            if ($house['cover_image']) {
                $house['cover_image_url'] = cdnurl($house['cover_image'], true);
            }
            if ($house['images']) {
                $images = explode(',', $house['images']);
                // $house['cover_image_url'] = cdnurl($images[0] ?? '', true);
            }
        }

        // 热门商铺（type=shop, 按 view_count 倒序，前6）
        $hotShopHouses = \app\common\model\House::where('type', 'shop')
            ->where('status', 1)
            ->where('check_status','in', [1,9])
            ->order(['view_count'=>'desc','createtime'=>'desc'])
            ->limit(6)
            ->select();

        foreach ($hotShopHouses as &$house) {
            if ($house['cover_image']) {
                $house['cover_image_url'] = cdnurl($house['cover_image'], true);
            }
            if ($house['images']) {
                $images = explode(',', $house['images']);
                // $house['cover_image_url'] = cdnurl($images[0] ?? '', true);
            }
        }


        $communities = CommunityModel::select();
        foreach ($communities as $com) {
            if ($com['cover_image']) {
                $com['cover_image'] = cdnurl($com['cover_image'], true);
            }
            $communityMap[$com['id']] = $com;
        }

        // 挂载小区信息到每个房源
        foreach ($saleHouses as &$house) {
            if (!empty($house['community_id']) && isset($communityMap[$house['community_id']])) {
                $house['community'] = $communityMap[$house['community_id']];
            }
        }
        foreach ($rentHouses as &$house) {
            if (!empty($house['community_id']) && isset($communityMap[$house['community_id']])) {
                $house['community'] = $communityMap[$house['community_id']];
            }
        }
        foreach ($newHouses as &$house) {
            if (!empty($house['community_id']) && isset($communityMap[$house['community_id']])) {
                $house['community'] = $communityMap[$house['community_id']];
            }
        }
        foreach ($hotSaleHouses as &$house) {
            if (!empty($house['community_id']) && isset($communityMap[$house['community_id']])) {
                $house['community'] = $communityMap[$house['community_id']];
            }
        }
        foreach ($hotRentHouses as &$house) {
            if (!empty($house['community_id']) && isset($communityMap[$house['community_id']])) {
                $house['community'] = $communityMap[$house['community_id']];
            }
        }
        foreach ($hotNewHouses as &$house) {
            if (!empty($house['community_id']) && isset($communityMap[$house['community_id']])) {
                $house['community'] = $communityMap[$house['community_id']];
            }
        }
        foreach ($parkingHouses as &$house) {
            if (!empty($house['community_id']) && isset($communityMap[$house['community_id']])) {
                $house['community'] = $communityMap[$house['community_id']];
            }
        }
        foreach ($shopHouses as &$house) {
            if (!empty($house['community_id']) && isset($communityMap[$house['community_id']])) {
                $house['community'] = $communityMap[$house['community_id']];
            }
        }
        foreach ($hotParkingHouses as &$house) {
            if (!empty($house['community_id']) && isset($communityMap[$house['community_id']])) {
                $house['community'] = $communityMap[$house['community_id']];
            }
        }
        foreach ($hotShopHouses as &$house) {
            if (!empty($house['community_id']) && isset($communityMap[$house['community_id']])) {
                $house['community'] = $communityMap[$house['community_id']];
            }
        }

        $this->success('', [
            'banners'         => $banners,
            'sale_houses'     => $saleHouses,
            'rent_houses'     => $rentHouses,
            'new_houses'      => $newHouses,
            'parking_houses'  => $parkingHouses,
            'shop_houses'     => $shopHouses,
            'hot_sale_houses' => $hotSaleHouses,
            'hot_rent_houses' => $hotRentHouses,
            'hot_new_houses'  => $hotNewHouses,
            'hot_parking_houses' => $hotParkingHouses,
            'hot_shop_houses' => $hotShopHouses,
        ]);
    }

    /**
     * 轮播图列表（独立接口）
     */
    public function banners()
    {
        $catId = $this->request->get('cat_id', 0);

        $where = ['status' => 1];
        if ($catId) {
            $where['cat_id'] = $catId;
        }

        $list = SlideModel::where($where)
            ->order('weigh', 'desc')
            ->order('id', 'desc')
            ->select();

        foreach ($list as &$row) {
            if ($row['image']) {
                $row['image'] = cdnurl($row['image'], true);
            }
        }

        $this->success('', $list);
    }

    /**
     * 轮播图/广告详情
     */
    public function bannerDetail()
    {
        $id = $this->request->get('id');
        if (!$id) {
            $this->error(__('Invalid parameters'));
        }

        $slide = SlideModel::get($id);
        if (!$slide) {
            $this->error('广告不存在');
        }

        if ($slide['image']) {
            $slide['image'] = cdnurl($slide['image'], true);
        }

        $this->success('', $slide);
    }
}
