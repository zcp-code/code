<template>
  <view class="container">
    <!-- Tabs -->
    <view class="tabs">
      <view class="tab" :class="status === '' ? 'active' : ''" @tap="switchTab('')">
        全部<text v-if="counts.all" class="tab-badge">{{ counts.all }}</text>
      </view>
      <view class="tab" :class="status === 'pending' ? 'active' : ''" @tap="switchTab('pending')">
        待确认<text v-if="counts.pending" class="tab-badge orange">{{ counts.pending }}</text>
      </view>
      <view class="tab" :class="status === 'confirmed' ? 'active' : ''" @tap="switchTab('confirmed')">
        已确认<text v-if="counts.confirmed" class="tab-badge green">{{ counts.confirmed }}</text>
      </view>
      <view class="tab" :class="status === 'cancelled' ? 'active' : ''" @tap="switchTab('cancelled')">
        已取消<text v-if="counts.cancelled" class="tab-badge gray">{{ counts.cancelled }}</text>
      </view>
    </view>

    <!-- 列表 -->
    <view class="res-list">
      <view v-if="loading && list.length === 0" class="empty-tip">⏳ 加载中...</view>
      <view v-else-if="list.length === 0" class="empty-tip">
        {{ status === '' ? '📋 暂无预订' :
           status === 'pending' ? '📋 暂无待确认订单' :
           status === 'confirmed' ? '📋 暂无已确认订单' : '📋 暂无已取消订单' }}
      </view>

      <view v-for="r in list" :key="r.id" class="res-card">
        <!-- 卡片头:编号 + 状态 -->
        <view class="res-head">
          <text class="res-no">{{ r.reservation_no }}</text>
          <text class="res-status" :class="'status-' + r.status">{{ statusLabel(r.status) }}</text>
        </view>
        <view class="res-time">{{ formatTime(r.createtime) }}</view>

        <!-- 货品行:封面 + 信息 -->
        <view class="goods-row">
          <image class="goods-cover" :src="r.cover || '/static/placeholder.png'" mode="aspectFill" @tap="goDetail(r.id)"></image>
          <view class="goods-info" @tap="goDetail(r.id)">
            <view class="goods-name ellipsis-2">{{ r.goods_name }}</view>
            <view class="goods-spec">¥{{ Number(r.price).toFixed(2) }}/{{ r.unit }} × {{ r.quantity.toLocaleString() }} {{ r.unit }}</view>
            <view class="goods-total">¥{{ (Number(r.price) * r.quantity).toFixed(2) }}</view>
          </view>
        </view>

        <!-- 采购商 + 店铺 -->
        <view class="kv-row">
          <text class="kv-k">采购商</text>
          <text class="kv-v">{{ r.buyer_name || '-' }}</text>
        </view>
        <view class="kv-row">
          <text class="kv-k">店铺</text>
          <text class="kv-v">{{ r.shop_name || '-' }}</text>
        </view>

        <!-- 底部操作 -->
        <view class="res-actions">
          <view class="btn-gray" @tap="goDetail(r.id)">详情</view>
          <view v-if="r.status === 'pending'" class="btn-warn" @tap.stop="cancel(r)">取消</view>
          <view v-if="r.status === 'pending'" class="btn-outline" @tap.stop="callBuyerFor(r)">📞 联系</view>
          <view v-if="r.status === 'pending'" class="btn-primary" @tap.stop="confirm(r)">✓ 确认</view>
          <view v-else class="btn-outline" @tap="callBuyerFor(r)">📞 联系</view>
        </view>
      </view>

      <view v-if="finished && list.length > 0" class="more-tip">— 没有更多了 —</view>
    </view>

    <custom-tabbar />
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'
import CustomTabbar from '@/components/custom-tabbar/custom-tabbar.vue'

