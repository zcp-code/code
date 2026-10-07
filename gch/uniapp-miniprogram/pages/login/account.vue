<template>
  <view class="container">
    <view class="header">
      <text class="logo-icon">🍎</text>
      <text class="title">{{ roleLabel }}账号密码登录</text>
    </view>

    <view class="card">
      <view class="form-row">
        <text class="label">账号</text>
        <input class="input" :placeholder="role === 'wholesaler' ? '批发商账号' : '采购商账号'"
               v-model="account" @input="onAccountInput" />
      </view>
      <view class="form-row">
        <text class="label">密码</text>
        <input class="input" password placeholder="8-20 位"
               v-model="password" @input="onPasswordInput" />
      </view>
    </view>

    <view class="demo-tip">演示账号:buyer001 / wh001 / test1234</view>

    <button class="btn-primary" :disabled="submitting" @tap="onLogin">
      {{ submitting ? '登录中...' : '登录' }}
    </button>

    <view class="back-tip" @tap="goBack">‹ 返回</view>
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
.container { padding: 40rpx; min-height: 100vh; background: #f5f5f5; }
.header { display: flex; flex-direction: column; align-items: center; padding: 40rpx 0 60rpx; }
.logo-icon { font-size: 80rpx; }
.title { font-size: 36rpx; font-weight: 600; color: #ff6b35; margin-top: 16rpx; }

.card { background: #fff; border-radius: 12rpx; padding: 20rpx; margin-bottom: 30rpx; }
.form-row { display: flex; align-items: center; padding: 24rpx 0; border-bottom: 1rpx solid #eee; }
.form-row:last-child { border-bottom: none; }
.label { width: 140rpx; color: #666; }
.input { flex: 1; font-size: 30rpx; }

.back-tip { text-align: center; color: #999; font-size: 26rpx; margin-top: 30rpx; }

.demo-tip {
  color: #999;
  font-size: 22rpx;
  text-align: center;
  margin: 16rpx 0 30rpx;
  padding: 16rpx 0;
  border-top: 1rpx solid #f2f2f2;
  border-bottom: 1rpx solid #f2f2f2;
}
</style>
