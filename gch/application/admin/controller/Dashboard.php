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

        $this->view->assign([
            'today_reservation' => Db::name('reservation')->whereBetween('createtime', [$todayStart, $todayEnd])->count(),
            'today_confirmed'   => Db::name('reservation')->where('status', 'confirmed')->whereBetween('confirm_time', [$todayStart, $todayEnd])->count(),
            'today_cancelled'   => Db::name('reservation')->where('status', 'cancelled')->whereBetween('cancel_time', [$todayStart, $todayEnd])->count(),
            'today_pending'     => Db::name('reservation')->where('status', 'pending')->count(),
            'total_buyer'       => Db::name('buyer')->count(),
            'total_wholesaler'  => Db::name('wholesaler')->count(),
            'total_goods'       => Db::name('goods')->count(),
        ]);

        return $this->view->fetch();
    }
}
