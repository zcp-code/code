<script>
import userStore from '@/store/user.js'

export default {
  globalData: {
    get apiBase()   { return userStore.apiBase },
    get token()     { return userStore.token },
    get role()      { return userStore.role },
    get userInfo()  { return userStore.profile },
    set apiBase(v)  { userStore.setApiBase(v) }
  },

  onLaunch() {
    // 启动诊断(上线时可注释掉)
    console.log('[App.onLaunch] storage:', uni.getStorageSync('GCH_USER'))
    console.log('[App.onLaunch] isRealLogin:', userStore.isRealLogin, 'role:', userStore.role, 'token.len:', (userStore.token || '').length)

    // 启动:看持久化 token 决定去向
    //   1. 没登录态(token 缺失/无效) → 登录入口
    //   2. 批发商 → 工作台
    //   3. 采购商 / 游客 → 首页(都能浏览,只是游客不能下单)
    if (!userStore.isRealLogin) {
      console.log('[App.onLaunch] 无登录态 → 跳登录页')
      uni.reLaunch({ url: '/pages/login/login' })
      return
    }
    // 静默续期(token 剩余 < 1 天才发请求,失败不影响主流程)
    userStore.silentRefresh()
    if (userStore.role === 'wholesaler') {
      console.log('[App.onLaunch] 批发商 → 工作台')
      uni.reLaunch({ url: '/pages/wholesaler/dashboard/dashboard' })
    } else {
      console.log('[App.onLaunch] 采购商/游客 → 首页')
      uni.reLaunch({ url: '/pages/index/index' })
    }
  },

  methods: {
    setLogin(token, role, userInfo) {
      userStore.setLogin(token, role, userInfo)
    },
    clearLogin() {
      userStore.clearLogin()
    }
  }
}
</script>

<style>
@import "@/static/icon.css";	
page {
  background-color: #f5f5f5;
  font-size: 28rpx;
  color: #333;
}
</style>
