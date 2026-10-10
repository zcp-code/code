<template>
  <view class="loginView">
    <view class="logo">
      <text class="big-icon">🍎</text>
      <view class="title">果仓货盘</view>
      <view class="slogan">产地直连 · 批发采购更省心</view>
    </view>

    <view class="info-card">
      <view class="info-row">
        <text class="info-icon">👀</text>
        <text class="info-text">授权后可浏览所有货品和店铺</text>
      </view>
      <view class="info-row">
        <text class="info-icon">🛒</text>
        <text class="info-text">下单预订需登录采购商账号</text>
      </view>
    </view>

    <button class="btn-wx" :disabled="submitting" @tap="onWxLogin">
      <text class="wx-icon">💬</text>
      <text>{{ submitting ? '授权中...' : '微信授权进入' }}</text>
    </button>

    <view class="demo-tip">演示模式:一键进入游客浏览模式,无需真实微信授权</view>

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
     * 微信授权登录(已认证 appid):
     *   1. 调 wx.login() 拿临时 code
     *   2. 调 uni.getUserProfile() 拿昵称头像(M2+ 已收紧,需要用户主动授权)
     *   3. 把 code + nickname + avatar 发给后端 /api/wxlogin
     *   4. 后端 code2Session 换 openid,创建/更新 visitor,签发 token
     */
    async onWxLogin() {
      if (this.submitting) return
      this.submitting = true
      try {
        // 1. 微信登录 → 临时 code
        const loginRes = await new Promise((resolve, reject) => {
          uni.login({
            provider: 'weixin',
            success: resolve,
            fail: reject
          })
        })

        // 2. 用户信息(昵称 + 头像)— 老版本基础库 2.x 必填,新版本可选
        let nickname = '游客用户', avatar = ''
        try {
          const profile = await new Promise((resolve, reject) => {
            uni.getUserProfile({
              desc: '用于显示您的访客头像和昵称',
              success: resolve,
              fail: () => resolve(null)  // 用户拒绝也继续走,只用 code
            })
          })
          if (profile) {
            nickname = profile.userInfo.nickName || nickname
            avatar   = profile.userInfo.avatarUrl || ''
          }
        } catch (e) { /* 忽略 */ }

        // 3. 后端换 token
        const data = await http.post('/api/wxlogin', {
          code: loginRes.code,
          nickname,
          avatar
        }, { hideError: true })

        // 4. 写入 userStore
        const profile_data = data.visitor || {
          id: data.user_id,
          nickname,
          avatar
        }
        userStore.setLogin(data.token, data.role, profile_data)

        uni.showToast({ title: '已进入浏览模式', icon: 'success' })
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
  margin-bottom: 60rpx;
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

.info-card {
  width: 100%;
  background: linear-gradient(180deg, #fff7ed, #fef3c7);
  border-radius: 20rpx;
  padding: 28rpx 32rpx;
  margin-bottom: 50rpx;
}
.info-row {
  display: flex;
  align-items: center;
  padding: 12rpx 0;
}
.info-icon { font-size: 32rpx; margin-right: 16rpx; }
.info-text { font-size: 26rpx; color: #4b5563; }

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
