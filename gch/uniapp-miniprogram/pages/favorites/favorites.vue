<template>
  <view class="container">

    <!-- 顶部 Tab 切换 -->
    <view class="tabs-bar">
      <view class="tabs-inner">
        <view
          v-for="t in tabs"
          :key="t.value"
          class="tab"
          :class="{ active: type === t.value }"
          @tap="switchTab(t.value)"
        >
          <text class="tab-label">{{ t.label }}</text>
          <text v-if="t.count > 0" class="tab-badge">{{ t.count }}</text>
          <view v-if="type === t.value" class="tab-underline"></view>
        </view>
      </view>
    </view>

    <!-- 加载中 -->
    <view v-if="loading && list.length === 0" class="state-card">
      <view class="state-emoji">⏳</view>
      <view class="state-sub">加载中...</view>
    </view>

    <!-- 空数据 -->
    <view v-else-if="list.length === 0" class="state-card">
      <view class="state-emoji">{{ type === 'shop' ? '🏬' : '⭐' }}</view>
      <view class="state-title">{{ type === 'shop' ? '还没有收藏店铺' : '还没有收藏货品' }}</view>
      <view class="state-sub">{{ type === 'shop' ? '去逛逛批发市场吧' : '看到喜欢的可以点星收藏' }}</view>
    </view>

    <!-- 货品收藏列表 -->
    <view v-else-if="type === 'goods'" class="list">
      <view v-for="g in list" :key="g.id" class="goods-card" @tap="goGoods(g.id)">
        <image class="goods-img" :src="g.cover || '/static/placeholder.png'" mode="aspectFill"></image>
        <view class="goods-info">
          <view class="goods-name ellipsis-2">{{ g.name || '-' }}</view>
          <view class="goods-meta">
            <text class="shop-name">🏬 {{ g.shop_name || '未知店铺' }}</text>
          </view>
          <view class="goods-meta">
            <text class="stock">📦 库存 {{ (g.total_stock || 0).toLocaleString() }} {{ g.unit || '' }}</text>
          </view>
          <view class="goods-bottom">
            <view class="price-block">
              <text class="price-num">¥{{ formatPrice(g.price) }}</text>
              <text class="price-unit">/{{ g.unit || '件' }}</text>
            </view>
            <view class="fav-tag">
              <text class="fav-icon">★</text>
              <text class="fav-time">{{ formatTime(g.fav_time) }}</text>
            </view>
          </view>
        </view>
      </view>

      <view v-if="!finished && list.length > 0" class="loadmore" @tap="load(false)">
        <text class="loadmore-text">{{ loading ? '加载中...' : '加载更多' }}</text>
      </view>
      <view v-else-if="list.length > 0" class="loadmore">
        <text class="loadmore-text dim">— 已经到底啦 —</text>
      </view>
    </view>

    <!-- 店铺收藏列表 -->
    <view v-else class="list">
      <view v-for="s in list" :key="s.id" class="shop-card" @tap="goShop(s.id)">
        <view class="shop-logo">{{ (s.name || '店').substring(0, 1) }}</view>
        <view class="shop-info">
          <view class="shop-name">{{ s.name || '未知店铺' }}</view>
          <view class="shop-meta">
            <text class="meta-line">📍 {{ s.position || '暂无地址' }}</text>
            <text class="meta-line">🕐 {{ s.business_hours || '营业时间未知' }}</text>
          </view>
          <view class="shop-bottom">
            <view class="today-stat">
              <text class="today-num">{{ s.today_count || 0 }}</text>
              <text class="today-label">今日新到货</text>
            </view>
            <view class="fav-tag">
              <text class="fav-icon">★</text>
              <text class="fav-time">{{ formatTime(s.fav_time) }}</text>
            </view>
          </view>
        </view>
      </view>

      <view v-if="!finished && list.length > 0" class="loadmore" @tap="load(false)">
        <text class="loadmore-text">{{ loading ? '加载中...' : '加载更多' }}</text>
      </view>
      <view v-else-if="list.length > 0" class="loadmore">
        <text class="loadmore-text dim">— 已经到底啦 —</text>
      </view>
    </view>
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'

export default {
  data() {
    return {
      tabs: [
        { value: 'goods', label: '货品', count: 0 },
        { value: 'shop',  label: '店铺', count: 0 }
      ],
      type: 'goods',
      list: [],
      page: 1,
      limit: 20,
      loading: false,
      finished: false
    }
  },
  onShow() {
    this.refresh()
  },
  onPullDownRefresh() {
    this.refresh().then(() => uni.stopPullDownRefresh())
  },
  onReachBottom() {
    if (!this.finished && !this.loading && this.list.length > 0) this.load(false)
  },
  methods: {
    async refresh() {
      this.page = 1
      this.list = []
      this.finished = false
      await Promise.all([this.load(false), this.loadCounts()])
    },
    switchTab(t) {
      if (this.type === t) return
      this.type = t
      this.refresh()
    },
    async loadCounts() {
      // 拉两种类型各自的总数(用于 tab 角标)
      await Promise.all(['goods', 'shop'].map(async t => {
        try {
          const d = await http.get(api.favorites, { type: t, page: 1, limit: 1 }, { hideError: true })
          const idx = this.tabs.findIndex(x => x.value === t)
          if (idx >= 0) this.tabs[idx].count = d.total || 0
        } catch (e) {}
      }))
    },
    async load(append) {
      if (this.loading) return
      this.loading = true
      try {
        const data = await http.get(api.favorites, {
          type: this.type, page: this.page, limit: this.limit
        }, { hideError: true })
        const rows = data.list || []
        this.list = append ? this.list.concat(rows) : rows
        if (append) this.page++
        else this.page = 2
        this.finished = this.list.length >= (data.total || 0)
      } catch (e) { uni.showToast({ title: e.message || '操作失败', icon: 'none' }) }
      this.loading = false
    },
    goGoods(id) { uni.navigateTo({ url: '/pages/goods/goods?id=' + id }) },
    goShop(id) { uni.navigateTo({ url: '/pages/shop/shop?id=' + id }) },
    formatPrice(p) {
      const n = parseFloat(p)
      return isNaN(n) ? '0.00' : n.toFixed(2)
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
      return `${d.getMonth() + 1}-${pad(d.getDate())}`
    }
  }
}
</script>

