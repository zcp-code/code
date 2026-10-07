<?php
/**
 * 小程序 API 路由
 *
 * 路由规则：'路由' => '控制器/方法'
 * 注意：所有路由不带前缀（pathinfo 模式）
 * 例如：POST /api/wxlogin  →  api/WxLogin/index
 */

use think\Route;

// ========== 公共（无需登录） ==========
Route::post('wxlogin',           'api/WxLogin/index');
Route::get('common/config',      'api/Common/config');
Route::post('common/upload',     'api/Common/upload');

// ========== 公开浏览（无需登录） ==========
Route::get('categories',         'api/Category/index');
Route::get('goods',              'api/Goods/index');
Route::get('goods/:id',          'api/Goods/detail');
Route::get('shops',              'api/Shop/index');
Route::get('shop/:id',           'api/Shop/detail');
Route::post('call/dial',         'api/Call/dial');

// ========== 游客 API ==========
Route::get('visitor/profile',    'api/Visitor/profile');
Route::post('visitor/logout',    'api/Visitor/logout');

// ========== 采购商 API ==========
Route::post('buyer/login',       'api/Buyer/login');
Route::post('buyer/logout',      'api/Buyer/logout');
Route::get('buyer/profile',      'api/Buyer/profile');
Route::post('buyer/changePwd',   'api/Buyer/changePwd');

// 采购商预订
Route::post('reservation',              'api/Reservation/create');
Route::get('reservations',              'api/Reservation/myList');
Route::get('reservation/:id',           'api/Reservation/detail');
Route::post('reservation/cancel',       'api/Reservation/cancel');

// 采购商收藏
Route::post('favorite/goods',           'api/Favorite/goods');
Route::post('favorite/shop',            'api/Favorite/shop');
Route::get('favorites',                 'api/Favorite/index');

// ========== 批发商 API ==========
Route::post('wholesaler/login',         'api/Wholesaler/login');
Route::post('wholesaler/logout',        'api/Wholesaler/logout');
Route::get('wholesaler/profile',        'api/Wholesaler/profile');
Route::post('wholesaler/changePwd',     'api/Wholesaler/changePwd');
Route::get('wholesaler/qrcode',         'api/Wholesaler/qrcode');

// 批发商货盘
Route::get('wholesaler/categories',     'api/WholesalerGoods/categories');
Route::get('wholesaler/goods',          'api/WholesalerGoods/index');
Route::get('wholesaler/goods/:id',      'api/WholesalerGoods/detail');
Route::post('wholesaler/goods/create',  'api/WholesalerGoods/create');
Route::post('wholesaler/goods/status',  'api/WholesalerGoods/status');

// 批发商预订
Route::get('wholesaler/reservations',       'api/WholesalerReservation/index');
Route::get('wholesaler/reservation/:id',    'api/WholesalerReservation/detail');
Route::post('wholesaler/reservation/confirm','api/WholesalerReservation/confirm');
Route::post('wholesaler/reservation/cancel','api/WholesalerReservation/cancel');
