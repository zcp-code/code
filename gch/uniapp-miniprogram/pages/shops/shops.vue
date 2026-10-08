<template>
  <view class="container">
    <!-- 顶部橙色搜索区域 -->
    <view class="search-header">
      <view class="search-box">
        <text class="search-icon iconfont icon-sousuo"></text>
        <input class="search-input" placeholder="搜索店铺名称" v-model="keyword" @confirm="load(true)" />
        <text v-if="keyword" class="clear-icon" @tap="keyword=''">×</text>
      </view>
    </view>

    <!-- 数据概览条 -->
    <view class="stats-row">
      <view class="stat-item">
        <text class="stat-num">{{ list.length }}</text>
        <text class="stat-label">家店铺</text>
      </view>
      <view class="stat-item">
        <text class="stat-num">{{ totalToday }}</text>
        <text class="stat-label">今日上新</text>
      </view>
      <view class="stat-item">
        <text class="stat-num">{{ favCount }}</text>
        <text class="stat-label">已收藏</text>
      </view>
    </view>

    <!-- 店铺列表 -->
    <view class="shop-list">
      <view v-if="loading && list.length === 0" class="state-card">
        <text class="state-icon">⏳</text>
        <text class="state-text">加载中...</text>
      </view>
      <view v-else-if="list.length === 0" class="state-card">
        <text class="state-icon">🏬</text>
        <text class="state-text">暂无店铺</text>
        <text class="state-tip">试试搜索其他关键词</text>
      </view>

      <view v-for="s in list" :key="s.id" class="shop-card">
        <view class="card-row" @tap="goDetail(s.id)">
          <image class="shop-logo" :src="s.logo || '/static/placeholder.png'" mode="aspectFill"></image>
          <view class="shop-info">
            <view class="shop-title-line">
              <text class="shop-name ellipsis">{{ s.name }}</text>
              <text v-if="s.today_count" class="shop-badge today-new">今日上新</text>
              <text v-if="s.favorited" class="shop-badge fav">★ 已收藏</text>
              <text v-else-if="!s.today_count" class="shop-badge">未收藏</text>
            </view>
            <view class="shop-address">
              <text class="iconfont icon-dizhi"></text>
              <text class="addr-text ellipsis">{{ s.position || '暂无地址' }}</text>
            </view>
            <view class="shop-meta">
              <text v-if="s.today_count" class="meta-tag new">
                📦 今日到货 {{ s.today_count }} 种
              </text>
              <text v-if="s.business_hours" class="meta-tag time">
                🕐 {{ s.business_hours }}
              </text>
            </view>
          </view>
        </view>

        <!-- 底部操作条 -->
        <view class="card-actions">
          <view class="action-btn call" @tap.stop="callPhone(s)">
            <text class="iconfont icon-dianhua"></text>
            <text>电话</text>
          </view>
          <view class="action-btn" :class="{ fav: s.favorited }" @tap.stop="toggleFav(s, $event)">
            <text>{{ s.favorited ? '★ 已收藏' : '☆ 收藏' }}</text>
          </view>
          <view class="action-btn primary" @tap.stop="goDetail(s.id)">
            <text>进店 →</text>
          </view>
        </view>
      </view>

      <view v-if="finished && list.length > 0" class="more-tip">— 没有更多了 —</view>
    </view>

    <custom-tabbar />
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'
import userStore from '@/store/user.js'
import CustomTabbar from '@/components/custom-tabbar/custom-tabbar.vue'

export default {
  components: { CustomTabbar },
  data() {
    return {
      keyword: '',
      list: [],
      page: 1,
      limit: 20,
      loading: false,
      finished: false
    }
  },
  computed: {
    totalToday() { return this.list.reduce((s, x) => s + (x.today_count || 0), 0) },
    favCount()    { return this.list.filter(x => x.favorited).length }
  },
  onShow() { this.load(true) },
  onReachBottom() { if (!this.finished && !this.loading) this.load(false) },
  methods: {
    async load(reset) {
      if (reset) { this.page = 1; this.list = []; this.finished = false }
      this.loading = true
      try {
        const data = await http.get(api.shopList, {
          page: this.page, limit: this.limit, keyword: this.keyword
        }, { hideError: true })
        const list = this.list.concat(data.list || [])
        this.list = list
        this.page++
        this.finished = list.length >= data.total
      } catch (e) { uni.showToast({ title: e.message || '操作失败', icon: 'none' }) }
      this.loading = false
    },
    goDetail(id) { uni.navigateTo({ url: `/pages/shop/shop?id=${id}` }) },
    async toggleFav(s, e) {
      if (e && e.stopPropagation) e.stopPropagation()
      if (!userStore.isRealLogin || userStore.role !== 'buyer') {
        return uni.showModal({
          title: '请登录',
          content: '店铺收藏需使用采购商账号',
          confirmText: '去登录',
          success: ({ confirm }) => {
            if (confirm) uni.navigateTo({ url: '/pages/login/account?role=buyer' })
          }
        })
      }
      try {
        const r = await http.post(api.favoriteShop, { shop_id: s.id }, { hideError: true })
        s.favorited = r.favored ? 1 : 0
        uni.showToast({ title: r.favored ? '已收藏' : '已取消收藏', icon: 'none' })
      } catch (e) { uni.showToast({ title: e.message || '操作失败', icon: 'none' }) }
    },
    callPhone(s, e) {
      if (e && e.stopPropagation) e.stopPropagation()
      if (!s.contact_phone) return uni.showToast({ title: '暂无联系电话', icon: 'none' })
      uni.makePhoneCall({ phoneNumber: s.contact_phone })
    }
  }
}
</script>

