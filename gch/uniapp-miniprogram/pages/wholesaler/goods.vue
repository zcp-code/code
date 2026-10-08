<template>
  <view class="container">
    <view class="tabs">
      <view class="tab" :class="status === '' ? 'active' : ''" @tap="switchTab('')">全部</view>
      <view class="tab" :class="status === '1' ? 'active' : ''" @tap="switchTab('1')">在售</view>
      <view class="tab" :class="status === '0' ? 'active' : ''" @tap="switchTab('0')">已下架</view>
      <view class="tab" :class="status === '2' ? 'active' : ''" @tap="switchTab('2')">售罄</view>
    </view>

    <view class="goods-list">
      <view v-for="g in list" :key="g.id" class="goods-card">
        <image class="cover" :src="g.cover || '/static/placeholder.png'" mode="aspectFill"></image>
        <view class="info">
          <view class="name ellipsis-2">{{g.name}}</view>
          <view class="meta">¥{{g.price}}/{{g.unit}} · 库存 {{g.total_stock}}</view>
          <view class="meta">已订 {{g.reserved_quantity}} · 可订 {{g.available}}</view>
          <view class="status-row">
            <text class="status" :class="'status-' + g.status">{{statusLabel(g.status)}}</text>
            <view class="actions">
              <view class="btn small warn" @tap="toggleStatus(g)">
                {{ g.status === 1 ? '下架' : '上架' }}
              </view>
            </view>
          </view>
        </view>
      </view>
      <view v-if="loading" class="loading">加载中...</view>
      <view v-else-if="finished && list.length > 0" class="loading">— 没有更多了 —</view>
      <view v-else-if="list.length === 0" class="empty">
        <text>暂无货盘</text>
        <view class="empty-tip">点击右上角「发布」按钮添加</view>
      </view>
    </view>

    <view class="fab" @tap="goPublish">+</view>
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
      return { '1': '在售', '0': '已下架', '2': '售罄' }[s] || '未知'
    },
    async load(reset) {
      if (reset) { this.page = 1; this.list = []; this.finished = false }
      this.loading = true
      try {
        const data = await http.get(api.wholesalerGoodsList, {
          page: this.page, limit: this.limit, status: this.status
        }, { hideError: true })
        const list = this.list.concat(data.list || [])
        this.list = list
        this.page++
        this.finished = list.length >= data.total
      } catch (e) { uni.showToast({ title: e.message || "操作失败", icon: "none" }) }
      this.loading = false
    },
    goPublish() { uni.navigateTo({ url: '/pages/wholesaler/publish' }) },
    async toggleStatus(g) {
      const newStatus = g.status === 1 ? 0 : 1
      uni.showModal({
        title: '提示',
        content: `确认${newStatus === 1 ? '上架' : '下架'}「${g.name}」？`,
        success: async ({ confirm }) => {
          if (!confirm) return
          try {
            await http.post(api.wholesalerGoodsStatus, { id: g.id, status: newStatus }, { hideError: true })
            uni.showToast({ title: '操作成功', icon: 'success' })
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
.goods-card { display: flex; background: #fff; border-radius: 12rpx; padding: 20rpx; margin-bottom: 20rpx; }
.cover { width: 160rpx; height: 160rpx; border-radius: 8rpx; background: #f5f5f5; }
.info { flex: 1; margin-left: 20rpx; display: flex; flex-direction: column; }
.name { font-size: 30rpx; font-weight: 500; }
.meta { color: #999; font-size: 24rpx; margin-top: 6rpx; }
.status-row { display: flex; justify-content: space-between; align-items: center; margin-top: 12rpx; }
.status { font-size: 22rpx; padding: 4rpx 12rpx; border-radius: 4rpx; }
.status-1 { color: #4caf50; background: #e8f5e9; }
.status-0 { color: #999; background: #f5f5f5; }
.status-2 { color: #ff9800; background: #fff3e0; }
.actions .btn { padding: 10rpx 24rpx; border-radius: 8rpx; font-size: 24rpx; }
.btn.warn { background: #fff5f0; color: #ff6600; border: 1rpx solid #ff6600; }
.loading, .empty { text-align: center; color: #999; padding: 60rpx 0; font-size: 26rpx; }
.fab { position: fixed; right: 30rpx; bottom: 60rpx; width: 100rpx; height: 100rpx; line-height: 100rpx; text-align: center; background: #ff6600; color: #fff; border-radius: 50%; font-size: 60rpx; box-shadow: 0 4rpx 20rpx rgba(255,107,53,.4); }
</style>
