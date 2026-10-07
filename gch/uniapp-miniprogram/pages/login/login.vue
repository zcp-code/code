<template>
  <view class="entry">
    <view class="hero">
      <text class="big-icon">🍎</text>
      <text class="title">果仓货盘</text>
      <text class="slogan">产地直连 · 批发采购更省心</text>
      <text class="hint">请选择您的身份进入</text>
    </view>

    <view class="role-list">
      <view class="role-card" @tap="enterRole('buyer')">
        <text class="role-icon">🧑‍💼</text>
        <view class="role-info">
          <text class="role-title">我是采购商</text>
          <text class="role-desc">浏览货盘 · 一键预定 · 管理订单</text>
        </view>
        <text class="arrow">›</text>
      </view>

      <view class="role-card" @tap="enterRole('wholesaler')">
        <text class="role-icon">👨‍🌾</text>
        <view class="role-info">
          <text class="role-title">我是批发商</text>
          <text class="role-desc">发布货盘 · 处理预订 · 管理库存</text>
        </view>
        <text class="arrow">›</text>
      </view>
    </view>

  </view>
</template>

<script>
import userStore from '@/store/user.js'

export default {
  methods: {
    enterRole(role) {
      // 采购商 → 微信授权登录(主入口)
      // 批发商 → 账号密码登录
      if (role === 'buyer') {
        uni.navigateTo({ url: '/pages/wxlogin/wxlogin' })
      } else {
        uni.navigateTo({ url: `/pages/login/account?role=${role}` })
      }
    },
    resetDemo() {
      uni.showModal({
        title: '重置演示',
        content: '清除本地登录态,回到入口页',
        success: ({ confirm }) => {
          if (!confirm) return
          uni.removeStorageSync('GCH_AUTH')
          userStore.clearLogin()
          uni.reLaunch({ url: '/pages/login/login' })
        }
      })
    }
  }
}
</script>

<style scoped>
.entry {
  min-height: 100vh;
  background: linear-gradient(180deg, #ff8a2b 0%, #ff6600 50%, #f04a00 100%);
  padding: 120rpx 60rpx 60rpx;
  display: flex;
  flex-direction: column;
}
.hero {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 80rpx;
}
.big-icon { font-size: 140rpx; }
.title {
  font-size: 52rpx;
  font-weight: 700;
  color: #fff;
  margin-top: 20rpx;
}
.slogan {
  color: rgba(255,255,255,.92);
  font-size: 26rpx;
  margin-top: 16rpx;
}
.hint {
  color: rgba(255,255,255,.85);
  font-size: 24rpx;
  margin-top: 30rpx;
}

.role-list { display: flex; flex-direction: column; gap: 24rpx; }
.role-card {
  background: rgba(255,255,255,.96);
  border-radius: 24rpx;
  padding: 36rpx 32rpx;
  display: flex;
  align-items: center;
  gap: 24rpx;
  box-shadow: 0 6rpx 20rpx rgba(0,0,0,.12);
}
.role-card:active { transform: scale(.98); }
.role-icon { font-size: 64rpx; flex-shrink: 0; }
.role-info { flex: 1; display: flex; flex-direction: column; }
.role-title { font-size: 32rpx; font-weight: 600; color: #111; }
.role-desc { color: #666; font-size: 22rpx; margin-top: 8rpx; }
.arrow { color: #ff6600; font-size: 36rpx; font-weight: bold; }

.footer {
  margin-top: auto;
  padding-top: 60rpx;
  text-align: center;
  color: rgba(255,255,255,.85);
  font-size: 22rpx;
}
.reset { color: #fff; text-decoration: underline; margin-left: 8rpx; }
</style>
