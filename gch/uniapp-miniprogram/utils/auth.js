// utils/auth.js — 登录态本地缓存
const KEY = 'GCH_AUTH'

function write(payload) {
  try {
    uni.setStorageSync(KEY, payload)
  } catch (e) {
    console.error('auth.write failed', e)
  }
}

function read() {
  try {
    return uni.getStorageSync(KEY) || {}
  } catch (e) {
    return {}
  }
}

function clear() {
  try {
    uni.removeStorageSync(KEY)
  } catch (e) {}
}

export default { write, read, clear }