export default {
  components: { CustomTabbar },
  data() {
    return {
      status: '',
      list: [],
      page: 1,
      limit: 20,
      loading: false,
      finished: false,
      counts: { all: 0, pending: 0, confirmed: 0, cancelled: 0 }
    }
  },
  onLoad() { this.load(true) },
  onShow() { this.load(true); this.loadCounts() },
  onPullDownRefresh() {
    Promise.all([this.load(true), this.loadCounts()]).then(() => uni.stopPullDownRefresh())
  },
  onReachBottom() { if (!this.finished && !this.loading) this.load(false) },
  methods: {
    switchTab(s) {
      if (this.status === s) return
      this.status = s
      this.load(true)
    },
    statusLabel(s) {
      return { pending: '待确认', confirmed: '已确认', cancelled: '已取消' }[s] || s
    },
    formatTime(ts) {
      if (!ts) return ''
      const d = new Date(ts * 1000)
      const now = new Date()
      const pad = n => String(n).padStart(2, '0')
      const sameDay = d.toDateString() === now.toDateString()
      const yesterday = new Date(now); yesterday.setDate(yesterday.getDate() - 1)
      const isYesterday = d.toDateString() === yesterday.toDateString()
      const hm = `${pad(d.getHours())}:${pad(d.getMinutes())}`
      if (sameDay) return `今天 ${hm}`
      if (isYesterday) return `昨天 ${hm}`
      return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${hm}`
    },
    async load(reset) {
      if (reset) { this.page = 1; this.list = []; this.finished = false }
      this.loading = true
      try {
        const data = await http.get(api.wholesalerReservations, {
          page: this.page, limit: this.limit, status: this.status
        }, { hideError: true })
        const list = this.list.concat(data.list || [])
        this.list = list
        this.page++
        this.finished = list.length >= data.total
      } catch (e) { uni.showToast({ title: e.message || '操作失败', icon: 'none' }) }
      this.loading = false
    },
    async loadCounts() {
      // 各状态总数(用于 tab 角标)
      const states = ['', 'pending', 'confirmed', 'cancelled']
      await Promise.all(states.map(async s => {
        try {
          const d = await http.get(api.wholesalerReservations, { status: s, page: 1, limit: 1 }, { hideError: true })
          const key = s === '' ? 'all' : s
          this.counts[key] = d.total || 0
        } catch (e) {}
      }))
    },
    goDetail(id) { uni.navigateTo({ url: `/pages/wholesaler-order-detail/wholesaler-order-detail?id=${id}` }) },
    callBuyerFor(r) {
      uni.showToast({ title: '演示:拨打采购商电话', icon: 'none' })
    },
    async confirm(r) {
      uni.showModal({
        title: '确认预订',
        content: `确认「${r.goods_name}」 ×${r.quantity}${r.unit}？\n总价 ¥${(Number(r.price) * r.quantity).toFixed(2)}`,
        success: async ({ confirm }) => {
          if (!confirm) return
          try {
            await http.post(api.wholesalerReservationConfirm, { id: r.id }, { hideError: true })
            uni.showToast({ title: '已确认', icon: 'success' })
            this.load(true); this.loadCounts()
          } catch (e) { uni.showToast({ title: e.message || '操作失败', icon: 'none' }) }
        }
      })
    },
    async cancel(r) {
      uni.showModal({
        title: '取消预订',
        content: `确认取消「${r.goods_name}」的预订?`,
        success: async ({ confirm }) => {
          if (!confirm) return
          try {
            await http.post(api.wholesalerReservationCancel, { id: r.id, reason: '批发商取消' }, { hideError: true })
            uni.showToast({ title: '已取消', icon: 'success' })
            this.load(true); this.loadCounts()
          } catch (e) { uni.showToast({ title: e.message || '操作失败', icon: 'none' }) }
        }
      })
    }
  }
}
</script>

<style scoped>
page { background-color: #f5f5f5; }
.container { padding-bottom: 120rpx; }

/* === Tabs === */
.tabs {
  display: flex;
  background: #fff;
  border-radius: 12rpx;
  margin: 16rpx 24rpx 0;
  overflow: hidden;
}
.tab {
  flex: 1;
  padding: 24rpx 0;
  text-align: center;
  color: #666;
  font-size: 28rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8rpx;
}
.tab.active {
  color: #ff6600;
  border-bottom: 4rpx solid #ff6600;
}
.tab-badge {
  background: #f0f0f0;
  color: #666;
  font-size: 20rpx;
  padding: 2rpx 10rpx;
  border-radius: 20rpx;
  min-width: 32rpx;
}
.tab-badge.orange { background: #fff2e8; color: #ff6600; }
.tab-badge.green  { background: #e8f5e9; color: #27ae60; }
.tab-badge.gray   { background: #f5f5f5; color: #999; }

/* === 列表 === */
.res-list {
  padding: 16rpx 24rpx;
}
.empty-tip {
  background: #fff;
  border-radius: 12rpx;
  text-align: center;
  padding: 100rpx 0;
  color: #999;
  font-size: 26rpx;
}
.more-tip {
  text-align: center;
  color: #ccc;
  font-size: 24rpx;
  padding: 20rpx 0;
}

/* === 预订卡片 === */
.res-card {
  background: #fff;
  border-radius: 12rpx;
  padding: 24rpx;
  margin-bottom: 16rpx;
  box-shadow: 0 2rpx 12rpx rgba(0,0,0,.04);
}
.res-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.res-no {
  color: #999;
  font-size: 22rpx;
  font-family: monospace;
}
.res-status {
  font-size: 22rpx;
  padding: 4rpx 14rpx;
  border-radius: 4rpx;
  font-weight: 500;
}
.status-pending   { color: #ff6600; background: #fff2e8; }
.status-confirmed { color: #27ae60; background: #e8f5e9; }
.status-cancelled { color: #999;     background: #f5f5f5; }

.res-time {
  color: #999;
  font-size: 22rpx;
  margin-top: 6rpx;
}

/* 货品行 */
.goods-row {
  display: flex;
  gap: 16rpx;
  padding: 16rpx 0;
  align-items: center;
}
.goods-cover {
  width: 120rpx;
  height: 120rpx;
  border-radius: 8rpx;
  background: #f5f5f5;
  flex-shrink: 0;
}
.goods-info { flex: 1; min-width: 0; }
.goods-name {
  font-size: 28rpx;
  font-weight: 500;
  color: #111;
  line-height: 1.4;
}
.goods-spec {
  color: #999;
  font-size: 22rpx;
  margin-top: 6rpx;
}
.goods-total {
  color: #ff6600;
  font-size: 32rpx;
  font-weight: 700;
  margin-top: 6rpx;
}

/* 采购商 + 店铺 */
.kv-row {
  display: flex;
  font-size: 24rpx;
  padding: 6rpx 0;
  align-items: center;
}
.kv-k { color: #999; width: 110rpx; flex-shrink: 0; }
.kv-v { color: #555; flex: 1; }

/* 操作按钮 */
.res-actions {
  display: flex;
  gap: 12rpx;
  margin-top: 16rpx;
}
.btn-gray {
  flex: 1;
  background: #f5f5f5;
  color: #666;
  border-radius: 32rpx;
  padding: 16rpx 0;
  font-size: 26rpx;
  text-align: center;
}
.btn-gray:active { background: #e5e5e5; }
.btn-warn {
  flex: 1;
  background: #fff;
  color: #e55a00;
  border: 1rpx solid #ff6600;
  border-radius: 32rpx;
  padding: 16rpx 0;
  font-size: 26rpx;
  text-align: center;
}
.btn-outline {
  flex: 1;
  background: #fff;
  color: #ff6600;
  border: 1rpx solid #ff6600;
  border-radius: 32rpx;
  padding: 16rpx 0;
  font-size: 26rpx;
  text-align: center;
}
.btn-primary {
  flex: 1.5;
  background: #ff6600;
  color: #fff;
  border-radius: 32rpx;
  padding: 16rpx 0;
  font-size: 26rpx;
  text-align: center;
  font-weight: 500;
}
.btn-primary:active { background: #e55a00; }
</style>
