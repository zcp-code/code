<template>
  <view class="container">
    <!-- 橙色头部(头像+昵称+公司名/账号) -->
    <view class="header">
      <view class="user-row">
        <view class="avatar">{{ userInfo.icon || '👤' }}</view>
        <view class="info">
          <view class="nick">{{ userInfo.name || '游客' }}</view>
          <view class="sub">{{ userInfo.subtitle || '微信授权登录 · 鲜果优选连锁' }}</view>
        </view>
      </view>
    </view>

    <!-- 未登录态(loginView)— 规格 6.5 -->
    <block v-if="!isLogin">
      <view class="login-card">
        <text class="login-icon">🍎</text>
        <view class="login-title">果仓货盘</view>
        <view class="login-slogan">产地直连 · 批发采购更省心</view>

        <!-- 微信授权登录(绿色,演示 demo) -->
        <button class="btn-green" @tap="wxLogin">
          <text class="wx-icon">💬</text>
          <text>微信授权登录</text>
        </button>

        <!-- 账号密码登录(链接) -->
        <view class="account-link" @tap="goAccountLogin">
          账号密码登录<text class="arrow">›</text>
        </view>

        <view class="demo-tip">演示账号 test / 123456</view>
      </view>

      <view class="agreement">登录代表已同意《用户协议》与《隐私政策》</view>
    </block>

    <!-- 已登录态 -->
    <block v-else>
      <!-- 第一组 cell -->
      <view class="cell-group">
        <view class="cell" @tap="goMyOrders">
          <text class="cell-icon">📋</text>
          <text class="cell-label">我的订单</text>
          <text class="cell-count" v-if="orderCount > 0">{{ orderCount }} 笔</text>
          <text class="cell-arrow">›</text>
        </view>
        <view class="cell" @tap="goFavorites">
          <text class="cell-icon">⭐</text>
          <text class="cell-label">我的收藏</text>
          <text class="cell-count" v-if="favCount > 0">{{ favCount }} 家</text>
          <text class="cell-arrow">›</text>
        </view>
        <view class="cell" @tap="goChangePwd">
          <text class="cell-icon">🔒</text>
          <text class="cell-label">修改密码</text>
          <text class="cell-arrow">›</text>
        </view>
      </view>

      <!-- 第二组 cell -->
      <view class="cell-group">
        <view v-if="isBuyer" class="cell" @tap="switchRole('wholesaler')">
          <text class="cell-icon">🏪</text>
          <text class="cell-label">切换为批发商</text>
          <text class="cell-arrow">›</text>
        </view>
        <view v-if="isWholesaler" class="cell" @tap="switchRole('buyer')">
          <text class="cell-icon">🛒</text>
          <text class="cell-label">切换为采购商</text>
          <text class="cell-arrow">›</text>
        </view>
        <view class="cell cell-danger" @tap="logout">
          <text class="cell-icon">🚪</text>
          <text class="cell-label">退出登录</text>
          <text class="cell-arrow">›</text>
        </view>
      </view>
    </block>

    <custom-tabbar v-if="isLogin" />
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'
import userStore from '@/store/user.js'
import CustomTabbar from '@/components/custom-tabbar/custom-tabbar.vue'

