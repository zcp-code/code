// pages/login/login.js
const { http, api } = require('../../utils/request.js');

Page({
  data: {
    role: 'visitor', // visitor / buyer
    account: '',
    password: '',
    nickname: '',
    avatar: ''
  },

  // 切换角色
  switchRole(e) {
    this.setData({ role: e.currentTarget.dataset.role });
  },

  // 采集昵称
  onNicknameInput(e) {
    this.setData({ nickname: e.detail.value });
  },

  // 采集头像 URL（实际项目应用 wx.chooseMedia 上传）
  onAvatarInput(e) {
    this.setData({ avatar: e.detail.value });
  },

  // 采集账号密码
  onAccountInput(e) { this.setData({ account: e.detail.value }); },
  onPasswordInput(e) { this.setData({ password: e.detail.value }); },

  /**
   * 微信一键登录（游客）
   * 流程：
   *   1. wx.login() 拿 code
   *   2. 后端 code2Session 拿到 openid
   *   3. 返回 token + 游客信息
   */
  async wxLoginTap() {
    wx.login({
      success: async ({ code }) => {
        if (!code) {
          wx.showToast({ title: '微信登录失败', icon: 'none' });
          return;
        }
        try {
          const data = await http.post(api.wxLogin, {
            code,
            nickname: this.data.nickname,
            avatar: this.data.avatar
          }, { hideError: true });

          // 全局存登录态
          const app = getApp();
          app.setLogin(data.token, data.role, data.visitor);
          wx.showToast({ title: '登录成功', icon: 'success' });
          setTimeout(() => wx.navigateBack(), 600);
        } catch (e) {
          wx.showToast({
            title: e.message || '登录失败（请检查 .env 中微信 AppID 是否已配置）',
            icon: 'none',
            duration: 2500
          });
        }
      }
    });
  },

  /**
   * 采购商账号密码登录
   */
  async buyerLoginTap() {
    const { account, password } = this.data;
    if (!account || !password) {
      wx.showToast({ title: '请输入账号密码', icon: 'none' });
      return;
    }
    try {
      const data = await http.post(api.buyerLogin, { account, password }, { hideError: true });
      const app = getApp();
      app.setLogin(data.token, data.role, data.buyer);
      wx.showToast({ title: '登录成功', icon: 'success' });
      setTimeout(() => wx.switchTab({ url: '/pages/index/index' }), 600);
    } catch (e) {
      wx.showToast({ title: e.message || '登录失败', icon: 'none' });
    }
  }
});
