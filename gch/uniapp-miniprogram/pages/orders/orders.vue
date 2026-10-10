<template>
  <view class="container">

    <!-- 顶部 Tab + 状态统计 -->
    <view v-if="isLogin" class="tabs-bar">
      <scroll-view scroll-x class="tabs-scroll" :show-scrollbar="false">
        <view class="tabs-inner">
          <view
            v-for="t in tabs"
            :key="t.value"
            class="tab-item"
            :class="{ active: currentTab === t.value }"
            @tap="switchTab(t.value)"
          >
            <text class="tab-text">{{ t.label }}</text>
            <text v-if="t.count > 0" class="tab-badge">{{ t.count }}</text>
            <view v-if="currentTab === t.value" class="tab-underline"></view>
          </view>
        </view>
      </scroll-view>
    </view>

    <!-- 未登录 -->
    <view v-if="!isLogin" class="state-card">
      <view class="state-emoji">🔒</view>
      <view class="state-title">登录后查看订单</view>
      <view class="state-sub">登录后可查看历史预订和实时状态</view>
      <view class="btn-primary" @tap="goLogin">立即登录</view>
    </view>

    <!-- 已登录 -->
    <block v-else>
      <!-- 加载中 -->
      <view v-if="loading && list.length === 0" class="state-card">
        <view class="state-emoji">⏳</view>
        <view class="state-sub">加载中...</view>
      </view>

      <!-- 空数据 -->
      <view v-else-if="list.length === 0" class="state-card">
        <view class="state-emoji">📭</view>
        <view class="state-title">{{ currentTab ? currentTabLabel + '的订单' : '暂无订单' }}</view>
        <view class="state-sub">{{ currentTab ? '换个状态看看吧' : '去看看有什么新鲜货品吧' }}</view>
      </view>

      <!-- 订单列表 -->
      <view v-else class="list">
        <view v-for="r in list" :key="r.id" class="order-card">
          <!-- 卡片顶部:店铺 + 状态 -->
          <view class="order-head">
            <view class="shop-info">
              <text class="shop-icon">🏬</text>
              <text class="shop-name ellipsis">{{ r.shop_name || '未知店铺' }}</text>
            </view>
            <text class="status-tag" :class="'status-' + r.status">
              {{ statusText(r.status) }}
            </text>
          </view>

          <!-- 商品信息 -->
          <view class="order-body" @tap="goDetail(r.id)">
            <image class="goods-img" :src="r.cover || '/static/placeholder.png'" mode="aspectFill"></image>
            <view class="goods-info">
              <view class="goods-name ellipsis-2">{{ r.goods_name || '-' }}</view>
              <view class="price-row">
                <text class="price-num">¥{{ formatPrice(r.price) }}</text>
                <text class="price-unit">/{{ r.unit || '件' }}</text>
              </view>
              <view class="qty-row">× {{ (r.quantity || 0).toLocaleString() }} {{ r.unit || '' }}</view>
            </view>
          </view>

          <!-- 合计 -->
          <view class="total-row">
            <text class="total-label">共 {{ (r.quantity || 0).toLocaleString() }} {{ r.unit || '' }}</text>
            <text class="total-amount">合计 <text class="total-num">¥{{ formatTotal(r.price, r.quantity) }}</text></text>
          </view>

          <!-- 订单号 + 时间 -->
          <view class="meta-row">
            <text class="meta-no">{{ r.reservation_no }}</text>
            <text class="meta-time">{{ formatTime(r.createtime) }}</text>
          </view>

          <!-- 操作按钮 -->
          <view v-if="r.status === 'pending'" class="actions">
            <view class="btn-ghost" @tap.stop="cancelOrder(r)">取消预订</view>
            <view class="btn-solid" @tap.stop="goDetail(r.id)">查看详情</view>
          </view>
          <view v-else class="actions">
            <view class="btn-solid" @tap.stop="goDetail(r.id)">查看详情</view>
          </view>
        </view>

        <!-- 加载更多 -->
        <view v-if="!finished" class="loadmore" @tap="load(false)">
          <text class="loadmore-text">{{ loading ? '加载中...' : '加载更多' }}</text>
        </view>
        <view v-else-if="list.length > 0" class="loadmore">
          <text class="loadmore-text dim">— 已经到底啦 —</text>
        </view>
      </view>
    </block>

    <custom-tabbar />
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'
import userStore from '@/store/user.js'
import CustomTabbar from '@/components/custom-tabbar/custom-tabbar.vue'

const STATUS_TABS = [
  { value: '',         label: '全部' },
  { value: 'pending',  label: '待确认' },
  { value: 'confirmed',label: '已确认' },
  { value: 'cancelled',label: '已取消' }
]