export default {
  components: { CustomTabbar },
  data() {
    return {
      userInfo: {},
      orderCount: 0,
      favCount: 0
    }
  },
  computed: {
    // 真实登录才显示(token ≥ 32 字符)
    isLogin() {
      const t = userStore.token || ''
      return t.length >= 32
    },
    isBuyer() { return this.isLogin && userStore.isBuyer },
    isWholesaler() { return this.isLogin && userStore.isWholesaler }
  },
  onShow() {
    this.updateUserInfo()
    if (this.isLogin) this.loadCounts()
  },
  methods: {
    updateUserInfo() {
      if (this.isBuyer) {
        this.userInfo = {
          icon: '🧑‍💼',
          name: userStore.profile?.real_name || userStore.profile?.account || '李采购',
          subtitle: userStore.profile?.company || '微信授权登录 · 鲜果优选连锁'
        }
      } else if (this.isWholesaler) {
        this.userInfo = {
          icon: '👨‍🌾',
          name: userStore.profile?.real_name || userStore.profile?.account || '王老板',
          subtitle: '账号:' + (userStore.profile?.account || '')
        }
      } else {
        this.userInfo = {}
      }
    },
    async loadCounts() {
      if (this.isBuyer) {
        try {
          const data = await http.get(api.reservationList, { page: 1, limit: 1 }, { hideError: true })
          this.orderCount = (data.total || 0)
        } catch (e) {}
        try {
          const data = await http.get(api.favorites, { type: 'shop', page: 1, limit: 1 }, { hideError: true })
          this.favCount = data.total || 0
        } catch (e) {}
      }
    },
    async wxLogin() {
      uni.login({
        provider: 'weixin',
        success: async ({ code }) => {
          if (!code) return uni.showToast({ title: '微信登录失败', icon: 'none' })
          try {
            const data = await http.post(api.wxLogin, {
              code, nickname: '游客' + Math.floor(Math.random() * 999), avatar: ''
            }, { hideError: true })
            userStore.setLogin(data.token, 'visitor', data.visitor)
            uni.showToast({ title: '微信授权登录成功', icon: 'success' })
            setTimeout(() => uni.switchTab({ url: '/pages/index/index' }), 600)
          } catch (e) {
            uni.showToast({ title: '演示原型:使用账号 test / 123456 登录', icon: 'none', duration: 2500 })
          }
        }
      })
    },
    goAccountLogin() { uni.navigateTo({ url: '/pages/login/account?role=buyer' }) },
    goMyOrders() { uni.navigateTo({ url: '/pages/orders/orders' }) },
    goFavorites() { uni.navigateTo({ url: '/pages/favorites/favorites' }) },
    goChangePwd() {
      const role = this.isWholesaler ? 'wholesaler' : 'buyer'
      uni.navigateTo({ url: `/pages/change-pwd/change-pwd?role=${role}` })
    },
    switchRole(target) {
      uni.showModal({
        title: '切换身份',
        content: `确认切换为${target === 'wholesaler' ? '批发商' : '采购商'}?需重新登录`,
        success: ({ confirm }) => {
          if (!confirm) return
          userStore.switchTo(target)
          // 清兼容 key(防止外部残留)
          try { uni.removeStorageSync('GCH_AUTH_token') } catch (e) {}
          try { uni.removeStorageSync('GCH_AUTH') } catch (e) {}
          uni.reLaunch({ url: `/pages/login/account?role=${target}` })
        }
      })
    },
    logout() {
      uni.showModal({
        title: '退出登录',
        content: '退出后回到入口页',
        success: ({ confirm }) => {
          if (!confirm) return
          userStore.clearLogin()
          uni.reLaunch({ url: '/pages/login/login' })
        }
      })
    }
  }
}
</script>

<style scoped>
.header {
  background: linear-gradient(135deg, #ff6600, #ff8a2b);
  padding: 80rpx 30rpx 100rpx;
  color: #fff;
}
.user-row { display: flex; align-items: center; }
.avatar {
  width: 124rpx;
  height: 124rpx;
  border-radius: 50%;
  background: rgba(255,255,255,.25);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 64rpx;
  margin-right: 24rpx;
}
.info { flex: 1; }
.nick { font-size: 36rpx; font-weight: 700; }
.sub { font-size: 24rpx; opacity: .92; margin-top: 8rpx; }

/* 未登录态 */
.login-card {
  background: #fff;
  border-radius: 24rpx;
  padding: 60rpx 40rpx 50rpx;
  margin: -60rpx 30rpx 20rpx;
  text-align: center;
}
.login-icon { font-size: 80rpx; display: block; }
.login-title { font-size: 40rpx; font-weight: 700; color: #111; margin-top: 12rpx; }
.login-slogan { color: #999; font-size: 24rpx; margin-top: 8rpx; }

.btn-green {
  background: #07c160;
  color: #fff;
  width: 100%;
  border-radius: 24rpx;
  padding: 24rpx 0;
  font-size: 30rpx;
  font-weight: 500;
  margin-top: 40rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12rpx;
}
.btn-green .wx-icon { font-size: 34rpx; }

.account-link {
  color: #ff6600;
  font-size: 28rpx;
  padding: 20rpx 0;
  margin-top: 10rpx;
}
.account-link .arrow { margin-left: 8rpx; font-size: 28rpx; }

.demo-tip {
  color: #999;
  font-size: 22rpx;
  margin-top: 20rpx;
  padding-top: 20rpx;
  border-top: 1rpx solid #f2f2f2;
}

.agreement {
  text-align: center;
  color: #999;
  font-size: 22rpx;
  padding: 30rpx 30rpx;
}

/* 已登录 cell 列表 */
.cell-group {
  background: #fff;
  border-radius: 12rpx;
  margin: -50rpx 24rpx 20rpx;
  overflow: hidden;
}
.cell {
  display: flex;
  align-items: center;
  padding: 30rpx 32rpx;
  border-bottom: 1rpx solid #f2f2f2;
}
.cell:last-child { border-bottom: none; }
.cell:active { background: #fafafa; }
.cell-icon { font-size: 36rpx; margin-right: 20rpx; }
.cell-label { flex: 1; font-size: 30rpx; color: #333; }
.cell-count { color: #999; font-size: 24rpx; margin-right: 8rpx; }
.cell-arrow { color: #ccc; font-size: 32rpx; }
.cell-danger .cell-label { color: #e64340; }
</style>
