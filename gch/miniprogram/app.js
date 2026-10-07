// app.js — 全局应用入口
const request = require('./utils/request.js');
const auth = require('./utils/auth.js');

App({
  globalData: {
    // 后端 API 基础地址（部署时改成实际域名）
    apiBase: 'http://www.gch.local:1992',
    apiBaseDev: 'http://127.0.0.1:1992', // 真机调试时填这个
    userInfo: null,
    token: '',
    role: '' // visitor / buyer / wholesaler
  },

  onLaunch() {
    // 启动时从本地缓存恢复登录态
    const cached = auth.read();
    if (cached.token) {
      this.globalData.token = cached.token;
      this.globalData.role = cached.role;
      this.globalData.userInfo = cached.userInfo;
    }
  },

  // 全局请求方法（页面里直接 getApp().http.get(...)）
  http: request.http,

  // 登录态写入
  setLogin(token, role, userInfo) {
    this.globalData.token = token;
    this.globalData.role = role;
    this.globalData.userInfo = userInfo;
    auth.write({ token, role, userInfo });
  },

  // 登出
  clearLogin() {
    this.globalData.token = '';
    this.globalData.role = '';
    this.globalData.userInfo = null;
    auth.clear();
  }
});