<style scoped>
.container { min-height: 100vh; background: #f5f7fa; padding-bottom: 60rpx; }

/* ===== Tabs ===== */
.tabs-bar {
  position: sticky;
  top: 0;
  z-index: 10;
  background: #fff;
  box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.04);
}
.tabs-inner { display: flex; }
.tab {
  position: relative;
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 26rpx 0 24rpx;
  gap: 10rpx;
}
.tab-label {
  font-size: 30rpx;
  color: #6b7280;
  font-weight: 500;
}
.tab.active .tab-label { color: #ff6600; font-weight: 700; font-size: 32rpx; }
.tab-badge {
  background: #f3f4f6;
  color: #6b7280;
  font-size: 22rpx;
  padding: 2rpx 12rpx;
  border-radius: 20rpx;
  font-weight: 600;
  min-width: 32rpx;
  text-align: center;
}
.tab.active .tab-badge { background: #fff1e6; color: #ff6600; }
.tab-underline {
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 48rpx;
  height: 6rpx;
  background: linear-gradient(90deg, #ff6600, #ff8c42);
  border-radius: 3rpx;
}

/* ===== State Card ===== */
.state-card {
  background: #fff;
  margin: 32rpx 24rpx;
  padding: 120rpx 40rpx;
  border-radius: 24rpx;
  text-align: center;
  box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.04);
}
.state-emoji { font-size: 120rpx; display: block; margin-bottom: 20rpx; }
.state-title { font-size: 32rpx; color: #1f2937; font-weight: 600; }
.state-sub { font-size: 26rpx; color: #9ca3af; margin-top: 12rpx; }

/* ===== List ===== */
.list { padding: 24rpx 24rpx 0; }

/* === Goods Card === */
.goods-card {
  display: flex;
  gap: 24rpx;
  padding: 28rpx;
  background: #fff;
  border-radius: 20rpx;
  margin-bottom: 24rpx;
  box-shadow: 0 2rpx 16rpx rgba(0, 0, 0, 0.04);
}
.goods-img {
  width: 180rpx;
  height: 180rpx;
  border-radius: 12rpx;
  background: #f3f4f6;
  flex-shrink: 0;
}
.goods-info { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: space-between; }
.goods-name { font-size: 28rpx; color: #1f2937; font-weight: 600; line-height: 1.4; }
.goods-meta { font-size: 24rpx; color: #6b7280; margin-top: 6rpx; }
.shop-name { color: #6b7280; }
.stock { color: #6b7280; }
.goods-bottom { display: flex; justify-content: space-between; align-items: center; margin-top: 12rpx; }
.price-block { display: flex; align-items: baseline; }
.price-num { font-size: 36rpx; color: #ff6600; font-weight: 700; }
.price-unit { font-size: 24rpx; color: #9ca3af; margin-left: 4rpx; }
.fav-tag { display: flex; align-items: center; gap: 4rpx; }
.fav-icon { color: #f59e0b; font-size: 24rpx; }
.fav-time { font-size: 22rpx; color: #9ca3af; }

/* === Shop Card === */
.shop-card {
  display: flex;
  gap: 24rpx;
  padding: 28rpx;
  background: #fff;
  border-radius: 20rpx;
  margin-bottom: 24rpx;
  box-shadow: 0 2rpx 16rpx rgba(0, 0, 0, 0.04);
}
.shop-logo {
  width: 120rpx;
  height: 120rpx;
  border-radius: 16rpx;
  background: linear-gradient(135deg, #fef3c7, #fde68a);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 56rpx;
  font-weight: 700;
  color: #92400e;
  flex-shrink: 0;
}
.shop-info { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: space-between; }
.shop-name { font-size: 30rpx; color: #1f2937; font-weight: 700; }
.shop-meta { margin-top: 10rpx; }
.meta-line { display: block; font-size: 24rpx; color: #6b7280; line-height: 1.6; }
.shop-bottom { display: flex; justify-content: space-between; align-items: center; margin-top: 12rpx; }
.today-stat { display: flex; align-items: baseline; gap: 8rpx; }
.today-num { font-size: 32rpx; color: #ff6600; font-weight: 700; }
.today-label { font-size: 22rpx; color: #9ca3af; }

/* ===== Load More ===== */
.loadmore { text-align: center; padding: 32rpx 0; }
.loadmore-text { font-size: 24rpx; color: #ff6600; font-weight: 500; }
.loadmore-text.dim { color: #cbd5e1; font-weight: 400; }

/* ===== Utilities ===== */
.ellipsis { overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
.ellipsis-2 {
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
</style>
