// pages/favorites/favorites.js
const { http, api } = require('../../utils/request.js');

Page({
  data: {
    type: 'goods', // goods | shop
    list: [],
    total: 0,
    page: 1,
    limit: 20,
    loading: false,
    finished: false
  },

  onShow() {
    // tabBar 切换时重新加载
    this.load(true);
  },

  switchType(e) {
    this.setData({ type: e.currentTarget.dataset.type });
    this.load(true);
  },

  async load(reset) {
    if (reset) this.setData({ page: 1, list: [], finished: false });
    this.setData({ loading: true });
    try {
      const data = await http.get(api.favorites, {
        type: this.data.type,
        page: this.data.page,
        limit: this.data.limit
      }, { hideError: true });

      const list = this.data.list.concat(data.list || []);
      this.setData({
        list, total: data.total,
        page: this.data.page + 1,
        loading: false,
        finished: list.length >= data.total
      });
    } catch (e) {
      this.setData({ loading: false });
    }
  },

  goGoods(e) {
    const id = e.currentTarget.dataset.id;
    wx.navigateTo({ url: `/pages/goods/goods?id=${id}` });
  },

  goShop(e) {
    const id = e.currentTarget.dataset.id;
    wx.navigateTo({ url: `/pages/shop/shop?id=${id}` });
  },

  goLogin() {
    wx.navigateTo({ url: '/pages/login/login' });
  }
});
