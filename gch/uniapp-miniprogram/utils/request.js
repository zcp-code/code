// utils/request.js — HTTP 请求封装
import api from './api.js'

function buildBaseUrl() {
  return getApp().globalData.apiBase
}

function request(url, options = {}) {
  const app = getApp()
  const method = (options.method || 'GET').toUpperCase()
  const header = {
    'Content-Type': 'application/json',
    ...options.header
  }

  // 注入 Token
  if (app.globalData.token) {
    header['Token'] = app.globalData.token
  }

  return new Promise((resolve, reject) => {
    uni.request({
      url: buildBaseUrl() + url,
      method,
      data: options.data || {},
      header,
      success: res => {
        if (res.statusCode !== 200) {
          uni.showToast({ title: `HTTP ${res.statusCode}`, icon: 'none' })
          reject(new Error(`HTTP ${res.statusCode}`))
          return
        }
        const body = res.data || {}
        if (body.code === 200) {
          resolve(body.data)
        } else if (body.code === 401) {
          // token 失效 — 静默清除登录态,不弹 toast(避免大量 console 报错)
          app.clearLogin()
          reject(new Error(body.msg || '请先登录'))
        } else {
          // 业务失败:始终弹 toast(无论 hideError),确保用户能看到提示
          uni.showToast({ title: body.msg || '请求失败', icon: 'none' })
          reject(new Error(body.msg || '请求失败'))
        }
      },
      fail: err => {
        if (!options.hideError) {
          uni.showToast({ title: '网络错误', icon: 'none' })
        }
        reject(err)
      }
    })
  })
}

export default {
  get:    (url, data, opts) => request(url, { ...opts, method: 'GET',  data }),
  post:   (url, data, opts) => request(url, { ...opts, method: 'POST', data }),
  delete: (url, data, opts) => request(url, { ...opts, method: 'DELETE', data }),
  raw:    request
}

export { api }
