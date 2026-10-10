// store/user.js — Pinia 风格用户状态管理(零依赖,基于 Vue.observable)
//
// 用法:
//   import userStore from '@/store/user.js'
//   userStore.isLogin       // boolean getter,响应式
//   userStore.setLogin(...)  // action
//
// Vue.observable 让 state 响应式,getter 在 template 里自动重渲染。

import Vue from 'vue'

const STORAGE_KEY = 'GCH_USER'

function readCache() {
  try {
    return uni.getStorageSync(STORAGE_KEY) || {}
  } catch (e) {
    return {}
  }
}

function writeCache() {
  try {
    uni.setStorageSync(STORAGE_KEY, {
      token:   state.token,
      role:    state.role,
      profile: state.profile,
      apiBase: state.apiBase
    })
  } catch (e) {}
}

// 响应式 state(Vue.observable 让任何地方访问都会自动追踪依赖)
const _cache = readCache()
const state = Vue.observable({
  token:   _cache.token   || '',
  role:    _cache.role    || '',
  profile: _cache.profile || null,
  apiBase: _cache.apiBase || 'http://192.168.0.15:1992'
})

// Pinia 风格 store:用 getter 暴露 state,模板自动响应
export const userStore = {
  // === state (响应式) ===
  get token()    { return state.token },
  get role()     { return state.role },
  get profile()  { return state.profile },
  get apiBase()  { return state.apiBase },

  // === getters ===
  get isLogin()      { return !!state.token && state.token.length >= 4 },
  get isRealLogin()  { return !!state.token && state.token.length >= 32 },
  // token 是否真有效(后端 bcrypt 64 字符,dev 登录也是真 token)
  get isRealToken()  {
    const t = state.token || ''
    return t.length >= 60 && !t.startsWith('mock_')
  },
  get isVisitor()    { return this.isRealLogin && state.role === 'visitor' },
  get isBuyer()      { return this.isRealLogin && state.role === 'buyer' },
  get isWholesaler() { return this.isRealLogin && state.role === 'wholesaler' },
  get canOrder()     { return this.isRealLogin && state.role === 'buyer' && this.isRealToken },
  get canPublish()   { return this.isRealLogin && state.role === 'wholesaler' && this.isRealToken },

  // === actions ===
  setLogin(token, role, profile) {
    state.token = token
    state.role = role
    state.profile = profile
    writeCache()
  },

  setRole(role) {
    state.role = role
    writeCache()
  },

  /** 清 token + profile,保留 role(切回起始页用) */
  clearLogin() {
    state.token = ''
    state.profile = null
    writeCache()
  },

  /** 清全部(role 也清,真正的退出登录) */
  clearAll() {
    state.token = ''
    state.role = ''
    state.profile = null
    writeCache()
  },

  /** 切换身份:清全部状态 + 清兼容 key(store 不调 uni,由 caller 负责跳转) */
  switchTo(target) {
    this.clearAll()
    return target === 'wholesaler' ? 'wholesaler' : 'buyer'
  },

  setApiBase(url) {
    state.apiBase = url
    writeCache()
  }
}

export default userStore
