<template>
  <view class="container">
    <!-- 头部:店名 + 账号(规格 6.6) -->
    <view class="header">
      <view class="header-row">
        <text class="shop-emoji">🏬</text>
        <view class="header-info">
          <view class="shop-name">{{ shopName }}</view>
          <view class="account">账号 {{ account }}</view>
        </view>
      </view>
    </view>

    <!-- 信息条:今日新增货盘 + 待处理预订 -->
    <view class="info-bar">
      📊 今日:新增货盘 {{ stats.todayGoods }} · 待处理预订 {{ stats.todayPending }}
    </view>

    <!-- 三宫格统计(规格 6.6) -->
    <view class="stats-row">
      <view class="stat-card">
        <text class="stat-num">{{ stats.totalGoods || 0 }}</text>
        <text class="stat-label">在售货盘数</text>
      </view>
      <view class="stat-card">
        <text class="stat-num">{{ stats.pendingBookings || 0 }}</text>
        <text class="stat-label">待确认预订数</text>
      </view>
      <view class="stat-card">
        <text class="stat-num">{{ stats.confirmedBookings || 0 }}</text>
        <text class="stat-label">已预订总量</text>
      </view>
    </view>

    <!-- 4 宫格快捷操作(规格 6.6) -->
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
        <view class="quick-item" @tap="callBuyer">
          <text class="emoji">📞</text>
          <text class="label">联系采购商</text>
        </view>
      </view>
    </view>

    <!-- 待处理预订列表(规格 6.6) -->
    <view class="card">
      <view class="section-title">
        待处理预订 {{ pendingList.length }} 笔待确认
        <text v-if="pendingList.length > 0" class="badge">{{ pendingList.length }}</text>
      </view>

      <view v-if="pendingList.length === 0" class="empty-tip">
        📋 暂无待处理订单
      </view>

      <view v-for="r in pendingList" :key="r.id" class="booking-card">
        <view class="booking-head">
          <text class="booking-no">{{ r.reservation_no }}</text>
          <text class="booking-status status-pending">待确认</text>
        </view>
        <view class="kv">
          <text class="k">货品</text>
          <text class="v">{{ r.goods_name }}</text>
        </view>
        <view class="kv">
          <text class="k">规格</text>
          <text class="v">¥{{ Number(r.price).toFixed(2) }}/{{ r.unit }} × {{ r.quantity.toLocaleString() }} {{ r.unit }}</text>
        </view>
        <view class="kv">
          <text class="k">采购商</text>
          <text class="v">{{ r.buyer_name || '-' }}</text>
        </view>
        <view class="booking-actions">
          <view class="btn-gray" @tap="cancel(r)">取消预订</view>
          <view class="btn-outline" @tap="callBuyerFor(r)">联系采购商</view>
          <view class="btn-primary" @tap="confirm(r)">确认预订</view>
        </view>
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
      account: '',
      stats: { todayGoods: 0, todayPending: 0, totalGoods: 0, pendingBookings: 0, confirmedBookings: 0 },
      pendingList: []
    }
  },
  onShow() {
    if (userStore.isWholesaler) {
      this.account = userStore.profile?.account || ''
      this.shopName = userStore.profile?.shop?.name || '示范水果店'
      this.loadAll()
    }
  },
  methods: {
    async loadAll() {
      await this.loadStats()
      await this.loadPending()
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

        // 今日数据(简化:全部 pending 算今日)
        this.stats.todayGoods = goodsRes.total || 0
        this.stats.todayPending = pendingRes.total || 0
      } catch (e) { uni.showToast({ title: e.message || "操作失败", icon: "none" }) }
    },

    async loadPending() {
      try {
        const data = await http.get(api.wholesalerReservations, {
          status: 'pending', page: 1, limit: 10
        }, { hideError: true })
        this.pendingList = data.list || []
      } catch (e) { uni.showToast({ title: e.message || "操作失败", icon: "none" }) }
    },

    go(page) {
      const urls = {
        publish: '/pages/wholesaler/publish',
        goods: '/pages/wholesaler/goods',
        reservations: '/pages/wholesaler/reservations'
      }
      uni.navigateTo({ url: urls[page] })
    },

    callBuyer() {
      uni.showToast({ title: '请在订单详情中查看采购商电话', icon: 'none' })
    },
    callBuyerFor(r) {
      uni.showToast({ title: '演示:拨打采购商电话', icon: 'none' })
    },

    async confirm(r) {
      uni.showModal({
        title: '确认预订',
        content: `确认预订「${r.goods_name}」?`,
        success: async ({ confirm }) => {
          if (!confirm) return
          try {
            await http.post(api.wholesalerReservationConfirm, { id: r.id }, { hideError: true })
            uni.showToast({ title: '已确认预订', icon: 'success' })
            this.loadAll()
          } catch (e) { uni.showToast({ title: e.message || "操作失败", icon: "none" }) }
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
            uni.showToast({ title: '已取消预订', icon: 'success' })
            this.loadAll()
          } catch (e) { uni.showToast({ title: e.message || "操作失败", icon: "none" }) }
        }
      })
    }
  }
}
</script>

