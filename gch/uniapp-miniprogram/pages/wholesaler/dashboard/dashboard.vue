<template>
  <view class="container">
    <!-- 头部:店铺 logo + 店名 + 账号 + 待确认角标 -->
    <view class="header">
      <view class="header-row">
        <image class="shop-logo" :src="shopLogo || '/static/placeholder.png'" mode="aspectFill"></image>
        <view class="header-info">
          <view class="shop-name">{{ shopName }}</view>
          <view class="account">账号 {{ account || '-' }}</view>
        </view>
        <view class="header-stat">
          <text class="num">{{ stats.pendingBookings || 0 }}</text>
          <text class="lbl">待确认</text>
        </view>
      </view>
    </view>

    <!-- 今日 + 累计 -->
    <view class="stats-row">
      <view class="stat-card green">
        <text class="stat-num">{{ stats.todayGoods || 0 }}</text>
        <text class="stat-label">今日新增货盘</text>
      </view>
      <view class="stat-card orange">
        <text class="stat-num">{{ stats.todayPending || 0 }}</text>
        <text class="stat-label">今日待确认</text>
      </view>
      <view class="stat-card blue">
        <text class="stat-num">{{ stats.confirmedBookings || 0 }}</text>
        <text class="stat-label">累计已确认</text>
      </view>
    </view>

    <!-- 4 宫格快捷操作 -->
    <view class="card">
      <view class="section-title">快捷操作</view>
      <view class="quick-grid">
        <view class="quick-item" @tap="go('publish')">
          <text class="emoji">📤</text>
          <text class="label">发布货盘</text>
        </view>
        <view class="quick-item" @tap="go('goods')">
          <text class="emoji">📦</text>
          <text class="label">货盘管理</text>
        </view>
        <view class="quick-item" @tap="go('reservations')">
          <text class="emoji">📨</text>
          <text class="label">预订处理</text>
        </view>
        <view class="quick-item" @tap="go('qrcode')">
          <text class="emoji">📱</text>
          <text class="label">店铺二维码</text>
        </view>
      </view>
    </view>

    <!-- 待处理预订列表 -->
    <view class="card">
      <view class="section-title">
        <text>待确认预订</text>
        <text v-if="pendingList.length > 0" class="badge">{{ pendingList.length }}</text>
      </view>

      <view v-if="loading && pendingList.length === 0" class="empty-tip">⏳ 加载中...</view>
      <view v-else-if="pendingList.length === 0" class="empty-tip">📋 暂无待处理订单</view>

      <view v-for="r in pendingList" :key="r.id" class="booking-card">
        <!-- 卡片头:编号 + 状态 + 时间 -->
        <view class="booking-head">
          <text class="booking-no">{{ r.reservation_no }}</text>
          <text class="booking-status">待确认</text>
        </view>
        <view class="booking-time">{{ formatTime(r.createtime) }}</view>

        <!-- 货品行:封面 + 信息 + 总价 -->
        <view class="goods-row">
          <image class="goods-cover" :src="r.cover || '/static/placeholder.png'" mode="aspectFill"></image>
          <view class="goods-info">
            <view class="goods-name ellipsis-2">{{ r.goods_name }}</view>
            <view class="goods-spec">¥{{ Number(r.price).toFixed(2) }}/{{ r.unit }} × {{ r.quantity.toLocaleString() }} {{ r.unit }}</view>
            <view class="goods-total">¥{{ (Number(r.price) * r.quantity).toFixed(2) }}</view>
          </view>
        </view>

        <!-- 采购商 -->
        <view class="kv">
          <text class="k">采购商</text>
          <text class="v">{{ r.buyer_name || '-' }}</text>
        </view>

        <!-- 操作按钮 -->
        <view class="booking-actions">
          <view class="btn-gray" @tap="cancel(r)">取消</view>
          <view class="btn-outline" @tap="callBuyerFor(r)">📞 联系</view>
          <view class="btn-primary" @tap="confirm(r)">✓ 确认</view>
        </view>
      </view>

      <view v-if="pendingList.length >= 10" class="more-btn" @tap="go('reservations')">
        查看全部待处理订单 →
      </view>
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
      shopName: '示范水果店',
      shopLogo: '',
      account: '',
      stats: { todayGoods: 0, todayPending: 0, totalGoods: 0, pendingBookings: 0, confirmedBookings: 0 },
      pendingList: [],
      loading: false
    }
  },
  onShow() {
    if (userStore.isWholesaler) {
      this.account = userStore.profile?.account || ''
      // Wholesaler login 现在直接返回 shop_name + shop_logo(扁平字段),前端无需再调 profile
      this.shopName  = userStore.profile?.shop_name  || userStore.profile?.shop?.name || '示范水果店'
      this.shopLogo  = userStore.profile?.shop_logo  || userStore.profile?.shop?.logo || ''
      this.loadAll()
    }
  },
  methods: {
    async loadAll() {
      this.loading = true
      await Promise.all([this.loadStats(), this.loadPending()])
      this.loading = false
    },

    async loadStats() {
      try {
        const todayStart = Math.floor(new Date().setHours(0, 0, 0, 0) / 1000)
        const goodsRes = await http.get(api.wholesalerGoodsList, { page: 1, limit: 1 }, { hideError: true })
        this.stats.totalGoods = goodsRes.total || 0

        const pendingRes = await http.get(api.wholesalerReservations, { status: 'pending', page: 1, limit: 1 }, { hideError: true })
        this.stats.pendingBookings = pendingRes.total || 0

        const confirmedRes = await http.get(api.wholesalerReservations, { status: 'confirmed', page: 1, limit: 1 }, { hideError: true })
        this.stats.confirmedBookings = confirmedRes.total || 0

        this.stats.todayGoods = goodsRes.total || 0
        this.stats.todayPending = pendingRes.total || 0
      } catch (e) { uni.showToast({ title: e.message || '操作失败', icon: 'none' }) }
    },

    async loadPending() {
      try {
        const data = await http.get(api.wholesalerReservations, {
          status: 'pending', page: 1, limit: 10
        }, { hideError: true })
        this.pendingList = data.list || []
      } catch (e) { uni.showToast({ title: e.message || '操作失败', icon: 'none' }) }
    },

    go(page) {
      const urls = {
        publish:      '/pages/wholesaler/publish',
        goods:         '/pages/wholesaler/goods',
        reservations: '/pages/wholesaler/reservations',
        qrcode:        '/pages/wholesaler/qrcode'
      }
      uni.navigateTo({ url: urls[page] })
    },

    callBuyerFor(r) {
      uni.showToast({ title: '演示:拨打采购商电话', icon: 'none' })
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
      return `${d.getMonth() + 1}-${pad(d.getDate())} ${hm}`
    },

    async confirm(r) {
      uni.showModal({
        title: '确认预订',
        content: `确认「${r.goods_name}」 ×${r.quantity}${r.unit}？\n总价 ¥${(Number(r.price) * r.quantity).toFixed(2)}`,
        success: async ({ confirm }) => {
          if (!confirm) return
          try {
            await http.post(api.wholesalerReservationConfirm, { id: r.id }, { hideError: true })
            uni.showToast({ title: '已确认预订', icon: 'success' })
            this.loadAll()
          } catch (e) { uni.showToast({ title: e.message || '操作失败', icon: 'none' }) }
        }
      })
    },

    async cancel(r) {
      uni.showModal({
        title: '取消预订',
        content: `确认取消「${r.goods_name}」?`,
        success: async ({ confirm }) => {
          if (!confirm) return
          try {
            await http.post(api.wholesalerReservationCancel, { id: r.id, reason: '批发商取消' }, { hideError: true })
            uni.showToast({ title: '已取消', icon: 'success' })
            this.loadAll()
          } catch (e) { uni.showToast({ title: e.message || '操作失败', icon: 'none' }) }
        }
      })
    }
  }
}
</script>

