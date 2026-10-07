// pages/profile/profile.js
const { http, api } = require('../../utils/request.js');

Page({
  data: {
    app: null,
    userInfo: null,
    role: '',
    roleName: '',
    reservations: []
  },

  onShow() {
    const app = getApp();
    const roleName = { visitor: '游客', buyer: '采购商', wholesaler: '批发商' }[app.globalData.role] || '未登录';
    this.setData({
      app, role: app.globalData.role, roleName,
      userInfo: app.globalData.userInfo
    });

    // 已登录则拉取我的预订
    if (app.globalData.token && app.globalData.role === 'buyer') {
      this.loadReservations();
    }
  },

  async loadReservations() {
    try {
      const data = await http.get(api.reservationList, {}, { hideError: true });
      this.setData({ reservations: data.list || [] });
    } catch (e) {}
  },

  goLogin() {
    wx.navigateTo({ url: '/pages/login/login' });
  },

  async logout() {
    wx.showModal({
      title: '确认退出',
      content: '退出后需重新登录',
      success: async ({ confirm }) => {
        if (!confirm) return;
        try {
          if (this.data.role === 'visitor') await http.post(api.visitorLogout, {}, { hideError: true });
          else if (this.data.role === 'buyer') await http.post(api.buyerLogout, {}, { hideError: true });
        } catch (e) {}
        getApp().clearLogin();
        this.setData({ userInfo: null, role: '', roleName: '未登录', reservations: [] });
        wx.showToast({ title: '已退出', icon: 'success' });
      }
    });
  },

  cancelReservation(e) {
    const id = e.currentTarget.dataset.id;
    wx.showModal({
      title: '取消预订',
      content: '确定取消该预订吗？',
      success: async ({ confirm }) => {
        if (!confirm) return;
        try {
          await http.post(api.reservationCancel, { id, reason: '买家主动取消' }, { hideError: true });
          wx.showToast({ title: '已取消', icon: 'success' });
          this.loadReservations();
        } catch (e) {}
      }
    });
  }
});
