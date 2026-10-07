<?php
/**
 * PC 后台路由（FastAdmin 默认 + 业务模块）
 *
 * 后台通过 http://your-domain/admin/ 访问
 * FastAdmin 默认会自动注册所有 application/admin/controller/ 下的控制器
 * 这里只需注册需要自定义的路由
 */

use think\Route;

// 控制台
Route::get('dashboard',          'admin/Dashboard/index');
Route::get('dashboard/stats',    'admin/Dashboard/stats');

// 货品分类
Route::any('product_category/index',    'admin/ProductCategory/index');
Route::any('product_category/add',      'admin/ProductCategory/add');
Route::any('product_category/edit/:id', 'admin/ProductCategory/edit');
Route::any('product_category/del',      'admin/ProductCategory/del');

// 店铺
Route::any('shop/index',    'admin/Shop/index');
Route::any('shop/add',      'admin/Shop/add');
Route::any('shop/edit/:id', 'admin/Shop/edit');
Route::any('shop/del',      'admin/Shop/del');

// 货盘
Route::any('goods/index',       'admin/Goods/index');
Route::any('goods/edit/:id',    'admin/Goods/edit');
Route::any('goods/status',      'admin/Goods/status');
Route::any('goods/del',         'admin/Goods/del');

// 预订
Route::any('reservation/index',     'admin/Reservation/index');
Route::any('reservation/edit/:id',  'admin/Reservation/edit');
Route::any('reservation/del',       'admin/Reservation/del');
Route::any('reservation/export',    'admin/Reservation/export');

// 采购商
Route::any('buyer/index',       'admin/Buyer/index');
Route::any('buyer/add',         'admin/Buyer/add');
Route::any('buyer/edit/:id',    'admin/Buyer/edit');
Route::any('buyer/del',         'admin/Buyer/del');
Route::any('buyer/status',      'admin/Buyer/status');
Route::any('buyer/resetPwd',    'admin/Buyer/resetPwd');

// 批发商
Route::any('wholesaler/index',       'admin/Wholesaler/index');
Route::any('wholesaler/add',         'admin/Wholesaler/add');
Route::any('wholesaler/edit/:id',    'admin/Wholesaler/edit');
Route::any('wholesaler/del',         'admin/Wholesaler/del');
Route::any('wholesaler/status',      'admin/Wholesaler/status');
Route::any('wholesaler/resetPwd',    'admin/Wholesaler/resetPwd');

return [
    // FastAdmin 路由已经通过 Route::any() 注册
];
