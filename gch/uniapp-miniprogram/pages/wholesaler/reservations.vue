<template>
  <view class="container">
    <view class="tabs">
      <view class="tab" :class="status === '' ? 'active' : ''" @tap="switchTab('')">全部</view>
      <view class="tab" :class="status === 'pending' ? 'active' : ''" @tap="switchTab('pending')">待确认</view>
      <view class="tab" :class="status === 'confirmed' ? 'active' : ''" @tap="switchTab('confirmed')">已确认</view>
      <view class="tab" :class="status === 'cancelled' ? 'active' : ''" @tap="switchTab('cancelled')">已取消</view>
    </view>

    <view class="res-list">
      <view v-for="r in list" :key="r.id" class="res-card" @tap="goDetail(r.id)">
        <view class="res-row">
          <text class="res-no">{{r.reservation_no}}</text>
          <text class="res-status" :class="'status-' + r.status">{{statusLabel(r.status)}}</text>
        </view>
        <view class="res-name ellipsis-2">{{r.goods_name}} × {{r.quantity}}</view>
        <view class="res-meta">
          <text>采购商:{{r.buyer_name}}</text>
          <text class="amount">¥{{r.price * r.quantity}}</text>
        </view>
        <view class="res-meta">预订时间:{{formatTime(r.createtime)}}</view>
        <view v-if="r.status === 'pending'" class="res-actions">
          <view class="btn small primary" @tap.stop="confirm(r)">确认预订</view>
          <view class="btn small warn" @tap.stop="cancel(r)">取消</view>
        </view>
      </view>
      <view v-if="loading" class="loading">加载中...</view>
      <view v-else-if="list.length === 0" class="empty">暂无预订</view>
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
      finished: false
    }
  },
  onLoad() { this.load(true) },
  onShow() { this.load(true) },
  onPullDownRefresh() { this.load(true).then(() => uni.stopPullDownRefresh()) },
  onReachBottom() { if (!this.finished && !this.loading) this.load(false) },
  methods: {
    switchTab(s) { this.status = s; this.load(true) },
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
      } catch (e) { uni.showToast({ title: e.message || "操作失败", icon: "none" }) }
      this.loading = false
    },
    goDetail(id) { uni.navigateTo({ url: `/pages/reservation-detail/reservation-detail?id=${id}` }) },
    confirm(r) {
      uni.showModal({
        title: '确认预订',
        content: `确认「${r.goods_name}」的预订?`,
        success: async ({ confirm }) => {
          if (!confirm) return
          try {
            await http.post(api.wholesalerReservationConfirm, { id: r.id }, { hideError: true })
            uni.showToast({ title: '已确认', icon: 'success' })
            this.load(true)
          } catch (e) { uni.showToast({ title: e.message || "操作失败", icon: "none" }) }
        }
      })
    },
    cancel(r) {
      uni.showModal({
        title: '取消预订',
        content: `确认取消「${r.goods_name}」？`,
        success: async ({ confirm }) => {
          if (!confirm) return
          try {
            await http.post(api.wholesalerReservationCancel, { id: r.id, reason: '批发商取消' }, { hideError: true })
            uni.showToast({ title: '已取消', icon: 'success' })
            this.load(true)
          } catch (e) { uni.showToast({ title: e.message || "操作失败", icon: "none" }) }
        }
      })
    }
  }
}
</script>

<style scoped>
.tabs { display: flex; background: #fff; border-radius: 12rpx; margin-bottom: 20rpx; }
.tab { flex: 1; padding: 24rpx 0; text-align: center; color: #666; font-size: 28rpx; }
.tab.active { color: #ff6600; border-bottom: 4rpx solid #ff6600; }
.res-card { background: #fff; border-radius: 12rpx; padding: 24rpx; margin-bottom: 20rpx; }
.res-row { display: flex; justify-content: space-between; }
.res-no { color: #999; font-size: 24rpx; }
.res-status { font-size: 24rpx; padding: 4rpx 16rpx; border-radius: 4rpx; }
.status-pending { color: #ff9800; background: #fff3e0; }
.status-confirmed { color: #4caf50; background: #e8f5e9; }
.status-cancelled { color: #999; background: #f5f5f5; }
.res-name { font-size: 28rpx; margin-top: 10rpx; }
.res-meta { color: #999; font-size: 24rpx; margin-top: 6rpx; display: flex; justify-content: space-between; }
.res-meta .amount { color: #ff6600; font-weight: 500; font-size: 28rpx; }
.res-actions { display: flex; gap: 16rpx; margin-top: 16rpx; }
.btn.small { padding: 12rpx 28rpx; border-radius: 8rpx; font-size: 26rpx; }
.btn.primary { background: #ff6600; color: #fff; }
.btn.warn { background: #fff; color: #f44336; border: 1rpx solid #f44336; }
.loading, .empty { text-align: center; color: #999; padding: 60rpx 0; font-size: 26rpx; }
</style>
