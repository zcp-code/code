// pages/goods/goods.js
const { http, api } = require('../../utils/request.js');

Page({
  data: {
    id: 0,
    goods: null,
    images: [],
    favorited: false
  },

  onLoad(query) {
    this.setData({ id: query.id });
    this.load();
  },

  async load() {
    wx.showLoading({ title: '加载中' });
    try {
      const goods = await http.get(api.goodsDetail(this.data.id), {}, { hideError: true });
      this.setData({
        goods,
        images: goods.images || []
      });
    } catch (e) {
      wx.showToast({ title: e.message, icon: 'none' });
    }
    wx.hideLoading();
  },

  // 收藏/取消
  async toggleFav() {
    const app = getApp();
    if (!app.globalData.token) {
      wx.navigateTo({ url: '/pages/login/login' });
      return;
    }
    try {
      const data = await http.post(api.favoriteGoods, { goods_id: this.data.id }, { hideError: true });
      this.setData({ favorited: data.favored });
      wx.showToast({ title: data.favored ? '已收藏' : '已取消', icon: 'none' });
    } catch (e) {}
  },

  // 一键预订
  goReserve() {
    const app = getApp();
    if (!app.globalData.token || app.globalData.role !== 'buyer') {
      wx.navigateTo({ url: '/pages/login/login' });
      return;
    }
    wx.navigateTo({
      url: `/pages/reserve/reserve?id=${this.data.id}`
    });
  },

  // 打电话给批发商
  async callWholesaler() {
    try {
      const data = await http.post(api.callDial, { shop_id: this.data.goods.shop_id }, { hideError: true });
      wx.makePhoneCall({ phoneNumber: data.phone });
    } catch (e) {}
  }
});
