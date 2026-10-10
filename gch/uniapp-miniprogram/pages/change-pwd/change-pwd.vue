<template>
  <view class="container">

    <!-- 顶部头部 -->
    <view class="hero">
      <view class="hero-icon">🔐</view>
      <view class="hero-title">修改密码</view>
      <view class="hero-sub">为了账号安全,建议定期修改</view>
    </view>

    <!-- 密码卡片 -->
    <view class="card">
      <!-- 旧密码 -->
      <view class="input-row">
        <text class="input-label">旧密码</text>
        <view class="input-wrap">
          <input
            class="input"
            :password="!showOld"
            v-model="form.old_password"
            placeholder="请输入当前密码"
            placeholder-class="input-placeholder"
          />
          <text class="eye" @tap="showOld = !showOld">{{ showOld ? '🙈' : '👁' }}</text>
        </view>
      </view>

      <view class="divider"></view>

      <!-- 新密码 -->
      <view class="input-row">
        <text class="input-label">新密码</text>
        <view class="input-wrap">
          <input
            class="input"
            :password="!showNew"
            v-model="form.new_password"
            placeholder="8-20 位,字母+数字"
            placeholder-class="input-placeholder"
          />
          <text class="eye" @tap="showNew = !showNew">{{ showNew ? '🙈' : '👁' }}</text>
        </view>
      </view>

      <!-- 密码强度 -->
      <view v-if="form.new_password" class="strength-row">
        <text class="strength-label">强度</text>
        <view class="strength-bars">
          <view class="bar" :class="strengthLevel >= 1 ? `bar-${strengthLevel}` : ''"></view>
          <view class="bar" :class="strengthLevel >= 2 ? `bar-${strengthLevel}` : ''"></view>
          <view class="bar" :class="strengthLevel >= 3 ? `bar-${strengthLevel}` : ''"></view>
          <view class="bar" :class="strengthLevel >= 4 ? `bar-${strengthLevel}` : ''"></view>
        </view>
        <text class="strength-text" :class="'text-' + strengthLevel">{{ strengthText }}</text>
      </view>

      <view class="divider"></view>

      <!-- 确认密码 -->
      <view class="input-row">
        <text class="input-label">确认密码</text>
        <view class="input-wrap">
          <input
            class="input"
            :password="!showConfirm"
            v-model="form.confirm"
            placeholder="再次输入新密码"
            placeholder-class="input-placeholder"
          />
          <text class="eye" @tap="showConfirm = !showConfirm">{{ showConfirm ? '🙈' : '👁' }}</text>
        </view>
      </view>

      <!-- 匹配提示 -->
      <view v-if="form.confirm && form.confirm !== form.new_password" class="match-tip bad">
        ✕ 两次输入不一致
      </view>
      <view v-else-if="form.confirm && form.confirm === form.new_password" class="match-tip good">
        ✓ 密码匹配
      </view>
    </view>

    <!-- 密码规则说明 -->
    <view class="rules">
      <view class="rules-title">📌 密码规则</view>
      <view class="rule-item">· 长度 8-20 位</view>
      <view class="rule-item">· 必须包含字母和数字</view>
      <view class="rule-item">· 建议混合大小写字母</view>
      <view class="rule-item">· 避免使用生日/连续数字等弱密码</view>
    </view>

    <!-- 提交按钮 -->
    <view class="submit-bar">
      <button class="btn-solid" :disabled="!canSubmit || submitting" @tap="submit">
        {{ submitting ? '提交中...' : '确认修改' }}
      </button>
    </view>
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
      submitting: false,
      showOld: false,
      showNew: false,
      showConfirm: false
    }
  },
  onLoad(q) { this.role = q.role || 'buyer' },
  computed: {
    // 密码强度:0=无,1=弱,2=中,3=强,4=极强
    strengthLevel() {
      const p = this.form.new_password
      if (!p) return 0
      let score = 0
      if (p.length >= 8) score++
      if (/[a-z]/.test(p) && /[A-Z]/.test(p)) score++
      else if (/[a-zA-Z]/.test(p)) score += 0.5
      if (/\d/.test(p)) score++
      if (/[^a-zA-Z0-9]/.test(p)) score++
      return Math.min(4, Math.floor(score))
    },
    strengthText() {
      return ['', '弱', '中', '强', '极强'][this.strengthLevel] || ''
    },
    canSubmit() {
      const f = this.form
      return f.old_password
        && f.new_password.length >= 8 && f.new_password.length <= 20
        && f.new_password === f.confirm
    }
  },
  methods: {
    async submit() {
      if (this.submitting || !this.canSubmit) return
      this.submitting = true
      try {
        const url = this.role === 'wholesaler' ? api.wholesalerChangePwd : api.buyerChangePwd
        await http.post(url, {
          old_password: this.form.old_password,
          new_password: this.form.new_password
        }, { hideError: true })
        userStore.clearAll()
        uni.showModal({
          title: '修改成功',
          content: '请使用新密码重新登录',
          showCancel: false,
          success: () => uni.reLaunch({ url: '/pages/login/login' })
        })
      } catch (e) {
        uni.showToast({ title: e.message || '操作失败', icon: 'none' })
      }
      this.submitting = false
    }
  }
}
</script>

