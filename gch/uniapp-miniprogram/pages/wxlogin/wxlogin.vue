<template>
  <view class="loginView">
    <view class="logo">
      <text class="big-icon">🍎</text>
      <view class="title">果仓货盘</view>
      <view class="slogan">产地直连 · 批发采购更省心</view>
    </view>

    <button class="btn-wx" :disabled="submitting" @tap="onWxLogin">
      <text class="wx-icon">💬</text>
      <text>{{ submitting ? '授权中...' : '微信授权登录' }}</text>
    </button>

    <view class="demo-tip">演示模式:后端 dev 登录接口直接生成 buyer token,可下单</view>

    <view class="agreement">
      <text class="checkbox">☑</text>
      <text>登录代表已同意《用户协议》与《隐私政策》</text>
    </view>

    <view class="back-tip" @tap="goBack">‹ 返回</view>
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'
import userStore from '@/store/user.js'

export default {
  data() {
    return {
      submitting: false
    }
  },
  methods: {
    /**
     * 演示模式:M2 之前直接调后端 /api/dev/login 生成 buyer 真 token
     * 之后接入真实 wx.login() → /api/wxlogin → 后端 wxlogin_test_mode 自动返 token
     */
    async onWxLogin() {
      if (this.submitting) return
      this.submitting = true
      try {
        // 调后端 dev 登录接口(测试模式,生成 buyer 真 token 写 user_token 表)
        const data = await http.post('/api/dev/login', {
          role: 'buyer',
          account: 'buyer001'
        }, { hideError: true })

        // 写入 userStore
        const profile = { id: data.user_id, account: 'buyer001', real_name: '演示采购' }
        userStore.setLogin(data.token, data.role, profile)

        uni.showToast({ title: '微信授权登录成功(演示)', icon: 'success' })
        setTimeout(() => uni.reLaunch({ url: '/pages/index/index' }), 600)
      } catch (e) {
        uni.showToast({ title: e.message || '登录失败', icon: 'none' })
      }
      this.submitting = false
    },

    goBack() {
      uni.navigateBack({ delta: 1 })
    }
  }
}
</script>

<style scoped>
.loginView {
  min-height: 100vh;
  background: #fff;
  padding: 120rpx 60rpx 60rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.logo {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 80rpx;
}
.big-icon { font-size: 140rpx; }
.title {
  font-size: 52rpx;
  font-weight: 700;
  color: #111;
  margin-top: 20rpx;
}
.slogan {
  color: #999;
  font-size: 26rpx;
  margin-top: 16rpx;
}
.btn-wx {
  background: #07c160;
  color: #fff;
  width: 100%;
  height: 100rpx;
  line-height: 100rpx;
  border-radius: 48rpx;
  font-size: 32rpx;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12rpx;
  box-shadow: 0 4rpx 16rpx rgba(7,193,96,.25);
}
.btn-wx[disabled] { background: #ccc; color: #fff; }
.wx-icon { font-size: 36rpx; }

.demo-tip {
  margin-top: 30rpx;
  color: #999;
  font-size: 22rpx;
  padding-top: 30rpx;
  border-top: 1rpx solid #f2f2f2;
  width: 100%;
  text-align: center;
}

.agreement {
  margin-top: 40rpx;
  display: flex;
  align-items: center;
  gap: 10rpx;
  font-size: 22rpx;
  color: #999;
}
.agreement .checkbox {
  color: #ff6600;
  font-size: 26rpx;
}

.back-tip {
  margin-top: 40rpx;
  text-align: center;
  color: #999;
  font-size: 26rpx;
}
</style>
