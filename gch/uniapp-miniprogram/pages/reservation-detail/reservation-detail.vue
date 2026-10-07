<template>
  <view class="container" v-if="detail">
    <view class="status-bar" :class="'status-' + detail.status">
      {{statusLabel(detail.status)}}
    </view>

    <view class="card">
      <view class="row">
        <text class="label">预订编号</text>
        <text class="value">{{detail.reservation_no}}</text>
      </view>
      <view class="row">
        <text class="label">货品</text>
        <text class="value">{{detail.goods_name}}</text>
      </view>
      <view class="row">
        <text class="label">单价</text>
        <text class="value">¥{{detail.price}}/{{detail.unit}}</text>
      </view>
      <view class="row">
        <text class="label">数量</text>
        <text class="value">{{detail.quantity}} {{detail.unit}}</text>
      </view>
      <view class="row total">
        <text class="label">合计</text>
        <text class="value">¥{{detail.price * detail.quantity}}</text>
      </view>
    </view>

    <view class="card">
      <view class="row"><text class="label">店铺</text><text class="value">{{detail.shop_name || '-'}}</text></view>
      <view class="row"><text class="label">批发商</text><text class="value">{{detail.wholesaler_name || '-'}}</text></view>
      <view class="row"><text class="label">预订时间</text><text class="value">{{formatTime(detail.createtime)}}</text></view>
      <view v-if="detail.confirm_time" class="row"><text class="label">确认时间</text><text class="value">{{formatTime(detail.confirm_time)}}</text></view>
      <view v-if="detail.cancel_time" class="row">
        <text class="label">取消时间</text>
        <text class="value">{{formatTime(detail.cancel_time)}}</text>
      </view>
      <view v-if="detail.cancel_reason" class="row">
        <text class="label">取消原因</text>
        <text class="value">{{detail.cancel_reason}}</text>
      </view>
    </view>

    <view v-if="detail.status === 'pending'" class="action-bar">
      <button class="btn warn" @tap="cancel">取消预订</button>
    </view>
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
    // 按当前角色选择 API:批发商走 wholesaler 路径,否则走 buyer 路径
    detailUrl()  { return this.role === 'wholesaler' ? api.wholesalerReservationDetail(this.id)  : api.reservationDetail(this.id) },
    cancelUrl()  { return this.role === 'wholesaler' ? api.wholesalerReservationCancel         : api.reservationCancel },
    cancelReason(){ return this.role === 'wholesaler' ? '批发商取消' : '买家主动取消' }
  },
  methods: {
    async load() {
      try {
        this.detail = await http.get(this.detailUrl, {}, { hideError: true })
      } catch (e) {}
    },
    statusLabel(s) {
      return { pending: '待确认', confirmed: '已确认', cancelled: '已取消' }[s] || s
    },
    formatTime(ts) {
      if (!ts) return ''
      const d = new Date(ts * 1000)
      return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`
    },
    cancel() {
      uni.showModal({
        title: '取消预订',
        content: '确认取消？',
        success: async ({ confirm }) => {
          if (!confirm) return
          try {
            await http.post(this.cancelUrl, { id: this.id, reason: this.cancelReason }, { hideError: true })
            uni.showToast({ title: '已取消', icon: 'success' })
            this.load()
          } catch (e) {}
        }
      })
    }
  }
}
</script>

<style scoped>
.status-bar { text-align: center; color: #fff; padding: 24rpx 0; font-size: 30rpx; font-weight: 500; margin-bottom: 20rpx; border-radius: 12rpx; }
.status-pending { background: #ff9800; }
.status-confirmed { background: #4caf50; }
.status-cancelled { background: #999; }
.card { background: #fff; border-radius: 12rpx; padding: 20rpx 24rpx; margin-bottom: 20rpx; }
.row { display: flex; padding: 20rpx 0; border-bottom: 1rpx solid #f5f5f5; }
.row:last-child { border-bottom: none; }
.row.total .value { color: #ff6b35; font-size: 36rpx; font-weight: 600; }
.row .label { width: 180rpx; color: #999; }
.row .value { flex: 1; font-size: 28rpx; }
.action-bar { position: fixed; bottom: 0; left: 0; right: 0; padding: 20rpx; background: #fff; box-shadow: 0 -2rpx 8rpx rgba(0,0,0,.04); }
.btn.warn { background: #fff; color: #f44336; border: 1rpx solid #f44336; border-radius: 8rpx; padding: 20rpx 40rpx; font-size: 28rpx; }
</style>
