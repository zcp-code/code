// utils/api.js — API 路径常量集中管理

module.exports = {
  // 公共
  wxLogin:        '/api/wxlogin',
  commonConfig:   '/api/common/config',
  commonUpload:   '/api/common/upload',

  // 公开浏览
  goodsList:      '/api/goods',
  goodsDetail:    id => `/api/goods/${id}`,
  categoryList:   '/api/categories',
  shopList:       '/api/shops',
  shopDetail:     id => `/api/shop/${id}`,
  callDial:       '/api/call/dial',

  // 游客
  visitorProfile: '/api/visitor/profile',
  visitorLogout:  '/api/visitor/logout',

  // 采购商
  buyerLogin:     '/api/buyer/login',
  buyerLogout:    '/api/buyer/logout',
  buyerProfile:   '/api/buyer/profile',
  buyerChangePwd: '/api/buyer/changePwd',

  // 预订
  reservationCreate:  '/api/reservation',
  reservationList:    '/api/reservations',
  reservationDetail:  id => `/api/reservation/${id}`,
  reservationCancel:  '/api/reservation/cancel',

  // 收藏
  favoriteGoods: '/api/favorite/goods',
  favoriteShop:  '/api/favorite/shop',
  favorites:     '/api/favorites'
};
