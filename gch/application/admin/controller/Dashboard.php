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

        // 2. 今日果仓行情:按"品名"分组聚合今日发布且在售的货品
        //   字段:品名 / 均价 / 最低 / 最高 / 款数 / 单位(取出现最多的)
        $rows = Db::name('goods')
            ->field('name, AVG(price) as avg_price, MIN(price) as min_price, MAX(price) as max_price, COUNT(*) as cnt')
            ->whereBetween('publish_time', [$todayStart, $todayEnd])
            ->where('status', 1)
            ->group('name')
            ->order('cnt desc, avg_price desc')
            ->select();

        $todayMarket = [];
        foreach ($rows as $r) {
            // 取该品名今日最常用的单位(简单取一个货品的 unit)
            $unit = Db::name('goods')
                ->where('name', $r['name'])
                ->whereBetween('publish_time', [$todayStart, $todayEnd])
                ->where('status', 1)
                ->order('id desc')
                ->value('unit') ?: '';
            $todayMarket[] = [
                'name'      => $r['name'],
                'unit'      => $unit,
                'avg_price' => round((float)$r['avg_price'], 2),
                'min_price' => round((float)$r['min_price'], 2),
                'max_price' => round((float)$r['max_price'], 2),
                'cnt'       => (int)$r['cnt'],
            ];
        }

        // 今日在售总款数(用于"今日新增"汇总)
        $todayTotal = Db::name('goods')
            ->whereBetween('publish_time', [$todayStart, $todayEnd])
            ->where('status', 1)
            ->count();

        $this->view->assign([
            'today_market' => $todayMarket,
            'today_total'  => $todayTotal,
        ]);

        return $this->view->fetch();
    }
}
