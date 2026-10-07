<template>
 <view class="container">
 
   <!-- 橙色头部 -->
   <view class="header">
     <view class="user-row">
       <view class="avatar">
         <image :src="userInfo.icon || '/static/avatar.png'" mode="widthFix"></image>
       </view>
       <view class="info">
         <view class="nick">{{ userInfo.name || '游客' }}</view>
         <view class="sub">{{ userInfo.subtitle || '微信授权登录 · 鲜果优选连锁' }}</view>
       </view>
     </view>
   </view>
 
   <!-- 未登录态 -->
   <block v-if="!isLogin">
     <view class="login-card">
       <text class="login-icon">🍎</text>
       <view class="login-title">果仓货盘</view>
       <view class="login-slogan">产地直连 · 批发采购更省心</view>
 
       <button class="btn-green" @tap="wxLogin">
         <text class="wx-icon">💬</text>
         <text>微信授权登录</text>
       </button>
 
       <view class="account-link" @tap="goAccountLogin">
         账号密码登录<text class="arrow">›</text>
       </view>
 
       <view class="demo-tip">演示账号 test / 123456</view>
     </view>
 
     <view class="agreement">登录代表已同意《用户协议》与《隐私政策》</view>
   </block>
 
   <!-- 已登录态 -->
   <block v-else>
 
     <!-- 我的订单卡片：设计重点 -->
     <view class="order-card">
       <view class="card-head">
         <text class="card-title">我的订单</text>
         <text class="card-more" @tap="goMyOrders">查看全部 ›</text>
       </view>
 
       <view class="order-grid">
         <view class="order-item" @tap="goMyOrders">
           <view class="order-icon wait">
             <text class="iconfont icon-daiqueren1"></text>
           </view>
           <text class="order-text">待确认</text>
         </view>
 
         <view class="order-item" @tap="goMyOrders">
           <view class="order-icon confirm">
             <text class="iconfont icon-yiqueren"></text>
           </view>
           <text class="order-text">已确认</text>
         </view>
 
         <view class="order-item" @tap="goMyOrders">
           <view class="order-icon cancel">
             <text class="iconfont icon-yiquxiao2"></text>
           </view>
           <text class="order-text">已取消</text>
         </view>
       </view>
     </view>
 
     <!-- 第一组功能 -->
     <view class="cell-group">
       <view class="cell" @tap="goMyOrders">
         <view class="cell-icon orange">
           <text>📋</text>
         </view>
         <text class="cell-label">我的订单</text>
         <text class="cell-count" v-if="orderCount > 0">{{ orderCount }} 笔</text>
         <text class="cell-arrow iconfont icon-xiangyou"></text>
       </view>
 
       <view class="cell" @tap="goFavorites">
         <view class="cell-icon red">
           <text>⭐</text>
         </view>
         <text class="cell-label">我的收藏</text>
         <text class="cell-count" v-if="favCount > 0">{{ favCount }} 家</text>
         <text class="cell-arrow iconfont icon-xiangyou"></text>
       </view>
 
       <view class="cell" @tap="goChangePwd">
         <view class="cell-icon yellow">
           <text>🔒</text>
         </view>
         <text class="cell-label">修改密码</text>
         <text class="cell-arrow iconfont icon-xiangyou"></text>
       </view>
     </view>
 
     <!-- 第二组功能 -->
     <view class="cell-group">
       <view v-if="isBuyer" class="cell" @tap="switchRole('wholesaler')">
         <view class="cell-icon blue">
           <text>🏪</text>
         </view>
         <text class="cell-label">切换为批发商</text>
         <text class="cell-arrow iconfont icon-xiangyou"></text>
       </view>
 
       <view v-if="isWholesaler" class="cell" @tap="switchRole('buyer')">
         <view class="cell-icon purple">
           <text>🛒</text>
         </view>
         <text class="cell-label">切换为采购商</text>
         <text class="cell-arrow iconfont icon-xiangyou"></text>
       </view>
 
       <view class="cell cell-danger" @tap="logout">
         <view class="cell-icon danger">
           <text>🚪</text>
         </view>
         <text class="cell-label">退出登录</text>
         <text class="cell-arrow iconfont icon-xiangyou"></text>
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
          icon: '/static/avatar.jpg',
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
page {
  background-color: #f7f8fa;
}

.container {
  min-height: 100vh;
  padding-bottom: 140rpx;
  box-sizing: border-box;
}

