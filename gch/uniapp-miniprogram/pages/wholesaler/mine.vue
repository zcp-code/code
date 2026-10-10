<template>
  <view class="container">
    <!-- 用户卡片:店铺 logo + 店名 + 账号 -->
    <view class="user-card">
      <image class="avatar" :src="profile.shop_logo || profile.avatar || '/static/avatar.jpg'" mode="aspectFill"></image>
      <view class="user-info">
        <view class="nick">{{ profile.real_name || profile.account || '批发商' }}</view>
        <view class="role">{{ profile.shop_name || (profile.shop && profile.shop.name) || '批发商账号' }}</view>
      </view>
      <view class="edit-btn" @tap="changePwd">🔒</view>
    </view>

    <!-- 数据概览 -->
    <view class="stats-row">
      <view class="stat-cell">
        <text class="stat-num">{{ stats.totalGoods || 0 }}</text>
        <text class="stat-label">在售货盘</text>
      </view>
      <view class="stat-cell">
        <text class="stat-num">{{ stats.pendingBookings || 0 }}</text>
        <text class="stat-label">待确认</text>
      </view>
      <view class="stat-cell">
        <text class="stat-num">{{ stats.confirmedBookings || 0 }}</text>
        <text class="stat-label">已确认</text>
      </view>
    </view>

    <!-- 我的业务 -->
    <view class="card">
      <view class="section-title">我的业务</view>
      <view class="menu-item" @tap="goQrcode">
        <view class="menu-icon icon-blue">📱</view>
        <view class="menu-text">
          <view class="menu-label">店铺二维码</view>
          <view class="menu-desc">分享给采购商扫码进店</view>
        </view>
        <text class="arrow">›</text>
      </view>

    </view>

    <!-- 设置 -->
    <view class="card">
      <view class="section-title">设置</view>
      <view class="menu-item" @tap="changePwd">
        <view class="menu-icon icon-gray">🔒</view>
        <view class="menu-text">
          <view class="menu-label">修改密码</view>
        </view>
        <text class="arrow">›</text>
      </view>

    </view>

    <!-- 退出登录 -->
    <view class="logout-btn" @tap="logout">退出登录</view>

    <custom-tabbar />
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
      profile: {},
      stats: { totalGoods: 0, pendingBookings: 0, confirmedBookings: 0 }
    }
  },
  onShow() {
    this.load()
    this.loadStats()
  },
  methods: {
    async load() {
      try {
        this.profile = await http.get(api.wholesalerProfile, {}, { hideError: true })
      } catch (e) {
        uni.showToast({ title: e.message || '操作失败', icon: 'none' })
      }
    },
    async loadStats() {
      try {
        const goods = await http.get(api.wholesalerGoodsList, { page: 1, limit: 1 }, { hideError: true })
        this.stats.totalGoods = goods.total || 0
        const pending = await http.get(api.wholesalerReservations, { status: 'pending', page: 1, limit: 1 }, { hideError: true })
        this.stats.pendingBookings = pending.total || 0
        const confirmed = await http.get(api.wholesalerReservations, { status: 'confirmed', page: 1, limit: 1 }, { hideError: true })
        this.stats.confirmedBookings = confirmed.total || 0
      } catch (e) {}
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
          try { await http.post(api.wholesalerLogout, {}, { hideError: true }) } catch (e) {}
          userStore.clearAll()
          uni.reLaunch({ url: '/pages/login/login' })
        }
      })
    }
  }
}
</script>

<style scoped>
page { background-color: #f5f5f5; }
.container { padding-bottom: 120rpx; }

/* === 用户卡片 === */
.user-card {
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, #ff6600, #ff8a5b);
  border-radius: 16rpx;
  padding: 36rpx 30rpx;
  margin: 20rpx 24rpx 0;
  color: #fff;
  box-shadow: 0 6rpx 20rpx rgba(255,107,53,.25);
}
.avatar {
  width: 96rpx;
  height: 96rpx;
  border-radius: 20rpx;
  background: rgba(255,255,255,.25);
  flex-shrink: 0;
}
.user-info { flex: 1; margin-left: 20rpx; min-width: 0; }
.nick { font-size: 36rpx; font-weight: 700; }
.role { font-size: 24rpx; opacity: .9; margin-top: 6rpx; }
.edit-btn {
  width: 64rpx;
  height: 64rpx;
  border-radius: 50%;
  background: rgba(255,255,255,.22);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32rpx;
  flex-shrink: 0;
}

/* === 数据概览 === */
.stats-row {
  display: flex;
  gap: 16rpx;
  padding: 20rpx 24rpx 0;
  margin-top: -30rpx;
}
.stat-cell {
  flex: 1;
  background: #fff;
  border-radius: 12rpx;
  padding: 20rpx 0;
  text-align: center;
  box-shadow: 0 4rpx 16rpx rgba(0,0,0,.06);
}
.stat-num {
  font-size: 38rpx;
  font-weight: 700;
  color: #ff6600;
  display: block;
}
.stat-label {
  color: #999;
  font-size: 22rpx;
  margin-top: 4rpx;
}

/* === 卡片 === */
.card {
  background: #fff;
  border-radius: 12rpx;
  margin: 20rpx 24rpx 0;
  padding: 20rpx 28rpx;
  box-shadow: 0 2rpx 12rpx rgba(0,0,0,.04);
}
.section-title {
  font-size: 28rpx;
  font-weight: 700;
  color: #333;
  padding-bottom: 16rpx;
  border-bottom: 1rpx solid #f2f2f2;
  margin-bottom: 12rpx;
}

/* === 菜单项 === */
.menu-item {
  display: flex;
  align-items: center;
  padding: 24rpx 0;
  border-bottom: 1rpx solid #f5f5f5;
}
.menu-item:last-child { border-bottom: none; }
.menu-icon {
  width: 64rpx;
  height: 64rpx;
  border-radius: 16rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32rpx;
  margin-right: 20rpx;
  flex-shrink: 0;
}
.icon-blue   { background: #e3f2fd; }
.icon-orange { background: #fff2e8; }
.icon-green  { background: #e8f5e9; }
.icon-purple { background: #f3e5f5; }
.icon-gray   { background: #f5f5f5; }
.menu-text { flex: 1; min-width: 0; }
.menu-label { font-size: 30rpx; color: #111; font-weight: 500; }
.menu-desc  { font-size: 22rpx; color: #999; margin-top: 4rpx; }
.arrow { color: #ccc; font-size: 32rpx; }

/* === 退出按钮 === */
.logout-btn {
  margin: 40rpx 24rpx 0;
  background: #fff;
  border-radius: 12rpx;
  padding: 24rpx 0;
  text-align: center;
  color: #e55a00;
  font-size: 30rpx;
  font-weight: 500;
  border: 1rpx solid #e55a00;
}
.logout-btn:active { background: #fff2e8; }
</style>
