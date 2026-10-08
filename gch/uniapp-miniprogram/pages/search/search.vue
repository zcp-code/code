<template>
  <view class="container">
    <view class="search-bar">
      <view class="search-input-wrap">
        <text class="search-icon">🔍</text>
        <input
          class="search-input"
          v-model="keyword"
          :focus="autoFocus"
          confirm-type="search"
          placeholder="搜索货品 / 店铺"
          @confirm="doSearch"
        />
        <text v-if="keyword" class="search-clear" @tap="clearKeyword">×</text>
      </view>
      <text class="search-cancel" @tap="cancel">取消</text>
    </view>

    <view class="tabs">
      <view class="tab" :class="type === 'goods' ? 'active' : ''" @tap="switchTab('goods')">货品</view>
      <view class="tab" :class="type === 'shop' ? 'active' : ''" @tap="switchTab('shop')">店铺</view>
    </view>

    <view v-if="!searched" class="empty-card">
      <text class="empty-icon">🔍</text>
      <view class="empty-text">输入关键词开始搜索</view>
    </view>
    <view v-else-if="loading && list.length === 0" class="empty-card">
      <text class="empty-icon">⏳</text>
      <view class="empty-text">搜索中...</view>
    </view>
    <view v-else-if="list.length === 0" class="empty-card">
      <text class="empty-icon">😢</text>
      <view class="empty-text">没找到{{ type === 'shop' ? '店铺' : '货品' }}</view>
    </view>

    <!-- 货品结果 -->
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

    <!-- 店铺结果 -->
    <view v-else class="list">
      <view v-for="s in list" :key="s.id" class="shop-card" @tap="goShop(s.id)">
        <view class="shop-emoji">🏬</view>
        <view class="info">
          <view class="name">{{ s.name }}</view>
          <view class="meta">📍 {{ s.position || '暂无地址' }}</view>
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
      keyword: '',
      type: 'goods',     // goods / shop
      list: [],
      loading: false,
      searched: false,
      autoFocus: true
    }
  },
  onLoad(q) {
    this.keyword = q.keyword || ''
    if (this.keyword) this.doSearch()
  },
  methods: {
    clearKeyword() {
      this.keyword = ''
      this.list = []
      this.searched = false
    },
    cancel() {
      uni.navigateBack()
    },
    doSearch() {
      const kw = (this.keyword || '').trim()
      if (!kw) return
      this.searched = true
      this.load()
    },
    switchTab(t) {
      if (this.type === t) return
      this.type = t
      if (this.searched) this.load()
    },
    async load() {
      const kw = (this.keyword || '').trim()
      if (!kw) return
      this.loading = true
      try {
        if (this.type === 'goods') {
          const data = await http.get(api.goodsList, {
            page: 1, limit: 30, keyword: kw
          }, { hideError: true })
          this.list = data.list || []
        } else {
          // 店铺搜索:/api/shops 也支持 keyword
          const data = await http.get(api.shopList, {
            page: 1, limit: 30, keyword: kw
          }, { hideError: true })
          this.list = data.list || []
        }
      } catch (e) { uni.showToast({ title: e.message || '操作失败', icon: 'none' }) }
      this.loading = false
    },
    goGoods(id) { uni.navigateTo({ url: '/pages/goods/goods?id=' + id }) },
    goShop(id) { uni.navigateTo({ url: '/pages/shop/shop?id=' + id }) }
  }
}
</script>

<style scoped>
.search-bar {
  display: flex; align-items: center; gap: 16rpx;
  padding: 16rpx 24rpx; background: #fff;
  border-bottom: 1rpx solid #eee;
}
.search-input-wrap {
  flex: 1;
  display: flex; align-items: center; gap: 8rpx;
  background: #f5f5f5; border-radius: 36rpx;
  padding: 0 20rpx; height: 64rpx;
}
.search-icon { font-size: 28rpx; }
.search-input { flex: 1; font-size: 28rpx; height: 60rpx; }
.search-clear {
  width: 36rpx; height: 36rpx; line-height: 30rpx; text-align: center;
  background: #ccc; color: #fff; border-radius: 50%; font-size: 28rpx;
}
.search-cancel { color: #ff6600; font-size: 28rpx; }

.tabs {
  display: flex; background: #fff;
  border-bottom: 1rpx solid #eee;
}
.tab { flex: 1; padding: 20rpx 0; text-align: center; color: #666; font-size: 28rpx; }
.tab.active { color: #ff6600; border-bottom: 4rpx solid #ff6600; font-weight: 500; }

.list { padding: 20rpx 24rpx; }

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
  margin: 40rpx 24rpx; padding: 100rpx 0;
  text-align: center;
}
.empty-icon { font-size: 100rpx; display: block; }
.empty-text { color: #bbb; font-size: 26rpx; margin-top: 16rpx; }
</style>