<style scoped>
.header {
  background: linear-gradient(135deg, #ff6600, #ff8a2b);
  padding: 40rpx 30rpx;
  color: #fff;
}
.header-row { display: flex; align-items: center; gap: 20rpx; }
.shop-emoji {
  width: 100rpx; height: 100rpx; border-radius: 24rpx;
  background: rgba(255,255,255,.25);
  display: flex; align-items: center; justify-content: center;
  font-size: 50rpx;
}
.header-info { flex: 1; }
.shop-name { font-size: 38rpx; font-weight: 700; }
.account { font-size: 24rpx; opacity: .9; margin-top: 8rpx; }

.info-bar {
  background: #fff7eb;
  color: #ff6600;
  font-size: 24rpx;
  padding: 16rpx 30rpx;
  margin-top: -1rpx;
}

.stats-row {
  display: flex;
  gap: 20rpx;
  padding: 20rpx 24rpx 0;
  margin-top: -30rpx;
}
.stat-card {
  flex: 1;
  background: #fff;
  border-radius: 12rpx;
  padding: 24rpx 0;
  text-align: center;
  box-shadow: 0 2rpx 12rpx rgba(0,0,0,.06);
}
.stat-num { font-size: 40rpx; font-weight: 700; color: #ff6600; display: block; }
.stat-label { color: #999; font-size: 22rpx; margin-top: 8rpx; }

.card {
  background: #fff;
  border-radius: 12rpx;
  margin: 20rpx 24rpx;
  padding: 20rpx 28rpx;
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
  font-size: 20rpx;
  padding: 4rpx 12rpx;
  border-radius: 20rpx;
}

.quick-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20rpx;
}
.quick-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 24rpx 0;
  border-radius: 12rpx;
  background: #fafafa;
}
.quick-item:active { background: #f0f0f0; }
.quick-item .emoji { font-size: 56rpx; }
.quick-item .label { font-size: 24rpx; color: #333; margin-top: 8rpx; }

.empty-tip {
  color: #999;
  text-align: center;
  padding: 40rpx 0;
  font-size: 26rpx;
}

.booking-card {
  padding: 20rpx 0;
  border-bottom: 1rpx solid #f2f2f2;
}
.booking-card:last-child { border-bottom: none; }
.booking-head { display: flex; justify-content: space-between; }
.booking-no { color: #999; font-size: 22rpx; }
.booking-status { font-size: 22rpx; padding: 4rpx 12rpx; border-radius: 4rpx; }
.status-pending { color: #ff6600; background: #fff1e6; }

.kv {
  display: flex;
  font-size: 26rpx;
  padding: 8rpx 0;
}
.kv .k { color: #666; width: 140rpx; flex-shrink: 0; }
.kv .v { color: #111; flex: 1; }

.booking-actions {
  display: flex;
  gap: 12rpx;
  margin-top: 16rpx;
}
.btn-gray {
  flex: 1;
  background: #f2f2f2;
  color: #666;
  border-radius: 16rpx;
  padding: 14rpx 0;
  font-size: 26rpx;
  text-align: center;
}
.btn-outline {
  flex: 1;
  background: #fff;
  color: #ff6600;
  border: 1rpx solid #ff6600;
  border-radius: 16rpx;
  padding: 14rpx 0;
  font-size: 26rpx;
  text-align: center;
}
.btn-primary {
  flex: 1;
  background: #ff6600;
  color: #fff;
  border-radius: 16rpx;
  padding: 14rpx 0;
  font-size: 26rpx;
  text-align: center;
}
</style>
