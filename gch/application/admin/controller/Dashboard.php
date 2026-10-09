<?php
namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;

/**
 * 控制台（统计）
 */
class Dashboard extends Backend
{
    protected $noNeedLogin = ['stats'];

    /**
     * 统计接口（供小程序端调用，公开）
     */
    public function stats()
    {
        $todayStart = strtotime(date('Y-m-d'));
        $todayEnd   = $todayStart + 86400;

        $data = [
            'today_reservation'  => Db::name('reservation')->whereBetween('createtime', [$todayStart, $todayEnd])->count(),
            'today_confirmed'    => Db::name('reservation')->where('status', 'confirmed')->whereBetween('confirm_time', [$todayStart, $todayEnd])->count(),
            'today_cancelled'    => Db::name('reservation')->where('status', 'cancelled')->whereBetween('cancel_time', [$todayStart, $todayEnd])->count(),
            'today_pending'      => Db::name('reservation')->where('status', 'pending')->whereBetween('createtime', [$todayStart, $todayEnd])->count(),
            'today_goods'        => Db::name('goods')->whereBetween('publish_time', [$todayStart, $todayEnd])->count(),
            'total_buyer'        => Db::name('buyer')->count(),
            'total_wholesaler'   => Db::name('wholesaler')->count(),
            'total_goods'        => Db::name('goods')->count(),
            'total_visitor'      => Db::name('visitor')->count(),
        ];
        $this->success('', null, $data);
    }

    /**
     * PC 后台首页
     */
    public function index()
    {
        $todayStart = strtotime(date('Y-m-d'));
        $todayEnd   = $todayStart + 86400;
        $weekStart  = $todayStart - 86400 * 6;   // 一周前(含今天共 7 天)

        // 1. KPI 数字卡片
        $this->view->assign([
            'today_reservation' => Db::name('reservation')->whereBetween('createtime', [$todayStart, $todayEnd])->count(),
            'today_confirmed'   => Db::name('reservation')->where('status', 'confirmed')->whereBetween('confirm_time', [$todayStart, $todayEnd])->count(),
            'today_cancelled'   => Db::name('reservation')->where('status', 'cancelled')->whereBetween('cancel_time', [$todayStart, $todayEnd])->count(),
            'today_pending'     => Db::name('reservation')->where('status', 'pending')->count(),
            'total_buyer'       => Db::name('buyer')->count(),
            'total_wholesaler'  => Db::name('wholesaler')->count(),
            'total_goods'       => Db::name('goods')->count(),
        ]);

        // 2. 一周内每日均价折线图(每天所有在售货品的平均单价)
        $weeklyAvg = [];
        $weeklyLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $dayStart = $todayStart - 86400 * $i;
            $dayEnd   = $dayStart + 86400;
            $weeklyLabels[] = date('m-d', $dayStart);
            $avg = Db::name('goods')
                ->whereBetween('publish_time', [$dayStart, $dayEnd])
                ->where('status', 1)
                ->avg('price');
            $weeklyAvg[] = $avg === null ? 0 : round((float)$avg, 2);
        }

        // 3. 一周内每种商品的均价柱状图(取最近 7 天 publish 的在售货品,按 name 分组)
        $goodsStats = Db::name('goods')
            ->field('name, AVG(price) as avg_price, COUNT(*) as cnt')
            ->whereBetween('publish_time', [$weekStart, $todayEnd])
            ->where('status', 1)
            ->group('name')
            ->order('cnt desc')
            ->limit(10)
            ->select();
        $goodsNames   = [];
        $goodsAvgList = [];
        foreach ($goodsStats as $g) {
            $goodsNames[]   = $g['name'];
            $goodsAvgList[] = round((float)$g['avg_price'], 2);
        }

        $this->view->assign([
            'weekly_labels' => json_encode($weeklyLabels),
            'weekly_avg'    => json_encode($weeklyAvg),
            'goods_names'   => json_encode($goodsNames),
            'goods_avg'     => json_encode($goodsAvgList),
        ]);

        return $this->view->fetch();
    }
}
