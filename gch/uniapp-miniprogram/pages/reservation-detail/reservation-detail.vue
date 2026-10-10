<template>
  <view class="container" v-if="detail">

    <!-- 顶部状态 Hero 区 -->
    <view class="hero" :class="'hero-' + detail.status">
      <view class="hero-icon">{{ heroIcon }}</view>
      <view class="hero-title">{{ statusLabel(detail.status) }}</view>
      <view class="hero-desc">{{ heroDesc }}</view>
    </view>

    <!-- 商品卡(主角) -->
    <view class="card goods-card" @tap="onGoodsTap">
      <image class="goods-img" :src="detail.cover || '/static/placeholder.png'" mode="aspectFill"></image>
      <view class="goods-body">
        <view class="goods-name">{{ detail.goods_name || '-' }}</view>
        <view class="price-row">
          <text class="price-num">¥{{ formatPrice(detail.price) }}</text>
          <text class="price-unit">/{{ detail.unit || '件' }}</text>
        </view>
        <view class="qty-row">× {{ (detail.quantity || 0).toLocaleString() }} {{ detail.unit || '' }}</view>
      </view>
    </view>

    <!-- 合计金额 -->
    <view class="card total-card">
      <view class="total-line">
        <text class="total-label">订单总额</text>
        <text class="total-value">¥{{ formatTotal(detail.price, detail.quantity) }}</text>
      </view>
      <view class="total-sub">
        含 {{ (detail.quantity || 0).toLocaleString() }} {{ detail.unit || '' }} × ¥{{ formatPrice(detail.price) }}/{{ detail.unit || '件' }}
      </view>
    </view>

    <!-- 订单信息 -->
    <view class="card info-card">
      <view class="card-title">
        <text class="card-title-bar"></text>
        <text class="card-title-text">订单信息</text>
      </view>
      <view class="info-list">
        <view class="info-row">
          <text class="info-label">预订编号</text>
          <view class="info-value-wrap">
            <text class="info-value" :selectable="true">{{ detail.reservation_no }}</text>
            <text class="info-copy" @tap="copy(detail.reservation_no, '已复制订单号')">复制</text>
          </view>
        </view>
        <view class="info-row">
          <text class="info-label">店铺</text>
          <text class="info-value">{{ detail.shop_name || '-' }}</text>
        </view>
        <view v-if="detail.wholesaler_name" class="info-row">
          <text class="info-label">批发商</text>
          <text class="info-value">{{ detail.wholesaler_name }}</text>
        </view>
      </view>
    </view>

    <!-- 时间线(履约轨迹) -->
    <view class="card timeline-card">
      <view class="card-title">
        <text class="card-title-bar"></text>
        <text class="card-title-text">订单进度</text>
      </view>
      <view class="timeline">
        <view class="tl-item" :class="{ active: true }">
          <view class="tl-dot"></view>
          <view class="tl-content">
            <view class="tl-title">提交预订</view>
            <view class="tl-time">{{ formatTime(detail.createtime) }}</view>
          </view>
        </view>
        <view v-if="detail.confirm_time" class="tl-item" :class="{ active: true }">
          <view class="tl-dot"></view>
          <view class="tl-content">
            <view class="tl-title">批发商确认</view>
            <view class="tl-time">{{ formatTime(detail.confirm_time) }}</view>
          </view>
        </view>
        <view v-if="detail.cancel_time" class="tl-item cancel">
          <view class="tl-dot"></view>
          <view class="tl-content">
            <view class="tl-title">订单已取消</view>
            <view class="tl-time">{{ formatTime(detail.cancel_time) }}</view>
            <view v-if="detail.cancel_reason" class="tl-reason">原因:{{ detail.cancel_reason }}</view>
          </view>
        </view>
        <view v-if="!detail.confirm_time && !detail.cancel_time && detail.status === 'pending'" class="tl-item pending">
          <view class="tl-dot"></view>
          <view class="tl-content">
            <view class="tl-title dim">等待批发商确认</view>
            <view class="tl-time">预计 24 小时内回复</view>
          </view>
        </view>
      </view>
    </view>

    <!-- 底部留白 -->
    <view class="bottom-pad"></view>

    <!-- 底部操作条(待确认时可取消) -->
    <view v-if="detail.status === 'pending'" class="action-bar">
      <view class="action-inner">
        <view class="btn-ghost" @tap="cancel">取消预订</view>
        <view class="btn-solid" @tap="contactShop">联系商家</view>
      </view>
    </view>
  </view>

  <!-- 加载中 -->
  <view v-else class="loading-state">
    <view class="loading-emoji">⏳</view>
    <view class="loading-text">加载中...</view>
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'
import userStore from '@/store/user.js'