<style scoped>
page { background-color: #f5f5f5; }
.container { padding-bottom: 120rpx; }

/* === 头部 === */
.header {
  background: linear-gradient(135deg, #ff6600, #ff8a5b);
  padding: 36rpx 30rpx 80rpx;
  color: #fff;
}
.header-row { display: flex; align-items: center; gap: 20rpx; }
.shop-logo {
  width: 96rpx; height: 96rpx;
  border-radius: 20rpx;
  background: rgba(255,255,255,.25);
  flex-shrink: 0;
}
.header-info { flex: 1; min-width: 0; }
.shop-name { font-size: 36rpx; font-weight: 700; }
.account { font-size: 22rpx; opacity: .85; margin-top: 6rpx; }
.header-stat {
  background: rgba(255,255,255,.22);
  border-radius: 14rpx;
  padding: 14rpx 20rpx;
  text-align: center;
  flex-shrink: 0;
}
.header-stat .num { display: block; font-size: 36rpx; font-weight: 700; line-height: 1.2; }
.header-stat .lbl { font-size: 20rpx; opacity: .9; }

/* === 统计卡片(向上覆盖 header 底部) === */
.stats-row {
  display: flex;
  gap: 16rpx;
  padding: 0 24rpx;
  margin-top: -50rpx;
}
.stat-card {
  flex: 1;
  background: #fff;
  border-radius: 12rpx;
  padding: 20rpx 0;
  text-align: center;
  box-shadow: 0 4rpx 16rpx rgba(0,0,0,.06);
  border-bottom: 4rpx solid #ccc;
}
.stat-card.green  { border-bottom-color: #27ae60; }
.stat-card.orange { border-bottom-color: #ff6600; }
.stat-card.blue   { border-bottom-color: #3498db; }
.stat-num { font-size: 36rpx; font-weight: 700; display: block; color: #111; }
.stat-card.green  .stat-num { color: #27ae60; }
.stat-card.orange .stat-num { color: #ff6600; }
.stat-card.blue   .stat-num { color: #3498db; }
.stat-label { color: #999; font-size: 22rpx; margin-top: 6rpx; }

/* === 卡片 === */
.card {
  background: #fff;
  border-radius: 12rpx;
  margin: 20rpx 24rpx 0;
  padding: 20rpx 28rpx;
  box-shadow: 0 2rpx 12rpx rgba(0,0,0,.04);
}
.section-title {
  font-size: 30rpx;
  font-weight: 700;
  padding-bottom: 16rpx;
  border-bottom: 1rpx solid #f2f2f2;
  margin-bottom: 20rpx;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.badge {
  background: #ff3b30;
  color: #fff;
  font-size: 22rpx;
  padding: 4rpx 14rpx;
  border-radius: 20rpx;
  font-weight: 500;
}

/* === 快捷操作 4 宫格 === */
.quick-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16rpx;
}
.quick-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20rpx 0;
  border-radius: 12rpx;
  background: #fafafa;
  transition: background .15s;
}
.quick-item:active { background: #fff2e8; }
.quick-item .emoji { font-size: 52rpx; }
.quick-item .label { font-size: 24rpx; color: #333; margin-top: 6rpx; }

/* === 待处理预订卡片 === */
.empty-tip {
  color: #999;
  text-align: center;
  padding: 60rpx 0;
  font-size: 26rpx;
}

.booking-card {
  padding: 20rpx 0;
  border-bottom: 1rpx solid #f5f5f5;
}
.booking-card:last-child { border-bottom: none; }

.booking-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.booking-no { color: #999; font-size: 22rpx; }
.booking-status {
  font-size: 22rpx;
  padding: 4rpx 14rpx;
  border-radius: 4rpx;
  background: #fff1e6;
  color: #ff6600;
  font-weight: 500;
}
.booking-time {
  color: #999;
  font-size: 22rpx;
  margin-top: 6rpx;
}

.goods-row {
  display: flex;
  gap: 16rpx;
  padding: 16rpx 0;
  align-items: center;
}
.goods-cover {
  width: 120rpx;
  height: 120rpx;
  border-radius: 8rpx;
  background: #f5f5f5;
  flex-shrink: 0;
}
.goods-info { flex: 1; min-width: 0; }
.goods-name {
  font-size: 28rpx;
  font-weight: 500;
  color: #111;
  line-height: 1.4;
}
.goods-spec {
  color: #999;
  font-size: 22rpx;
  margin-top: 6rpx;
}
.goods-total {
  color: #ff6600;
  font-size: 30rpx;
  font-weight: 700;
  margin-top: 6rpx;
}

.kv {
  display: flex;
  font-size: 26rpx;
  padding: 6rpx 0;
  align-items: center;
}
.kv .k { color: #666; width: 140rpx; flex-shrink: 0; font-size: 24rpx; }
.kv .v { color: #111; flex: 1; }

.booking-actions {
  display: flex;
  gap: 12rpx;
  margin-top: 16rpx;
}
.btn-gray {
  flex: 1;
  background: #f5f5f5;
  color: #666;
  border-radius: 32rpx;
  padding: 16rpx 0;
  font-size: 26rpx;
  text-align: center;
  font-weight: 500;
}
.btn-gray:active { background: #e5e5e5; }
.btn-outline {
  flex: 1;
  background: #fff;
  color: #ff6600;
  border: 1rpx solid #ff6600;
  border-radius: 32rpx;
  padding: 16rpx 0;
  font-size: 26rpx;
  text-align: center;
  font-weight: 500;
}
.btn-primary {
  flex: 1.5;
  background: #ff6600;
  color: #fff;
  border-radius: 32rpx;
  padding: 16rpx 0;
  font-size: 26rpx;
  text-align: center;
  font-weight: 500;
}
.btn-primary:active { background: #e55a00; }

.more-btn {
  text-align: center;
  color: #ff6600;
  font-size: 26rpx;
  padding: 24rpx 0 4rpx;
  font-weight: 500;
}
</style>
