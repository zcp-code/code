<template>
  <view class="container">
    <view class="title-bar">
      <text class="title">订单列表 · 全部</text>
    </view>

    <!-- 未登录 -->
    <view v-if="!isLogin" class="empty-card">
      <text class="empty-icon">🔒</text>
      <view class="empty-text">登录后查看订单</view>
      <button class="btn-primary" @tap="goLogin">立即登录</button>
    </view>

    <!-- 已登录 -->
    <block v-else>
      <view v-if="loading && list.length === 0" class="empty-card">
        <text class="empty-icon">📋</text>
        <view class="empty-text">加载中...</view>
      </view>

      <view v-else-if="list.length === 0" class="empty-card">
        <text class="empty-icon">📋</text>
        <view class="empty-text">暂无订单</view>
      </view>

      <view v-for="r in list" :key="r.id" class="order-card">
        <view class="order-head">
          <text class="order-no">{{ r.reservation_no }}</text>
          <text class="order-status" :class="'status-' + r.status">{{ statusText(r.status) }}</text>
        </view>
        <view class="order-body" @tap="goDetail(r.id)">
          <image class="emoji" :src="r.cover || '/static/placeholder.png'" mode="aspectFill"></image>
          <view class="info">
            <view class="name ellipsis-2">{{ r.goods_name }}</view>
            <view class="amount">¥{{ formatPrice(r.price) }}/{{ r.unit }} × {{ r.quantity.toLocaleString() }} {{ r.unit }}</view>
            <view class="shop">{{ r.shop_name || '-' }} · {{ formatTime(r.createtime) }}</view>
          </view>
        </view>
        <view v-if="r.status === 'pending'" class="order-actions">
          <view class="btn-gray" @tap.stop="cancelOrder(r)">取消预订</view>
          <view class="btn-primary" @tap.stop="goDetail(r.id)">订单详情</view>
        </view>
      </view>
    </block>

    <custom-tabbar />
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'
import userStore from '@/store/user.js'
import CustomTabbar from '@/components/custom-tabbar/custom-tabbar.vue'

const FOOD_EMOJI = ['🍎', '🍌', '🍇', '🍊', '🍓', '🍑', '🥬', '🥦', '🥕', '🍅', '🌽', '🥔', '🥒', '🍆', '🌶️', '🧅', '🥭', '🍍', '🥥', '🦐', '🦀', '🐟', '🐠', '🍤', '🥚', '🍚', '🥟']

export default {
  components: { CustomTabbar },
  data() {
    return {
      list: [],
      page: 1,
      limit: 20,
      loading: false,
      finished: false
    }
  },
  computed: {
    // 真实登录才显示(token ≥ 32 字符 + role=buyer)
    // mock token < 32 时不显示订单(避免 401)
    isLogin() {
      const t = userStore.token || ''
      return t.length >= 32 && userStore.role === 'buyer'
    }
  },
  onShow() {
    if (this.isLogin) this.load(true)
  },
  onPullDownRefresh() {
    if (this.isLogin) this.load(true).then(() => uni.stopPullDownRefresh())
  },
  methods: {
    async load(reset) {
      if (reset) { this.page = 1; this.list = []; this.finished = false }
      this.loading = true
      try {
        const data = await http.get(api.reservationList, {
          page: this.page, limit: this.limit
        }, { hideError: true })
        const list = this.list.concat(data.list || [])
        this.list = list
        this.page++
        this.finished = list.length >= data.total
      } catch (e) {}
      this.loading = false
    },
    statusText(s) {
      return { pending: '待确认', confirmed: '已确认', cancelled: '已取消' }[s] || s
    },
    cancelOrder(r) {
      uni.showModal({
        title: '取消预订',
        content: `确认取消「${r.goods_name}」?`,
        success: async ({ confirm }) => {
          if (!confirm) return
          try {
            await http.post(api.reservationCancel, { id: r.id, reason: '买家取消' }, { hideError: true })
            uni.showToast({ title: '已取消预订', icon: 'success' })
            this.load(true)
          } catch (e) {}
        }
      })
    },
    goDetail(id) { uni.navigateTo({ url: `/pages/reservation-detail/reservation-detail?id=${id}` }) },
    goLogin() { uni.navigateTo({ url: '/pages/login/account?role=buyer' }) },
    emojiOf(name) {
      const code = (name || '').split('').reduce((s, c) => s + c.charCodeAt(0), 0)
      return FOOD_EMOJI[code % FOOD_EMOJI.length]
    },
    formatTime(ts) {
      if (!ts) return ''
      const d = new Date(ts * 1000)
      return `${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')} ${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`
    },
    formatPrice(p) {
      // 后端 DECIMAL 字段返回字符串(如 "5.00"),必须先转数字才能 toFixed
      const n = parseFloat(p)
      return isNaN(n) ? '0.00' : n.toFixed(2)
    }
  }
}
</script>

<style scoped>
.container { padding-bottom: 40rpx; }

.title-bar {
  background: #fff;
  padding: 24rpx 30rpx;
  border-bottom: 1rpx solid #f2f2f2;
}
.title-bar .title { font-size: 34rpx; font-weight: 700; color: #111; }

.empty-card {
  background: #fff;
  border-radius: 12rpx;
  margin: 20rpx 24rpx;
  padding: 100rpx 0;
  text-align: center;
}
.empty-icon { font-size: 100rpx; display: block; }
.empty-text { color: #bbb; font-size: 26rpx; margin: 16rpx 0 30rpx; }
.btn-primary { background: #ff6600; color: #fff; border-radius: 24rpx; padding: 20rpx 60rpx; font-size: 28rpx; }

.order-card {
  background: #fff;
  border-radius: 12rpx;
  margin: 20rpx 24rpx;
  padding: 0;
  overflow: hidden;
}
.order-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20rpx 28rpx;
  border-bottom: 1rpx solid #f2f2f2;
}
.order-no { color: #999; font-size: 22rpx; }
.order-status {
  font-size: 22rpx;
  padding: 4rpx 12rpx;
  border-radius: 4rpx;
}
.status-pending { color: #ff6600; background: #fff1e6; }
.status-confirmed { color: #07c160; background: #e8f7ef; }
.status-cancelled { color: #999; background: #f2f2f2; }

.order-body { display: flex; gap: 20rpx; padding: 20rpx 28rpx; }
.emoji {
  width: 100rpx;
  height: 100rpx;
  border-radius: 8rpx;
  background: linear-gradient(135deg, #fff3e0, #ffe0b2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 50rpx;
  flex-shrink: 0;
}
.info { flex: 1; min-width: 0; }
.name { font-size: 28rpx; font-weight: 600; color: #111; }
.amount { color: #ff6600; font-size: 26rpx; margin-top: 8rpx; }
.shop { color: #999; font-size: 22rpx; margin-top: 6rpx; }

.order-actions {
  display: flex;
  justify-content: flex-end;
  gap: 16rpx;
  padding: 16rpx 28rpx;
  border-top: 1rpx solid #f2f2f2;
}
.btn-gray {
  background: #f2f2f2;
  color: #666;
  border-radius: 16rpx;
  padding: 14rpx 28rpx;
  font-size: 26rpx;
}
.btn-primary {
  background: #ff6600;
  color: #fff;
  border-radius: 16rpx;
  padding: 14rpx 28rpx;
  font-size: 26rpx;
}
</style>
