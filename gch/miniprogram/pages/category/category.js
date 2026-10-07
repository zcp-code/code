// pages/category/category.js
const { http, api } = require('../../utils/request.js');

Page({
  data: {
    categories: [],
    selectedId: 0
  },

  onShow() {
    this.load();
  },

  async load() {
    try {
      const list = await http.get(api.categoryList, {}, { hideError: true });
      this.setData({ categories: list || [] });
    } catch (e) {}
  },

  selectCategory(e) {
    const id = e.currentTarget.dataset.id;
    this.setData({ selectedId: id });
    // 跳首页并带上分类筛选
    wx.switchTab({
      url: '/pages/index/index',
      success: () => {
        // 通知首页按分类筛选（可通过全局事件或 storage）
        getApp().globalData.categoryFilter = id;
      }
    });
  }
});