/* ========== 顶部橙色头部 ========== */
.header {
  background: linear-gradient(135deg, #ff6600 0%, #ff8a2b 100%);
  padding: 90rpx 32rpx 110rpx;
  color: #fff;
  border-radius: 0 0 32rpx 32rpx;
}

.user-row {
  display: flex;
  align-items: center;
}

.avatar {
  width: 128rpx;
  height: 128rpx;
  border-radius: 50%;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  box-shadow: 0 8rpx 20rpx rgba(255, 102, 0, 0.25);
}

.avatar image {
  width: 100%;
  height: 100%;
}

.info {
  flex: 1;
  margin-left: 24rpx;
}

.nick {
  font-size: 34rpx;
  font-weight: 700;
  color: #ffffff;
}

.sub {
  font-size: 24rpx;
  color: rgba(255, 255, 255, 0.88);
  margin-top: 6rpx;
}

/* ========== 未登录态 ========== */
.login-card {
  background: #ffffff;
  border-radius: 24rpx;
  padding: 70rpx 40rpx 60rpx;
  margin: -60rpx 24rpx 24rpx;
  text-align: center;
  box-shadow: 0 10rpx 30rpx rgba(0, 0, 0, 0.06);
}

.login-icon {
  font-size: 88rpx;
  display: block;
}

.login-title {
  font-size: 40rpx;
  font-weight: 700;
  color: #111111;
  margin-top: 16rpx;
}

.login-slogan {
  color: #999999;
  font-size: 24rpx;
  margin-top: 10rpx;
}

.btn-green {
  background: #07c160;
  color: #ffffff;
  width: 100%;
  border-radius: 24rpx;
  padding: 26rpx 0;
  font-size: 30rpx;
  font-weight: 500;
  margin-top: 44rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14rpx;
}

.btn-green .wx-icon {
  font-size: 34rpx;
}

.account-link {
  color: #ff6600;
  font-size: 28rpx;
  padding: 24rpx 0;
  margin-top: 12rpx;
}

.account-link .arrow {
  margin-left: 8rpx;
}

.demo-tip {
  color: #999999;
  font-size: 22rpx;
  margin-top: 24rpx;
  padding-top: 20rpx;
  border-top: 1rpx solid #f2f2f2;
}

.agreement {
  text-align: center;
  color: #999999;
  font-size: 22rpx;
  padding: 0 30rpx;
}

/* ========== 我的订单卡片（设计重点） ========== */
.order-card {
  background: #ffffff;
  border-radius: 20rpx;
  padding: 28rpx 24rpx;
  margin: -60rpx 24rpx 20rpx;
  box-shadow: 0 12rpx 32rpx rgba(255, 102, 0, 0.08);
}

.card-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24rpx;
}

.card-title {
  font-size: 30rpx;
  font-weight: 700;
  color: #222222;
}

.card-more {
  font-size: 24rpx;
  color: #999999;
}

.order-grid {
  display: flex;
  justify-content: space-around;
  align-items: center;
}

.order-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.order-icon {
  width: 88rpx;
  height: 88rpx;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 40rpx;
  margin-bottom: 12rpx;
}

.order-icon.wait {
  background: #fff2e8;
}

.order-icon.confirm {
  background: #e8f8ef;
}

.order-icon.cancel {
  background: #f5f5f5;
}

.order-text {
  font-size: 24rpx;
  color: #666666;
}

/* ========== 功能卡片组 ========== */
.cell-group {
  background: #ffffff;
  border-radius: 20rpx;
  margin: 0 24rpx 20rpx;
  overflow: hidden;
  box-shadow: 0 8rpx 24rpx rgba(0, 0, 0, 0.04);
}

.cell {
  display: flex;
  align-items: center;
  padding: 28rpx 24rpx;
  border-bottom: 1rpx solid #f5f5f5;
}

.cell:last-child {
  border-bottom: none;
}

.cell:active {
  background-color: #fafafa;
}

.cell-icon {
  width: 72rpx;
  height: 72rpx;
  border-radius: 16rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32rpx;
  margin-right: 20rpx;
}

.cell-icon.orange {
  background: #fff2e8;
}

.cell-icon.red {
  background: #ffe8e8;
}

.cell-icon.yellow {
  background: #fff8e1;
}

.cell-icon.blue {
  background: #e8f3ff;
}

.cell-icon.purple {
  background: #f3e8ff;
}

.cell-icon.danger {
  background: #ffe8e8;
}

.cell-label {
  flex: 1;
  font-size: 30rpx;
  color: #222222;
}

.cell-count {
  color: #999999;
  font-size: 24rpx;
  margin-right: 10rpx;
}

.cell-arrow {
  color: #cccccc;
  font-size: 32rpx;
}

.cell-danger .cell-label {
  color: #e53e3e;
}
</style>
