// utils/api.js — API 路径常量
export default {
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
  favorites:     '/api/favorites',

  // 批发商
  wholesalerLogin:        '/api/wholesaler/login',
  wholesalerLogout:       '/api/wholesaler/logout',
  wholesalerProfile:      '/api/wholesaler/profile',
  wholesalerChangePwd:    '/api/wholesaler/changePwd',
  wholesalerQrcode:       '/api/wholesaler/qrcode',
  wholesalerCategories:   '/api/wholesaler/categories',
  wholesalerGoodsList:    '/api/wholesaler/goods',
  wholesalerGoodsDetail:  id => `/api/wholesaler/goods/${id}`,
  wholesalerGoodsCreate:  '/api/wholesaler/goods/create',
  wholesalerGoodsStatus:  '/api/wholesaler/goods_status',  // 下划线!斜杠 pathinfo 会落到 goods()->create()
  wholesalerReservations:      '/api/wholesaler/reservations',
  wholesalerReservationDetail: id => `/api/wholesaler/reservation/${id}`,
  wholesalerReservationConfirm: '/api/wholesaler/reservation_confirm',  // 下划线!斜杠 pathinfo 会落到 reservation()->reservation_detail
  wholesalerReservationCancel:  '/api/wholesaler/reservation_cancel',

  // 登录态辅助
  pendingRoleKey: 'pending_role'   // 起始页 → 登录页 之间传递角色
}
