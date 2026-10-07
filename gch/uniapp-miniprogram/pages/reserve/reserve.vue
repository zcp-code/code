<template>
  <view class="container" v-if="goods">
    <view class="card goods-summary">
      <image :src="goods.cover || '/static/placeholder.png'" mode="aspectFill" class="cover"></image>
      <view class="info">
        <view class="name ellipsis-2">{{goods.name}}</view>
        <view class="price">¥{{goods.price}}/{{goods.unit}}</view>
        <view class="available">可订 {{goods.available}} {{goods.unit}}</view>
      </view>
    </view>

    <view class="card">
      <view class="form-row">
        <text class="label">预订数量</text>
        <view class="qty-control">
          <view class="qty-btn" @tap="decQty">−</view>
          <text class="qty-value">{{quantity}}</text>
          <view class="qty-btn" @tap="incQty">+</view>
        </view>
      </view>
      <view class="form-row total">
        <text class="label">合计</text>
        <text class="total-price">¥{{goods.price * quantity}}</text>
      </view>
    </view>

    <view class="card">
      <view class="tip">📌 预订提交后，批发商会收到通知并确认。请保持电话畅通。</view>
    </view>

    <button class="btn-primary submit-btn" @tap="submit">提交预订</button>
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'

export default {
  data() {
    return { id: 0, goods: null, quantity: 1 }
  },
  onLoad(q) {
    this.id = q.id
    this.load()
  },
  methods: {
    async load() {
      try {
        const goods = await http.get(api.goodsDetail(this.id), {}, { hideError: true })
        this.goods = goods
        this.quantity = Math.min(1, goods.available || 1)
      } catch (e) {}
    },
    decQty() { if (this.quantity > 1) this.quantity-- },
    incQty() {
      const max = this.goods ? (this.goods.available || 99) : 99
      if (this.quantity < max) this.quantity++
    },
    async submit() {
      if (!this.goods) return
      try {
        const order = await http.post(api.reservationCreate, {
          goods_id: this.id, quantity: this.quantity
        }, { hideError: true })
        uni.showModal({
          title: '预订成功',
          content: `订单号: ${order.reservation_no}\n等待批发商确认`,
          showCancel: false,
          success: () => uni.switchTab({ url: '/pages/profile/profile' })
        })
      } catch (e) {}
    }
  }
}
</script>

<style scoped>
.goods-summary { display: flex; }
.cover { width: 160rpx; height: 160rpx; border-radius: 8rpx; background: #f5f5f5; }
.info { flex: 1; margin-left: 20rpx; display: flex; flex-direction: column; justify-content: space-between; }
.name { font-size: 30rpx; font-weight: 500; }
.price { color: #ff6b35; font-size: 32rpx; font-weight: 600; }
.available { color: #999; font-size: 24rpx; }
.form-row { display: flex; align-items: center; justify-content: space-between; padding: 24rpx 0; border-bottom: 1rpx solid #eee; }
.form-row:last-child { border-bottom: none; }
.label { color: #666; }
.qty-control { display: flex; align-items: center; }
.qty-btn { width: 60rpx; height: 60rpx; line-height: 60rpx; text-align: center; background: #f5f5f5; border-radius: 8rpx; font-size: 36rpx; }
.qty-value { min-width: 80rpx; text-align: center; font-size: 32rpx; padding: 0 20rpx; }
.total .total-price { color: #ff6b35; font-size: 38rpx; font-weight: 600; }
.tip { color: #999; font-size: 26rpx; line-height: 1.6; padding: 10rpx 0; }
.submit-btn { margin-top: 40rpx; }
</style>