export default {
  data() { return { id: 0, detail: null, role: 'buyer' } },
  onLoad(q) {
    this.id = q.id
    this.role = userStore.role || 'buyer'
    this.load()
  },
  computed: {
    detailUrl()  { return this.role === 'wholesaler' ? api.wholesalerReservationDetail(this.id)  : api.reservationDetail(this.id) },
    cancelUrl()  { return this.role === 'wholesaler' ? api.wholesalerReservationCancel         : api.reservationCancel },
    cancelReason(){ return this.role === 'wholesaler' ? '批发商取消' : '买家主动取消' },
    heroIcon() {
      return { pending: '⏳', confirmed: '✅', cancelled: '✕' }[this.detail.status] || '•'
    },
    heroDesc() {
      const map = {
        pending: '已收到预订,等待批发商确认',
        confirmed: '批发商已确认,可通过联系商家沟通取货',
        cancelled: '本次预订已取消'
      }
      return map[this.detail.status] || ''
    }
  },
  methods: {
    async load() {
      try {
        this.detail = await http.get(this.detailUrl, {}, { hideError: true })
      } catch (e) {}
    },
    statusLabel(s) {
      return { pending: '待批发商确认', confirmed: '已确认', cancelled: '已取消' }[s] || s
    },
    onGoodsTap() {
      if (this.detail && this.detail.goods_id) {
        uni.navigateTo({ url: `/pages/goods/detail?id=${this.detail.goods_id}` })
      }
    },
    contactShop() {
      // 简易占位:实际可复制店铺电话 / 调起 wx.makePhoneCall
      uni.showToast({ title: '请到店铺页联系商家', icon: 'none' })
    },
    copy(text, msg) {
      uni.setClipboardData({
        data: text,
        success: () => uni.showToast({ title: msg || '已复制', icon: 'success' })
      })
    },
    formatTime(ts) {
      if (!ts) return ''
      const d = new Date(ts * 1000)
      const pad = n => String(n).padStart(2, '0')
      return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`
    },
    formatPrice(p) {
      const n = parseFloat(p)
      return isNaN(n) ? '0.00' : n.toFixed(2)
    },
    formatTotal(price, qty) {
      const p = parseFloat(price), q = parseInt(qty, 10) || 0
      return (isNaN(p) ? 0 : p * q).toFixed(2)
    },
    cancel() {
      uni.showModal({
        title: '取消预订',
        content: '确认取消本次预订?取消后无法恢复。',
        confirmText: '确认取消',
        cancelText: '再想想',
        confirmColor: '#ff6600',
        success: async ({ confirm }) => {
          if (!confirm) return
          try {
            await http.post(this.cancelUrl, { id: this.id, reason: this.cancelReason }, { hideError: true })
            uni.showToast({ title: '已取消', icon: 'success' })
            setTimeout(() => this.load(), 600)
          } catch (e) {}
        }
      })
    }
  }
}
</script>

<style scoped>
.container { min-height: 100vh; background: #f5f7fa; padding-bottom: 200rpx; }

/* ===== Hero 顶部状态区 ===== */
.hero {
  padding: 60rpx 40rpx 80rpx;
  text-align: center;
  position: relative;
}
.hero-icon { font-size: 96rpx; display: block; line-height: 1; margin-bottom: 20rpx; }
.hero-title { font-size: 38rpx; font-weight: 700; color: #fff; margin-bottom: 12rpx; }
.hero-desc { font-size: 26rpx; color: rgba(255, 255, 255, 0.85); }

.hero-pending {
  background: linear-gradient(135deg, #f59e0b 0%, #fb923c 100%);
}
.hero-confirmed {
  background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
}
.hero-cancelled {
  background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
}

/* ===== Cards ===== */
.card {
  background: #fff;
  margin: 0 24rpx 24rpx;
  border-radius: 20rpx;
  box-shadow: 0 2rpx 16rpx rgba(0, 0, 0, 0.04);
  overflow: hidden;
}

.card-title {
  display: flex;
  align-items: center;
  padding: 28rpx 28rpx 16rpx;
  border-bottom: 1rpx solid #f3f4f6;
}
.card-title-bar {
  width: 6rpx;
  height: 28rpx;
  background: linear-gradient(180deg, #ff6600, #ff8c42);
  border-radius: 3rpx;
  margin-right: 12rpx;
}
.card-title-text { font-size: 30rpx; font-weight: 700; color: #1f2937; }

/* ===== Goods Card ===== */
.goods-card { display: flex; gap: 24rpx; padding: 28rpx; }
.goods-img {
  width: 180rpx;
  height: 180rpx;
  border-radius: 16rpx;
  background: #f3f4f6;
  flex-shrink: 0;
}
.goods-body { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: space-between; }
.goods-name { font-size: 30rpx; color: #1f2937; font-weight: 600; line-height: 1.4; }
.price-row { margin-top: 16rpx; display: flex; align-items: baseline; }
.price-num { font-size: 40rpx; color: #ff6600; font-weight: 700; }
.price-unit { font-size: 24rpx; color: #9ca3af; margin-left: 4rpx; }
.qty-row { font-size: 26rpx; color: #6b7280; margin-top: 6rpx; }

/* ===== Total Card ===== */
.total-card { padding: 28rpx; }
.total-line { display: flex; justify-content: space-between; align-items: baseline; }
.total-label { font-size: 28rpx; color: #6b7280; }
.total-value { font-size: 44rpx; color: #ff6600; font-weight: 700; }
.total-sub { font-size: 24rpx; color: #9ca3af; margin-top: 8rpx; text-align: right; }

/* ===== Info Card ===== */
.info-list { padding: 8rpx 28rpx 28rpx; }
.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 22rpx 0;
  border-bottom: 1rpx solid #f3f4f6;
}
.info-row:last-child { border-bottom: none; }
.info-label { font-size: 26rpx; color: #9ca3af; flex-shrink: 0; }
.info-value-wrap { display: flex; align-items: center; gap: 16rpx; min-width: 0; flex: 1; justify-content: flex-end; }
.info-value {
  font-size: 28rpx;
  color: #1f2937;
  text-align: right;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  max-width: 360rpx;
}
.info-copy {
  font-size: 22rpx;
  color: #ff6600;
  background: #fff1e6;
  padding: 4rpx 14rpx;
  border-radius: 4rpx;
  flex-shrink: 0;
}

/* ===== Timeline ===== */
.timeline { padding: 24rpx 28rpx 28rpx; }
.tl-item {
  display: flex;
  position: relative;
  padding-bottom: 32rpx;
}
.tl-item:last-child { padding-bottom: 0; }
.tl-item:not(:last-child)::before {
  content: '';
  position: absolute;
  left: 11rpx;
  top: 24rpx;
  bottom: 0;
  width: 2rpx;
  background: #e5e7eb;
}
.tl-item.active:not(:last-child)::before,
.tl-item:last-child::before { background: transparent; }

.tl-dot {
  width: 24rpx;
  height: 24rpx;
  border-radius: 50%;
  background: #cbd5e1;
  margin-right: 24rpx;
  margin-top: 6rpx;
  flex-shrink: 0;
  position: relative;
  z-index: 1;
}
.tl-item.active .tl-dot {
  background: #ff6600;
  box-shadow: 0 0 0 6rpx #fff1e6;
}
.tl-item.cancel .tl-dot { background: #9ca3af; box-shadow: 0 0 0 6rpx #f3f4f6; }
.tl-item.pending .tl-dot { background: #cbd5e1; }

.tl-content { flex: 1; min-width: 0; }
.tl-title { font-size: 28rpx; color: #1f2937; font-weight: 600; }
.tl-title.dim { color: #9ca3af; font-weight: 400; }
.tl-time { font-size: 24rpx; color: #9ca3af; margin-top: 6rpx; }
.tl-reason { font-size: 24rpx; color: #ef4444; margin-top: 8rpx; }

/* ===== Bottom padding ===== */
.bottom-pad { height: 40rpx; }

/* ===== Action Bar ===== */
.action-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 20rpx 32rpx calc(20rpx + env(safe-area-inset-bottom));
  background: rgba(255, 255, 255, 0.96);
  backdrop-filter: blur(20rpx);
  box-shadow: 0 -4rpx 24rpx rgba(0, 0, 0, 0.06);
  z-index: 100;
}
.action-inner { display: flex; gap: 20rpx; }

.btn-ghost {
  flex: 1;
  text-align: center;
  border: 2rpx solid #e5e7eb;
  color: #6b7280;
  border-radius: 48rpx;
  padding: 22rpx 0;
  font-size: 28rpx;
  font-weight: 600;
}
.btn-solid {
  flex: 1;
  text-align: center;
  background: linear-gradient(135deg, #ff6600, #ff8c42);
  color: #fff;
  border-radius: 48rpx;
  padding: 22rpx 0;
  font-size: 28rpx;
  font-weight: 600;
  box-shadow: 0 6rpx 20rpx rgba(255, 102, 0, 0.3);
}

/* ===== Loading State ===== */
.loading-state {
  min-height: 100vh;
  background: #f5f7fa;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.loading-emoji { font-size: 120rpx; }
.loading-text { font-size: 28rpx; color: #9ca3af; margin-top: 20rpx; }
</style>
