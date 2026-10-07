// utils/request.js — HTTP 请求封装
// - 自动注入 Token（从全局 app.globalData 读取）
// - 统一处理 {code, msg, data} 响应格式
// - 401 自动跳登录

const api = require('./api.js');

function buildBaseUrl() {
  const app = getApp();
  // 真机调试用 apiBaseDev（开发者工具用 apiBase）
  // 这里简化处理，开发者可在 app.js 切换
  return app.globalData.apiBase;
}

/**
 * 通用请求
 * @param {string} url   接口路径（不含 base）
 * @param {object} options { method, data, header, hideError }
 * @returns {Promise<any>}
 */
function request(url, options = {}) {
  const app = getApp();
  const base = buildBaseUrl();
  const method = (options.method || 'GET').toUpperCase();

  const header = {
    'Content-Type': 'application/json',
    ...options.header
  };

  // 注入 Token（如果有）
  if (app.globalData.token) {
    header['Token'] = app.globalData.token;
  }

  return new Promise((resolve, reject) => {
    wx.request({
      url: base + url,
      method,
      data: options.data || {},
      header,
      success: res => {
        // HTTP 层错误（500/404 等）
        if (res.statusCode !== 200) {
          reject(new Error(`HTTP ${res.statusCode}`));
          return;
        }

        // 业务层：后端统一返回 {code, msg, data}
        const body = res.data || {};
        if (body.code === 200) {
          resolve(body.data);
        } else if (body.code === 401) {
          // Token 失效
          app.clearLogin();
          wx.showToast({ title: '请先登录', icon: 'none' });
          wx.navigateTo({ url: '/pages/login/login' });
          reject(new Error(body.msg || '请先登录'));
        } else {
          // 业务错误
          if (!options.hideError) {
            wx.showToast({ title: body.msg || '请求失败', icon: 'none' });
          }
          reject(new Error(body.msg || '请求失败'));
        }
      },
      fail: err => {
        if (!options.hideError) {
          wx.showToast({ title: '网络错误', icon: 'none' });
        }
        reject(err);
      }
    });
  });
}

// 便捷方法
const http = {
  get:    (url, data, opts) => request(url, { ...opts, method: 'GET',  data }),
  post:   (url, data, opts) => request(url, { ...opts, method: 'POST', data }),
  delete: (url, data, opts) => request(url, { ...opts, method: 'DELETE', data })
};

module.exports = { http, request, api };
