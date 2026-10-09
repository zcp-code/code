<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// ============================================================
// 安全: /admin/... 重定向到随机入口 MaQbwKnULZ.php
// 消除 FastAdmin 警告 + 隐藏真实后台入口防止 /admin/ 被探测
// FastAdmin 推荐做法: 用改名后的入口访问后台(public/MaQbwKnULZ.php)
// ============================================================
\think\Route::any('admin', function () {
    header('Location: /MaQbwKnULZ.php', true, 302);
    exit;
});
\think\Route::any('admin/:any', function () {
    $newUrl = preg_replace('#^/?admin/#', '/MaQbwKnULZ.php/', $_SERVER['REQUEST_URI'], 1);
    header('Location: ' . $newUrl, true, 302);
    exit;
});

return [
    //别名配置,别名只能是映射到控制器且访问时必须加上请求的方法
    '__alias__'   => [
    ],
    //变量规则
    '__pattern__' => [
    ],

    // 经纪人房源接口
    'api/agent/house/list'  => 'api/agent/house/list',
    'api/agent/house/detail' => 'api/agent/house/detail',
    'api/agent/house/add'   => 'api/agent/house/add',
    'api/agent/house/edit'  => 'api/agent/house/edit',
    'api/agent/house/del'   => 'api/agent/house/del',

    // 房源接口
    'api/house/list'         => 'api/house/list',
    'api/house/detail'       => 'api/house/detail',
    'api/house/add'          => 'api/house/add',
    'api/house/edit'         => 'api/house/edit',
    'api/house/delete'       => 'api/house/delete',
    'api/house/updateStatus' => 'api/house/updateStatus',

    // ============================================================
    // 仓货盘小程序 GCH API 路由
    // 说明：模块级路由 application/api/route.php 不会被自动加载，
    // 所以必须在这里手动声明。
    // ============================================================

    // 公共（无需登录）
    'api/wxlogin'                       => 'api/WxLogin/index',
    'api/common/config'                 => 'api/Common/config',
    'api/common/upload'                 => 'api/Common/upload',

    // 公开浏览（无需登录）
    'api/categories'                    => 'api/Category/index',
    'api/goods'                         => 'api/Goods/index',
    'api/goods/:id'                     => 'api/Goods/detail',
    'api/shops'                         => 'api/Shop/index',
    'api/shop/:id'                      => 'api/Shop/detail',
    'api/call/dial'                     => 'api/Call/dial',

    // 游客 API
    'api/visitor/profile'               => 'api/Visitor/profile',
    'api/visitor/logout'                => 'api/Visitor/logout',

    // 采购商 API
    'api/buyer/login'                   => 'api/Buyer/login',
    'api/buyer/logout'                  => 'api/Buyer/logout',
    'api/buyer/profile'                 => 'api/Buyer/profile',
    'api/buyer/changePwd'               => 'api/Buyer/changePwd',

    // 采购商预订
    'api/reservation'                   => 'api/Reservation/create',
    'api/reservations'                  => 'api/Reservation/myList',
    'api/reservation/:id'               => 'api/Reservation/detail',
    'api/reservation/cancel'            => 'api/Reservation/cancel',

    // 采购商收藏
    'api/favorite/goods'                => 'api/Favorite/goods',
    'api/favorite/shop'                 => 'api/Favorite/shop',
    'api/favorites'                     => 'api/Favorite/index',

    // 批发商 API
    'api/wholesaler/login'              => 'api/Wholesaler/login',
    'api/wholesaler/logout'             => 'api/Wholesaler/logout',
    'api/wholesaler/profile'            => 'api/Wholesaler/profile',
    'api/wholesaler/changePwd'          => 'api/Wholesaler/changePwd',
    'api/wholesaler/qrcode'             => 'api/Wholesaler/qrcode',

    // 批发商货盘 + 预订(已合并到 Wholesaler.php,合并避免 pathinfo 路由覆盖)
    'api/wholesaler/categories'             => 'api/Wholesaler/categories',
    'api/wholesaler/goods'                  => 'api/Wholesaler/index',
    'api/wholesaler/goods/:id'              => 'api/Wholesaler/detail',
    'api/wholesaler/goods/create'           => 'api/Wholesaler/create',
    'api/wholesaler/goods/status'           => 'api/Wholesaler/status',

    'api/wholesaler/reservations'           => 'api/Wholesaler/reservations',
    'api/wholesaler/reservation/:id'        => 'api/Wholesaler/reservation_detail',
    'api/wholesaler/reservation/confirm'    => 'api/Wholesaler/reservation_confirm',
    'api/wholesaler/reservation/cancel'     => 'api/Wholesaler/reservation_cancel',

//        域名绑定到模块
//        '__domain__'  => [
//            'admin' => 'admin',
//            'api'   => 'api',
//        ],
];
