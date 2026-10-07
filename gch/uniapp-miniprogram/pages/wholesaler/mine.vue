<template>
  <view class="container">
    <view class="user-card">
      <image class="avatar" :src="profile.avatar || '/static/avatar.png'" mode="aspectFill"></image>
      <view class="user-info">
        <view class="nick">{{profile.real_name || profile.account || '批发商'}}</view>
        <view class="role">{{profile.shop ? '店铺:' + profile.shop.name : '批发商账号'}}</view>
      </view>
    </view>

    <view class="menu">
      <view class="menu-item" @tap="goQrcode">
        <text class="icon">📱</text>
        <text class="label">我的店铺二维码</text>
        <text class="arrow">›</text>
      </view>
      <view class="menu-item" @tap="changePwd">
        <text class="icon">🔒</text>
        <text class="label">修改密码</text>
        <text class="arrow">›</text>
      </view>
      <view class="menu-item" @tap="goGoods">
        <text class="icon">📦</text>
        <text class="label">我的货盘</text>
        <text class="arrow">›</text>
      </view>
      <view class="menu-item" @tap="goReservations">
        <text class="icon">📋</text>
        <text class="label">预订处理</text>
        <text class="arrow">›</text>
      </view>
      <view class="menu-item" @tap="goPublish">
        <text class="icon">➕</text>
        <text class="label">发布货盘</text>
        <text class="arrow">›</text>
      </view>
    </view>

    <view class="menu">
      <view class="menu-item" @tap="switchRole">
        <text class="icon">🔄</text>
        <text class="label">切换身份</text>
        <text class="arrow">›</text>
      </view>
      <view class="menu-item logout" @tap="logout">
        <text class="icon">🚪</text>
        <text class="label">退出登录</text>
        <text class="arrow">›</text>
      </view>
    </view>
    <custom-tabbar />
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'
import userStore from '@/store/user.js'
import CustomTabbar from '@/components/custom-tabbar/custom-tabbar.vue'

export default {
  components: { CustomTabbar },
  data() { return { profile: {} } },
  onShow() { this.load() },
  methods: {
    async load() {
      try {
        this.profile = await http.get(api.wholesalerProfile, {}, { hideError: true })
      } catch (e) { uni.showToast({ title: e.message || "操作失败", icon: "none" }) }
    },
    goQrcode() { uni.navigateTo({ url: '/pages/wholesaler/qrcode' }) },
    changePwd() { uni.navigateTo({ url: '/pages/change-pwd/change-pwd?role=wholesaler' }) },
    goGoods() { uni.switchTab({ url: '/pages/wholesaler/goods' }) },
    goReservations() { uni.navigateTo({ url: '/pages/wholesaler/reservations' }) },
    goPublish() { uni.navigateTo({ url: '/pages/wholesaler/publish' }) },
    switchRole() {
      uni.showModal({
        title: '切换身份',
        content: '确认切换为采购商?需重新登录',
        success: ({ confirm }) => {
          if (!confirm) return
          userStore.switchTo('buyer')
          // 清兼容 key(防止外部残留)
          try { uni.removeStorageSync('GCH_AUTH_token') } catch (e) {}
          try { uni.removeStorageSync('GCH_AUTH') } catch (e) {}
          uni.reLaunch({ url: '/pages/login/account?role=buyer' })
        }
      })
    },
    async logout() {
      uni.showModal({
        title: '退出',
        content: '确认退出登录？',
        async success({ confirm }) {
          if (!confirm) return
          try { await http.post(api.wholesalerLogout, {}, { hideError: true }) } catch (e) { uni.showToast({ title: e.message || "操作失败", icon: "none" }) }
          userStore.clearAll()
          uni.reLaunch({ url: '/pages/login/login' })
        }
      })
    }
  }
}
</script>

<style scoped>
.user-card { display: flex; align-items: center; background: #fff; border-radius: 12rpx; padding: 30rpx; margin-bottom: 20rpx; }
.avatar { width: 100rpx; height: 100rpx; border-radius: 50%; background: #f5f5f5; }
.user-info { flex: 1; margin-left: 20rpx; }
.nick { font-size: 32rpx; font-weight: 500; }
.role { color: #999; font-size: 24rpx; margin-top: 6rpx; }
.menu { background: #fff; border-radius: 12rpx; margin-bottom: 20rpx; }
.menu-item { display: flex; align-items: center; padding: 28rpx 24rpx; border-bottom: 1rpx solid #f5f5f5; }
.menu-item:last-child { border-bottom: none; }
.menu-item .icon { font-size: 36rpx; margin-right: 20rpx; }
.menu-item .label { flex: 1; font-size: 30rpx; }
.menu-item .arrow { color: #ccc; font-size: 32rpx; }
.menu-item.logout .label { color: #f44336; }
</style>
