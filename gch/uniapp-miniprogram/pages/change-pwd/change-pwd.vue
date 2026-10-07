<template>
  <view class="container">
    <view class="card">
      <view class="form-row">
        <text class="label">旧密码</text>
        <input class="input" password v-model="form.old_password" placeholder="请输入旧密码" />
      </view>
      <view class="form-row">
        <text class="label">新密码</text>
        <input class="input" password v-model="form.new_password" placeholder="8-20 位字母+数字" />
      </view>
      <view class="form-row">
        <text class="label">确认新密码</text>
        <input class="input" password v-model="form.confirm" placeholder="再次输入新密码" />
      </view>
    </view>

    <view class="tip">密码规则:8-20 位,字母+数字。建议包含大小写字母。</view>

    <button class="btn-primary" :disabled="submitting" @tap="submit">
      {{ submitting ? '提交中...' : '确认修改' }}
    </button>
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'
import userStore from '@/store/user.js'

export default {
  data() {
    return {
      role: '',
      form: { old_password: '', new_password: '', confirm: '' },
      submitting: false
    }
  },
  onLoad(q) { this.role = q.role || 'buyer' },
  methods: {
    async submit() {
      const f = this.form
      if (!f.old_password || !f.new_password) {
        return uni.showToast({ title: '请填写完整', icon: 'none' })
      }
      if (f.new_password.length < 8 || f.new_password.length > 20) {
        return uni.showToast({ title: '新密码需 8-20 位', icon: 'none' })
      }
      if (f.new_password !== f.confirm) {
        return uni.showToast({ title: '两次输入不一致', icon: 'none' })
      }
      this.submitting = true
      try {
        const url = this.role === 'wholesaler' ? api.wholesalerChangePwd : api.buyerChangePwd
        await http.post(url, { old_password: f.old_password, new_password: f.new_password }, { hideError: true })
        // 清全部状态(token + role + profile),跳登录页
        userStore.clearAll()
        uni.showModal({
          title: '修改成功',
          content: '请重新登录',
          showCancel: false,
          success: () => {
            uni.reLaunch({ url: '/pages/login/login' })
          }
        })
      } catch (e) { uni.showToast({ title: e.message || '操作失败', icon: 'none' }) }
      this.submitting = false
    }
  }
}
</script>

<style scoped>
.card { background: #fff; border-radius: 12rpx; padding: 20rpx; margin-bottom: 20rpx; }
.form-row { display: flex; align-items: center; padding: 24rpx 0; border-bottom: 1rpx solid #eee; }
.form-row:last-child { border-bottom: none; }
.label { width: 160rpx; color: #666; font-size: 28rpx; }
.input { flex: 1; font-size: 28rpx; }
.tip { color: #999; font-size: 24rpx; padding: 16rpx 0; line-height: 1.5; }
.btn-primary { margin-top: 40rpx; }
</style>
