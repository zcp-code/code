<?php
namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 仓货盘 GCH 顶级目录
 *
 * FastAdmin 把 fy_auth_rule 里 gch/xxx 这种菜单渲染成 /admin/gch/xxx，
 * 被 ThinkPHP 解析为 Gch::xxx()。这里显式定义所有子模块入口，
 * 统一重定向到对应 controller。
 *
 * 注意：不能用 __call 兜底，因为某些 FastAdmin 内部反射可能绕过它。
 */
class Gch extends Backend
{
    protected $noNeedLogin = ['index'];

    public function index()
    {
        $this->redirect('dashboard/index');
    }

    public function dashboard()      { $this->redirect('dashboard/index'); }
    public function goods()           { $this->redirect('goods/index'); }
    public function buyer()           { $this->redirect('buyer/index'); }
    public function wholesaler()      { $this->redirect('wholesaler/index'); }
    public function reservation()     { $this->redirect('reservation/index'); }
    public function shop()            { $this->redirect('shop/index'); }
    public function product_category(){ $this->redirect('product_category/index'); }

    /**
     * 兜底：未来新增菜单时忘了加方法，自动重定向
     */
    public function __call($method, $args)
    {
        $this->redirect($method . '/index');
    }
}
