// utils/auth.js — 登录态本地存储

const KEY = 'GCH_AUTH';

function write(payload) {
  try {
    wx.setStorageSync(KEY, payload);
  } catch (e) {
    console.error('auth.write failed', e);
  }
}

function read() {
  try {
    return wx.getStorageSync(KEY) || {};
  } catch (e) {
    return {};
  }
}

function clear() {
  try {
    wx.removeStorageSync(KEY);
  } catch (e) {}
}

module.exports = { write, read, clear };
