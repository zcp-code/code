
<template>
  <view class="container">

    <!-- 顶部橙色品牌区 -->
    <view class="brand-section">
      <view class="logo-box">
        <text class="logo-icon">🍊</text>
      </view>
      <text class="brand-name">{{ roleLabel }}</text>
    </view>

    <!-- 白色登录卡片 -->
    <view class="login-card">
      <view class="card-header">
        <text class="card-title">欢迎使用</text>
        <text class="card-subtitle">请先登录后使用全部功能</text>
      </view>

      <view class="form-section">
        <view class="input-item">
          <text class="input-icon">👤</text>
          <input
            class="input"
            placeholder="请输入账号"
            :placeholder-style="'color:#999'"
            v-model="account"
            @input="onAccountInput"
          />
        </view>

        <view class="input-item">
          <text class="input-icon">🔒</text>
          <input
            class="input"
            password
            placeholder="请输入密码"
            :placeholder-style="'color:#999'"
            v-model="password"
            @input="onPasswordInput"
          />
        </view>
      </view>

      <view class="demo-tip">演示账号:buyer001 / wh001 / test1234</view>

      <button
        class="btn-primary"
        :disabled="submitting"
        @tap="onLogin"
      >
        {{ submitting ? '登录中...' : '登录' }}
      </button>

	 <view class="back-tip" @tap="goBack">‹ 返回</view>

      <view class="agreement">
        登录即代表您同意
        <text class="link">《用户协议》</text>
        和
        <text class="link">《隐私政策》</text>
      </view>
    </view>

  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'
import userStore from '@/store/user.js'

export default {
  data() {
    return {
      role: 'buyer',
      account: '',     // 不预填,让用户自己输入(避免误点登录)
      password: '',
      submitting: false
    }
  },
  computed: {
    roleLabel() {
      return this.role === 'wholesaler' ? '批发商' : '采购商'
    }
  },
  onLoad(q) {
    this.role = q.role || 'buyer'
  },
  methods: {
    onAccountInput(e) { this.account = (e.detail.value || '').trim() },
    onPasswordInput(e) { this.password = e.detail.value || '' },
    async onLogin() {
      if (!this.account || !this.password) {
        return uni.showToast({ title: '请输入账号密码', icon: 'none' })
      }
      this.submitting = true
      try {
        const url = this.role === 'wholesaler' ? api.wholesalerLogin : api.buyerLogin
        const data = await http.post(url, {
          account: this.account,
          password: this.password
        }, { hideError: true })

        const profile = this.role === 'wholesaler' ? data.wholesaler : data.buyer
        userStore.setLogin(data.token, data.role, profile)

        uni.showToast({ title: '登录成功', icon: 'success' })
        setTimeout(() => {
          if (this.role === 'wholesaler') {
            uni.reLaunch({ url: '/pages/wholesaler/dashboard/dashboard' })
          } else {
            uni.switchTab({ url: '/pages/index/index' })
          }
        }, 600)
      } catch (e) {
        uni.showToast({ title: e.message || '登录失败', icon: 'none' })
      }
      this.submitting = false
    },
    goBack() { uni.navigateBack() }
  }
}
</script>

<style scoped>
page {
  background-color: #ff6600;
}

.container {
  min-height: 100vh;
  background: #f7f8fa;
  display: flex;
  flex-direction: column;
}

/* ========== 顶部橙色品牌区 ========== */
.brand-section {
  background: linear-gradient(180deg, #ff8a2b 0%, #ff6600 100%);
  padding: 120rpx 40rpx 200rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.logo-box {
  width: 140rpx;
  height: 140rpx;
  border-radius: 28rpx;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 12rpx 30rpx rgba(0, 0, 0, 0.15);
}

.logo-icon {
  font-size: 72rpx;
}

.brand-name {
  font-size: 44rpx;
  font-weight: 700;
  color: #ffffff;
  margin-top: 20rpx;
}

/* ========== 白色登录卡片 ========== */
.login-card {
  background: #ffffff;
  border-radius: 32rpx;
  padding: 48rpx 40rpx;
  margin: -140rpx 24rpx 0;
  box-shadow: 0 20rpx 40rpx rgba(255, 102, 0, 0.08);
}

.card-header {
  margin-bottom: 40rpx;
}

.card-title {
  display: block;
  font-size: 36rpx;
  font-weight: 700;
  color: #222222;
}

.card-subtitle {
  display: block;
  font-size: 24rpx;
  color: #999999;
  margin-top: 10rpx;
}

/* ========== 输入区 ========== */
.form-section {
  background: #f7f8fa;
  border-radius: 20rpx;
  padding: 8rpx 28rpx;
}

.input-item {
  display: flex;
  align-items: center;
  height: 96rpx;
  border-bottom: 1rpx solid #eeeeee;
}

.input-item:last-child {
  border-bottom: none;
}

.input-icon {
  font-size: 32rpx;
  margin-right: 16rpx;
  color: #999999;
}

.input {
  flex: 1;
  font-size: 28rpx;
  color: #333333;
  background: transparent;
}

/* ========== 演示提示 ========== */
.demo-tip {
  text-align: center;
  color: #999999;
  font-size: 22rpx;
  padding: 30rpx 0 10rpx;
}

/* ========== 主登录按钮 ========== */
.btn-primary {
  background: linear-gradient(135deg, #ff8a2b, #ff6600);
  color: #ffffff;
  font-size: 32rpx;
  font-weight: 600;
  border-radius: 48rpx;
  height: 96rpx;
  line-height: 96rpx;
  text-align: center;
  margin-top: 20rpx;
  box-shadow: 0 12rpx 30rpx rgba(255, 102, 0, 0.25);
}

.btn-primary[disabled] {
  opacity: 0.6;
}

/* ========== 分割线 ========== */
.divider {
  display: flex;
  align-items: center;
  margin: 40rpx 0 20rpx;
}

.line {
  flex: 1;
  height: 1rpx;
  background: #eeeeee;
}

.divider-text {
  padding: 0 24rpx;
  font-size: 24rpx;
  color: #999999;
}

/* ========== 微信按钮 ========== */
.btn-wechat {
  background: #07c160;
  color: #ffffff;
  font-size: 30rpx;
  font-weight: 600;
  border-radius: 48rpx;
  height: 96rpx;
  line-height: 96rpx;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12rpx;
}

.wechat-icon {
  font-size: 34rpx;
}

/* ========== 协议说明 ========== */
.agreement {
  text-align: center;
  color: #bbbbbb;
  font-size: 22rpx;
  padding: 30rpx 0 20rpx;
  line-height: 1.6;
}

.link {
  color: #ff6600;
}
/* ========== 演示提示 ========== */
.demo-tip {
  text-align: center;
  color: #999999;
  font-size: 22rpx;
  padding: 30rpx 24rpx 0;
}
</style>