export default {
  components: { CustomTabbar },
  data() {
    return {
      tabs: STATUS_TABS.map(t => ({ ...t, count: 0 })),
      currentTab: '',
      list: [],
      page: 1,
      limit: 20,
      loading: false,
      finished: false
    }
  },
  computed: {
    isLogin() {
      const t = userStore.token || ''
      return t.length >= 32 && userStore.role === 'buyer'
    },
    currentTabLabel() {
      const t = STATUS_TABS.find(x => x.value === this.currentTab)
      return t ? t.label : ''
    }
  },
  onLoad(q) {
    // 从个人中心等地方跳过来时,带 status=pending|confirmed|cancelled 自动选中对应 tab
    if (q && q.status && STATUS_TABS.some(t => t.value === q.status)) {
      this.currentTab = q.status
    }
  },
  onShow() {
    if (this.isLogin) this.refreshCurrentTab()
  },
  onPullDownRefresh() {
    if (!this.isLogin) { uni.stopPullDownRefresh(); return }
    this.refreshCurrentTab().then(() => uni.stopPullDownRefresh())
  },
  methods: {
    async refreshCurrentTab() {
      this.page = 1
      this.list = []
      this.finished = false
      // 并行:加载当前 tab + 加载 tab 计数
      await Promise.all([
        this.load(false),
        this.loadCounts()
      ])
    },
    switchTab(value) {
      if (this.currentTab === value) return
      this.currentTab = value
      this.refreshCurrentTab()
    },
    async loadCounts() {
      // 拉一次每种状态的总数(用于 tab 上的数字徽标)
      try {
        const results = await Promise.all(
          STATUS_TABS
            .filter(t => t.value !== '')
            .map(t => http.get(api.reservationList, { status: t.value, page: 1, limit: 1 }, { hideError: true }))
        )
        this.tabs = this.tabs.map(t => {
          if (t.value === '') return t
          const idx = STATUS_TABS.findIndex(x => x.value === t.value) - 1  // 对齐 results 数组
          return { ...t, count: results[idx] ? (results[idx].total || 0) : 0 }
        })
      } catch (e) { /* 静默失败,徽标可有可无 */ }
    },
    async load(append) {
      if (this.loading) return
      this.loading = true
      try {
        // 动态拼参数 — "全部" tab 时不带 status 字段,避免被某些运行时序列化成 "undefined" 字符串导致后端查不到数据
        const params = { page: this.page, limit: this.limit }
        if (this.currentTab) params.status = this.currentTab
        const data = await http.get(api.reservationList, params, { hideError: true })
        const rows = data.list || []
        this.list = append ? this.list.concat(rows) : rows
        if (!append) {
          this.page = 2
        } else {
          this.page++
        }
        this.finished = this.list.length >= (data.total || 0)
      } catch (e) {}
      this.loading = false
    },
    statusText(s) {
      return { pending: '待确认', confirmed: '已确认', cancelled: '已取消' }[s] || s
    },
    cancelOrder(r) {
      uni.showModal({
        title: '取消预订',
        content: `确认取消「${r.goods_name}」?`,
        confirmText: '确认取消',
        cancelText: '再想想',
        confirmColor: '#ff6600',
        success: async ({ confirm }) => {
          if (!confirm) return
          try {
            await http.post(api.reservationCancel, { id: r.id, reason: '买家取消' }, { hideError: true })
            uni.showToast({ title: '已取消预订', icon: 'success' })
            this.refreshCurrentTab()
          } catch (e) {}
        }
      })
    },
    goDetail(id) { uni.navigateTo({ url: `/pages/reservation-detail/reservation-detail?id=${id}` }) },
    goLogin() { uni.navigateTo({ url: '/pages/login/account?role=buyer' }) },
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
      return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${hm}`
    },
    formatPrice(p) {
      const n = parseFloat(p)
      return isNaN(n) ? '0.00' : n.toFixed(2)
    },
    formatTotal(price, qty) {
      const p = parseFloat(price), q = parseInt(qty, 10) || 0
      return (isNaN(p) ? 0 : p * q).toFixed(2)
    }
  }
}
</script>

<style scoped>
.container { padding-bottom: 160rpx; min-height: 100vh; background: #f5f7fa; }

/* ===== Tabs ===== */
.tabs-bar {
  position: sticky;
  top: 0;
  z-index: 10;
  background: #fff;
  box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.04);
}
.tabs-scroll { white-space: nowrap; }
.tabs-inner {
  display: inline-flex;
  align-items: center;
  padding: 0 20rpx;
}
.tab-item {
  position: relative;
  display: inline-flex;
  align-items: center;
  padding: 26rpx 28rpx 24rpx;
  margin-right: 8rpx;
}
.tab-text {
  font-size: 30rpx;
  color: #6b7280;
  font-weight: 500;
}
.tab-item.active .tab-text { color: #ff6600; font-weight: 700; font-size: 32rpx; }
.tab-badge {
  margin-left: 8rpx;
  background: #f2f3f5;
  color: #6b7280;
  font-size: 22rpx;
  padding: 2rpx 12rpx;
  border-radius: 20rpx;
  font-weight: 600;
  min-width: 32rpx;
  text-align: center;
}
.tab-item.active .tab-badge { background: #fff1e6; color: #ff6600; }
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

/* ===== State Card (空/未登录/加载中) ===== */
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
.state-sub { font-size: 26rpx; color: #9ca3af; margin-top: 12rpx; margin-bottom: 36rpx; }
.btn-primary {
  display: inline-block;
  background: linear-gradient(135deg, #ff6600, #ff8c42);
  color: #fff;
  border-radius: 48rpx;
  padding: 22rpx 80rpx;
  font-size: 28rpx;
  font-weight: 600;
  box-shadow: 0 8rpx 20rpx rgba(255, 102, 0, 0.25);
}

/* ===== Order Card ===== */
.list { padding: 24rpx 24rpx 0; }
.order-card {
  background: #fff;
  border-radius: 20rpx;
  margin-bottom: 24rpx;
  overflow: hidden;
  box-shadow: 0 2rpx 16rpx rgba(0, 0, 0, 0.04);
}
.order-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24rpx 28rpx;
  border-bottom: 1rpx solid #f3f4f6;
}
.shop-info { display: flex; align-items: center; min-width: 0; flex: 1; }
.shop-icon { font-size: 28rpx; margin-right: 10rpx; flex-shrink: 0; }
.shop-name {
  font-size: 28rpx;
  color: #1f2937;
  font-weight: 600;
  max-width: 360rpx;
}

.status-tag {
  font-size: 24rpx;
  padding: 6rpx 16rpx;
  border-radius: 6rpx;
  font-weight: 600;
  flex-shrink: 0;
}
.status-pending   { color: #d97706; background: #fef3c7; }
.status-confirmed { color: #059669; background: #d1fae5; }
.status-cancelled { color: #6b7280; background: #f3f4f6; }

.order-body {
  display: flex;
  gap: 24rpx;
  padding: 28rpx;
}
.goods-img {
  width: 160rpx;
  height: 160rpx;
  border-radius: 12rpx;
  background: #f3f4f6;
  flex-shrink: 0;
}
.goods-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.goods-name {
  font-size: 28rpx;
  color: #1f2937;
  font-weight: 600;
  line-height: 1.4;
}
.price-row { margin-top: 12rpx; display: flex; align-items: baseline; }
.price-num { font-size: 32rpx; color: #ff6600; font-weight: 700; }
.price-unit { font-size: 24rpx; color: #9ca3af; margin-left: 2rpx; }
.qty-row { font-size: 24rpx; color: #6b7280; margin-top: 4rpx; }

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20rpx 28rpx;
  background: #fafbfc;
  border-top: 1rpx solid #f3f4f6;
}
.total-label { font-size: 24rpx; color: #6b7280; }
.total-amount { font-size: 26rpx; color: #6b7280; }
.total-num { color: #ff6600; font-weight: 700; font-size: 30rpx; margin-left: 6rpx; }

.meta-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16rpx 28rpx;
  border-top: 1rpx solid #f3f4f6;
}
.meta-no { font-size: 22rpx; color: #9ca3af; font-family: monospace; }
.meta-time { font-size: 22rpx; color: #9ca3af; }

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 16rpx;
  padding: 20rpx 28rpx 24rpx;
  border-top: 1rpx solid #f3f4f6;
}
.btn-ghost {
  border: 1rpx solid #e5e7eb;
  color: #6b7280;
  border-radius: 36rpx;
  padding: 12rpx 32rpx;
  font-size: 26rpx;
  font-weight: 500;
}
.btn-solid {
  background: linear-gradient(135deg, #ff6600, #ff8c42);
  color: #fff;
  border-radius: 36rpx;
  padding: 12rpx 32rpx;
  font-size: 26rpx;
  font-weight: 600;
  box-shadow: 0 4rpx 12rpx rgba(255, 102, 0, 0.2);
}

/* ===== Load More ===== */
.loadmore {
  text-align: center;
  padding: 36rpx 0;
}
.loadmore-text {
  font-size: 24rpx;
  color: #ff6600;
  font-weight: 500;
}
.loadmore-text.dim { color: #cbd5e1; font-weight: 400; }

/* ===== Utilities ===== */
.ellipsis {
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}
.ellipsis-2 {
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
</style>
