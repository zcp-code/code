// pages/index/index.js
const { http, api } = require('../../utils/request.js');

Page({
  data: {
    page: 1,
    limit: 20,
    list: [],
    total: 0,
    loading: false,
    finished: false,
    keyword: '',
    categoryId: ''
  },

  onLoad() {
    this.loadList(true);
  },

  // 下拉刷新
  onPullDownRefresh() {
    this.loadList(true).then(() => wx.stopPullDownRefresh());
  },

  // 上拉加载更多
  onReachBottom() {
    if (this.data.finished || this.data.loading) return;
    this.loadList(false);
  },

  async loadList(reset) {
    if (reset) {
      this.setData({ page: 1, list: [], finished: false });
    }
    this.setData({ loading: true });
    try {
      const data = await http.get(api.goodsList, {
        page: this.data.page,
        limit: this.data.limit,
        keyword: this.data.keyword,
        category_id: this.data.categoryId
      }, { hideError: true });

      const newList = this.data.list.concat(data.list || []);
      this.setData({
        list: newList,
        total: data.total,
        page: this.data.page + 1,
        loading: false,
        finished: newList.length >= data.total
      });
    } catch (e) {
      this.setData({ loading: false });
    }
  },

  // 搜索
  onSearchInput(e) {
    this.setData({ keyword: e.detail.value });
  },
  onSearchConfirm() {
    this.loadList(true);
  },

  // 点击商品
  goDetail(e) {
    const id = e.currentTarget.dataset.id;
    wx.navigateTo({ url: `/pages/goods/goods?id=${id}` });
  },

  // 跳登录
  goLogin() {
    wx.navigateTo({ url: '/pages/login/login' });
  }
});
