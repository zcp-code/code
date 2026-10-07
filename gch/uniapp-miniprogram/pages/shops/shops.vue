<template>
  <view class="container">
    <view class="search-bar">
      <input class="search-input" placeholder="搜索店铺名" v-model="keyword" @confirm="load(true)" />
      <view class="search-btn" @tap="load(true)">搜索</view>
    </view>

    <view class="shop-list">
      <view v-for="s in list" :key="s.id" class="shop-card" @tap="goDetail(s.id)">
        <image class="logo" :src="s.logo || '/static/placeholder.png'" mode="aspectFill"></image>
        <view class="info">
          <view class="name">{{s.name}}</view>
          <view class="meta">📍 {{s.position || '暂无地址'}}</view>
          <view class="meta">📞 {{s.contact_phone || '暂无电话'}}</view>
          <view class="meta">🕐 {{s.business_hours || '营业时间待定'}}</view>
          <view v-if="s.today_count" class="today">今日到货 {{s.today_count}} 个</view>
        </view>
      </view>
      <view v-if="loading" class="loading">加载中...</view>
      <view v-else-if="list.length === 0" class="empty">暂无店铺</view>
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
      keyword: '',
      list: [],
      page: 1,
      limit: 20,
      loading: false,
      finished: false
    }
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
      } catch (e) {}
      this.loading = false
    },
    goDetail(id) { uni.navigateTo({ url: `/pages/shop/shop?id=${id}` }) }
  }
}
</script>

<style scoped>
.search-bar { display: flex; background: #fff; border-radius: 12rpx; padding: 16rpx; margin-bottom: 20rpx; }
.search-input { flex: 1; background: #f5f5f5; border-radius: 8rpx; padding: 0 20rpx; font-size: 28rpx; }
.search-btn { background: #ff6b35; color: #fff; border-radius: 8rpx; padding: 0 30rpx; line-height: 60rpx; margin-left: 16rpx; font-size: 28rpx; }
.shop-card { display: flex; background: #fff; border-radius: 12rpx; padding: 20rpx; margin-bottom: 20rpx; }
.logo { width: 140rpx; height: 140rpx; border-radius: 8rpx; background: #f5f5f5; }
.info { flex: 1; margin-left: 20rpx; }
.name { font-size: 32rpx; font-weight: 600; }
.meta { color: #999; font-size: 24rpx; margin-top: 6rpx; }
.today { color: #ff6b35; font-size: 24rpx; margin-top: 8rpx; font-weight: 500; }
.loading, .empty { text-align: center; color: #999; padding: 60rpx 0; font-size: 26rpx; }
</style>
