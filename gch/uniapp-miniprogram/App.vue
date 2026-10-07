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
    // 启动:未登录 → 登录页;批发商 → 工作台;采购商 → 首页
    const role = userStore.role
    if (role === 'wholesaler') {
      uni.reLaunch({ url: '/pages/wholesaler/dashboard/dashboard' })
    } else if (role === 'buyer') {
      uni.reLaunch({ url: '/pages/index/index' })
    } else {
      uni.reLaunch({ url: '/pages/login/login' })
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
page {
  background-color: #f5f5f5;
  font-size: 28rpx;
  color: #333;
}
</style>