<style scoped>
.container { min-height: 100vh; background: #f5f7fa; padding-bottom: 200rpx; }

/* ===== Hero ===== */
.hero {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  padding: 60rpx 32rpx 80rpx;
  text-align: center;
  color: #fff;
}
.hero-icon { font-size: 80rpx; line-height: 1; margin-bottom: 16rpx; }
.hero-title { font-size: 40rpx; font-weight: 700; }
.hero-sub { font-size: 24rpx; opacity: 0.9; margin-top: 8rpx; }

/* ===== Card ===== */
.card {
  background: #fff;
  margin: -40rpx 24rpx 24rpx;
  border-radius: 20rpx;
  padding: 8rpx 28rpx;
  box-shadow: 0 4rpx 24rpx rgba(0, 0, 0, 0.08);
  position: relative;
  z-index: 1;
}

.input-row {
  display: flex;
  align-items: center;
  padding: 32rpx 0;
}
.input-label {
  width: 160rpx;
  font-size: 28rpx;
  color: #4b5563;
  flex-shrink: 0;
}
.input-wrap {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 12rpx;
}
.input {
  flex: 1;
  font-size: 30rpx;
  color: #1f2937;
  letter-spacing: 2rpx;
}
.input-placeholder {
  color: #cbd5e1;
  letter-spacing: 0;
}
.eye {
  font-size: 32rpx;
  padding: 8rpx 12rpx;
  flex-shrink: 0;
}

.divider {
  height: 1rpx;
  background: #f3f4f6;
}

/* ===== Strength ===== */
.strength-row {
  display: flex;
  align-items: center;
  padding: 0 0 24rpx;
  gap: 16rpx;
}
.strength-label { font-size: 24rpx; color: #9ca3af; width: 100rpx; flex-shrink: 0; }
.strength-bars { display: flex; gap: 8rpx; flex: 1; }
.bar {
  flex: 1;
  height: 8rpx;
  background: #e5e7eb;
  border-radius: 4rpx;
  transition: background 0.2s;
}
.bar-1 { background: #ef4444; }
.bar-2 { background: #f59e0b; }
.bar-3 { background: #10b981; }
.bar-4 { background: #6366f1; }
.strength-text { font-size: 22rpx; font-weight: 600; flex-shrink: 0; }
.text-1 { color: #ef4444; }
.text-2 { color: #f59e0b; }
.text-3 { color: #10b981; }
.text-4 { color: #6366f1; }

/* ===== Match Tip ===== */
.match-tip {
  font-size: 24rpx;
  padding: 0 0 20rpx 160rpx;
}
.match-tip.bad { color: #ef4444; }
.match-tip.good { color: #10b981; }

/* ===== Rules ===== */
.rules {
  background: #fff;
  margin: 0 24rpx 24rpx;
  border-radius: 16rpx;
  padding: 24rpx 28rpx;
  box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.04);
}
.rules-title { font-size: 26rpx; font-weight: 600; color: #1f2937; margin-bottom: 12rpx; }
.rule-item { font-size: 24rpx; color: #6b7280; line-height: 1.8; }

/* ===== Submit ===== */
.submit-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 20rpx 32rpx calc(20rpx + env(safe-area-inset-bottom));
  background: rgba(255, 255, 255, 0.96);
  backdrop-filter: blur(20rpx);
  z-index: 100;
}
.btn-solid {
  width: 100%;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff;
  border-radius: 48rpx;
  padding: 26rpx 0;
  font-size: 32rpx;
  font-weight: 700;
  box-shadow: 0 6rpx 20rpx rgba(99, 102, 241, 0.3);
}
.btn-solid[disabled] {
  background: #d1d5db;
  box-shadow: none;
  color: #9ca3af;
}
</style>
