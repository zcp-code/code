// pages/shop/shop.js
const { http, api } = require('../../utils/request.js');

Page({
  data: {
    id: 0,
    shop: null
  },

  onLoad(query) {
    this.setData({ id: query.id });
    this.load();
  },

  async load() {
    try {
      const shop = await http.get(api.shopDetail(this.data.id), {}, { hideError: true });
      this.setData({ shop });
    } catch (e) {}
  },

  async callShop() {
    try {
      const data = await http.post(api.callDial, { shop_id: this.data.id }, { hideError: true });
      wx.makePhoneCall({ phoneNumber: data.phone });
    } catch (e) {}
  },

  async toggleFav() {
    const app = getApp();
    if (!app.globalData.token) {
      wx.navigateTo({ url: '/pages/login/login' });
      return;
    }
    try {
      await http.post(api.favoriteShop, { shop_id: this.data.id }, { hideError: true });
      wx.showToast({ title: '操作成功', icon: 'success' });
    } catch (e) {}
  }
});