<style scoped>
page { background-color: #f5f5f5; }
.container { min-height: 100vh; padding-bottom: 120rpx; background-color: #f5f5f5; }

/* === 搜索栏 === */
.search-header {
  background: linear-gradient(135deg, #ff6600, #ff8a5b);
  padding: 32rpx 28rpx 56rpx;
}
.search-box {
  display: flex; align-items: center;
  background: #fff;
  border-radius: 40rpx;
  padding: 16rpx 24rpx;
  box-shadow: 0 4rpx 12rpx rgba(0,0,0,.08);
}
.search-icon {
  font-size: 32rpx;
  color: #ff6600;
  margin-right: 12rpx;
}
.search-input {
  flex: 1;
  font-size: 28rpx;
  background: transparent;
}
.clear-icon {
  width: 40rpx;
  height: 40rpx;
  line-height: 36rpx;
  text-align: center;
  background: #ccc;
  color: #fff;
  border-radius: 50%;
  font-size: 28rpx;
}

/* === 数据概览条(向上覆盖搜索栏底) === */
.stats-row {
  display: flex;
  gap: 16rpx;
  padding: 0 24rpx;
  margin: -30rpx 0 0;
}
.stat-item {
  flex: 1;
  background: #fff;
  border-radius: 12rpx;
  padding: 20rpx 0;
  text-align: center;
  box-shadow: 0 4rpx 16rpx rgba(0,0,0,.06);
}
.stat-num {
  font-size: 36rpx;
  font-weight: 700;
  color: #ff6600;
  display: block;
}
.stat-label {
  color: #999;
  font-size: 22rpx;
  margin-top: 4rpx;
}

/* === 状态卡片(加载/空) === */
.state-card {
  background: #fff;
  border-radius: 12rpx;
  padding: 80rpx 0;
  text-align: center;
  margin-bottom: 20rpx;
}
.state-icon { font-size: 80rpx; display: block; margin-bottom: 12rpx; }
.state-text { color: #999; font-size: 28rpx; display: block; }
.state-tip { color: #ccc; font-size: 24rpx; margin-top: 8rpx; display: block; }

/* === 店铺列表 === */
.shop-list {
  padding: 20rpx 24rpx 0;
}

/* 店铺卡片 */
.shop-card {
  background: #fff;
  border-radius: 16rpx;
  margin-bottom: 16rpx;
  box-shadow: 0 2rpx 12rpx rgba(0,0,0,.04);
  overflow: hidden;
}
.card-row {
  display: flex;
  align-items: flex-start;
  padding: 24rpx;
}
.shop-logo {
  width: 140rpx;
  height: 140rpx;
  border-radius: 16rpx;
  background: #f5f5f5;
  flex-shrink: 0;
}
.shop-info {
  flex: 1;
  margin-left: 20rpx;
  min-width: 0;
}
.shop-title-line {
  display: flex;
  align-items: center;
  gap: 10rpx;
  flex-wrap: wrap;
}
.shop-name {
  font-size: 32rpx;
  font-weight: 600;
  color: #222;
  max-width: 50%;
}
.shop-badge {
  font-size: 22rpx;
  padding: 4rpx 12rpx;
  border-radius: 6rpx;
  font-weight: 500;
}
.shop-badge.fav { color: #ff6600; background: #fff2e8; }
.shop-badge.today-new { color: #fff; background: #ff6600; }
.shop-badge:not(.fav):not(.today-new) {
  color: #999;
  background: #f5f5f5;
}
.shop-address {
  display: flex;
  align-items: center;
  font-size: 24rpx;
  color: #666;
  margin-top: 10rpx;
  gap: 8rpx;
}
.addr-text { flex: 1; }
.shop-meta {
  margin-top: 12rpx;
  display: flex;
  flex-wrap: wrap;
  gap: 12rpx;
}
.meta-tag {
  font-size: 22rpx;
  padding: 4rpx 12rpx;
  border-radius: 4rpx;
  color: #555;
  background: #f5f5f5;
}
.meta-tag.new {
  color: #27ae60;
  background: #e8f5e9;
  font-weight: 500;
}
.meta-tag.time {
  color: #666;
}

/* === 底部操作条 === */
.card-actions {
  display: flex;
  border-top: 1rpx solid #f5f5f5;
  background: #fafafa;
}
.action-btn {
  flex: 1;
  padding: 20rpx 0;
  text-align: center;
  font-size: 26rpx;
  color: #666;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6rpx;
  transition: background .15s;
}
.action-btn:not(:last-child) { border-right: 1rpx solid #f5f5f5; }
.action-btn:active { background: #f0f0f0; }
.action-btn.call { color: #ff6600; }
.action-btn.fav { color: #ff6600; background: #fff2e8; }
.action-btn.primary {
  color: #fff;
  background: #ff6600;
  font-weight: 500;
}
.action-btn.primary:active { background: #e55a00; }

.more-tip {
  text-align: center;
  color: #ccc;
  font-size: 24rpx;
  padding: 24rpx 0;
}
</style>
