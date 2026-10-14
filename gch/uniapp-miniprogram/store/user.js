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

// tabBar 页面(登录后跳转必须用 switchTab,不能用 redirectTo)
const TAB_BAR_PAGES = [
  'pages/index/index',
  'pages/shops/shops',
  'pages/orders/orders',
  'pages/profile/profile',
  'pages/wholesaler/dashboard/dashboard',
  'pages/wholesaler/goods',
  'pages/wholesaler/reservations',
  'pages/wholesaler/mine'
]

/** 获取当前页面的完整路径(含 query),登录后回跳用 */
function getCurrentPath() {
  const pages = typeof getCurrentPages === 'function' ? getCurrentPages() : []
  if (!pages.length) return ''
  const cur = pages[pages.length - 1]
  const opts = cur.options || {}
  const qs = Object.keys(opts)
    .filter(k => opts[k] !== undefined && opts[k] !== null)
    .map(k => `${k}=${encodeURIComponent(opts[k])}`)
    .join('&')
  return '/' + cur.route + (qs ? '?' + qs : '')
}

/** 登录后回跳处理:
 *  - 有 redirect → 走对应路径(tab 用 reLaunch 更稳,普通用 redirectTo)
 *  - 无 redirect → 按角色默认
 */
function afterLoginJump(redirect, role) {
  if (redirect) {
    const path = redirect.split('?')[0].replace(/^\//, '')
    if (TAB_BAR_PAGES.includes(path)) {
      // tabBar 用 reLaunch:从非 tab 页面切到 tabBar 页面最稳,避免 switchTab 卡死
      uni.reLaunch({ url: redirect })
    } else {
      uni.redirectTo({ url: redirect, fail: () => uni.reLaunch({ url: redirect }) })
    }
    return
  }
  if (role === 'wholesaler') {
    uni.reLaunch({ url: '/pages/wholesaler/dashboard/dashboard' })
  } else if (role === 'visitor') {
    uni.reLaunch({ url: '/pages/index/index' })
  } else {
    uni.switchTab({ url: '/pages/index/index' })
  }
}

/** 任意页面调用:带 redirect 跳到登录页(登录后回到原页) */
function goLogin({ role = 'buyer', mode = 'account' } = {}) {
  const redirect = encodeURIComponent(getCurrentPath())
  const path = mode === 'wx' ? '/pages/wxlogin/wxlogin' : '/pages/login/account'
  uni.navigateTo({ url: `${path}?role=${role}&redirect=${redirect}` })
}

function readCache() {
  try {
    const data = uni.getStorageSync(STORAGE_KEY)
    if (data && data.token) return data
  } catch (e) {}
  // 兜底:从备份 key 恢复(只剩 token,role/profile 丢了让用户重登)
  try {
    const backup = uni.getStorageSync('GCH_TK_BACKUP')
    if (backup) {
      console.warn('[userStore.readCache] 主 key 丢了,从备份恢复 token')
      return { token: backup, role: '', profile: null }
    }
  } catch (e) {}
  return {}
}

function writeCache() {
  // 同步双写:主 key + 兼容 key(老版本/部分场景下兼容性更好)
  const data = {
    token:   state.token,
    role:    state.role,
    profile: state.profile,
    apiBase: state.apiBase,
    expire:  state.expire
  }
  try {
    uni.setStorageSync(STORAGE_KEY, data)
    // 同步写一个简单 token-only 的备份,排查"丢了"时有用
    if (data.token) uni.setStorageSync('GCH_TK_BACKUP', data.token)
  } catch (e) {
    console.error('[userStore.writeCache] failed:', e)
  }
}

// 响应式 state(Vue.observable 让任何地方访问都会自动追踪依赖)
const _cache = readCache()
const state = Vue.observable({
  token:   _cache.token   || '',
  role:    _cache.role    || '',
  profile: _cache.profile || null,
  apiBase: _cache.apiBase || 'http://192.168.0.15:1992',
  expire:  _cache.expire  || 0   // token 过期时间(秒级时间戳),0 = 未知
})

// Pinia 风格 store:用 getter 暴露 state,模板自动响应
export const userStore = {
  // === state (响应式) ===
  get token()    { return state.token },
  get role()     { return state.role },
  get profile()  { return state.profile },
  get apiBase()  { return state.apiBase },
  get expire()   { return state.expire },

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
  setLogin(token, role, profile, expire = 0) {
    state.token = token
    state.role = role
    state.profile = profile
    state.expire = expire
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
  },

  /**
   * 静默续期 token(返回 Promise,失败不抛错)
   * - 仅在 token 距过期 < 1 天时才发请求
   * - 不阻塞调用方
   */
  async silentRefresh() {
    if (!state.token) return
    const now = Math.floor(Date.now() / 1000)
    // 剩余 > 1 天 或 没记录 expire → 不续期
    if (!state.expire || state.expire - now > 86400) return
    try {
      // 直接发请求(避免循环 import 引发循环依赖)
      const app = (typeof getApp === 'function') ? getApp() : null
      const apiBase = app ? app.globalData.apiBase : state.apiBase
      const res = await new Promise((resolve, reject) => {
        uni.request({
          url: apiBase + '/api/common/refreshToken',
          method: 'POST',
          header: { Token: state.token },
          success: r => resolve(r),
          fail: e => reject(e)
        })
      })
      if (res.statusCode === 200 && res.data && res.data.code === 200) {
        state.expire = res.data.data.expire || 0
        writeCache()
      } else if (res.statusCode === 401) {
        // 续期失败(token 已死)→ 清登录态,让后续请求走重新登录
        this.clearAll()
      }
    } catch (e) {
      // 静默,不打扰用户
    }
  },

  // === 登录跳转工具 ===
  goLogin,
  afterLoginJump
}

export default userStore
