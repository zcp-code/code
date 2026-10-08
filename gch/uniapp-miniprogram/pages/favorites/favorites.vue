<template>
  <view class="container">
    <view class="tabs">
      <view class="tab" :class="type === 'goods' ? 'active' : ''" @tap="switchTab('goods')">货品</view>
      <view class="tab" :class="type === 'shop' ? 'active' : ''" @tap="switchTab('shop')">店铺</view>
    </view>

    <view v-if="loading && list.length === 0" class="empty-card">
      <text class="empty-icon">⏳</text>
      <view class="empty-text">加载中...</view>
    </view>
    <view v-else-if="list.length === 0" class="empty-card">
      <text class="empty-icon">⭐</text>
      <view class="empty-text">暂无{{ type === 'shop' ? '收藏店铺' : '收藏货品' }}</view>
    </view>

    <!-- 货品列表 -->
    <view v-else-if="type === 'goods'" class="list">
      <view v-for="g in list" :key="g.id" class="card" @tap="goGoods(g.id)">
        <image class="emoji-cover" :src="g.cover || '/static/placeholder.png'" mode="aspectFill"></image>
        <view class="info">
          <view class="name ellipsis-2">{{ g.name }}</view>
          <view class="meta">{{ g.shop_name || '-' }} · 库存 {{ (g.total_stock || 0).toLocaleString() }} {{ g.unit }}</view>
          <view class="price">¥{{ Number(g.price).toFixed(2) }}/{{ g.unit }}</view>
        </view>
      </view>
    </view>

    <!-- 店铺列表 -->
    <view v-else class="list">
      <view v-for="s in list" :key="s.id" class="shop-card" @tap="goShop(s.id)">
        <view class="shop-emoji">🏬</view>
        <view class="info">
          <view class="name">{{ s.name }}</view>
          <view class="meta">📍 {{ s.position || '暂无地址' }}</view>
          <view class="meta">🕐 {{ s.business_hours || '营业时间未知' }}</view>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'

export default {
  data() {
    return {
      type: 'goods',     // goods / shop
      list: [],
      page: 1,
      limit: 20,
      loading: false,
      finished: false
    }
  },
  onShow() { this.load(true) },
  onPullDownRefresh() {
    this.load(true).then(() => uni.stopPullDownRefresh())
  },
  methods: {
    switchTab(t) {
      if (this.type === t) return
      this.type = t
      this.load(true)
    },
    async load(reset) {
      if (reset) { this.page = 1; this.list = []; this.finished = false }
      this.loading = true
      try {
        const data = await http.get(api.favorites, {
          type: this.type, page: this.page, limit: this.limit
        }, { hideError: true })
        const list = this.list.concat(data.list || [])
        this.list = list
        this.page++
        this.finished = list.length >= data.total
      } catch (e) { uni.showToast({ title: e.message || '操作失败', icon: 'none' }) }
      this.loading = false
    },
    goGoods(id) { uni.navigateTo({ url: '/pages/goods/goods?id=' + id }) },
    goShop(id) { uni.navigateTo({ url: '/pages/shop/shop?id=' + id }) }
  }
}
</script>

<style scoped>
.tabs { display: flex; background: #fff; border-bottom: 1rpx solid #eee; }
.tab { flex: 1; padding: 24rpx 0; text-align: center; color: #666; font-size: 28rpx; }
.tab.active { color: #ff6600; border-bottom: 4rpx solid #ff6600; font-weight: 500; }

.list { padding: 20rpx 24rpx; padding-bottom: 40rpx; }

.card, .shop-card {
  display: flex; gap: 20rpx; padding: 24rpx;
  background: #fff; border-radius: 12rpx;
  margin-bottom: 20rpx;
}
.emoji-cover, .shop-emoji {
  width: 100rpx; height: 100rpx; flex-shrink: 0;
  border-radius: 8rpx;
  background: linear-gradient(135deg, #fff3e0, #ffe0b2);
  display: flex; align-items: center; justify-content: center;
  font-size: 50rpx;
}
.shop-emoji { background: linear-gradient(135deg, #e3f2fd, #bbdefb); }

.info { flex: 1; min-width: 0; }
.name { font-size: 28rpx; font-weight: 600; color: #111; }
.meta { color: #999; font-size: 22rpx; margin-top: 6rpx; }
.price { color: #ff6600; font-size: 28rpx; margin-top: 8rpx; font-weight: 500; }

.empty-card {
  background: #fff; border-radius: 12rpx;
  margin: 20rpx 24rpx; padding: 100rpx 0;
  text-align: center;
}
.empty-icon { font-size: 100rpx; display: block; }
.empty-text { color: #bbb; font-size: 26rpx; margin-top: 16rpx; }
</style>
