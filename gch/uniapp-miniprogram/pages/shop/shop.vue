<template>
  <view class="container" v-if="shop">
    <!-- 顶部:渐变背景 + logo + 店名 + 地址 + 营业时间 -->
    <view class="header">
      <view class="header-row">
        <image class="shop-logo" :src="shop.logo || '/static/placeholder.png'" mode="aspectFill"></image>
        <view class="shop-info">
          <view class="shop-name">{{ shop.name }}</view>
          <view class="shop-meta">
            <text class="iconfont icon-dizhi"></text>
            <text class="meta-text">{{ shop.position || '暂无地址' }}</text>
          </view>
          <view class="shop-meta" v-if="shop.business_hours">
            <text class="iconfont icon-shijian"></text>
            <text class="meta-text">{{ shop.business_hours }}</text>
          </view>
        </view>
      </view>
      <view v-if="shop.intro" class="shop-intro">{{ shop.intro }}</view>
    </view>

    <!-- 操作栏(向上覆盖头部底) -->
    <view class="action-bar">
      <view class="action-btn fav" :class="{ active: favorited }" @tap="toggleFav">
        <text class="action-icon">{{ favorited ? '★' : '☆' }}</text>
        <text class="action-label">{{ favorited ? '已收藏' : '收藏' }}</text>
      </view>
      <view class="action-btn call" @tap="callShop">
        <text class="action-icon iconfont icon-dianhua"></text>
        <text class="action-label">拨打电话</text>
      </view>
    </view>

    <!-- 货盘列表(简洁大气) -->
    <view class="goods-list">
      <view v-if="loading && goodsList.length === 0" class="state-empty">⏳ 加载中...</view>
      <view v-else-if="goodsList.length === 0" class="state-empty">
        <view class="state-icon">📦</view>
        <view>该店铺暂无货盘</view>
      </view>

      <view v-for="g in goodsList" :key="g.id" class="goods-card">
        <image class="goods-cover" :src="g.cover || '/static/placeholder.png'" mode="aspectFill" @tap="goDetail(g.id)"></image>
        <view class="goods-info">
          <view class="goods-name ellipsis-2" @tap="goDetail(g.id)">{{ g.name }}</view>
          <view class="goods-price">¥{{ Number(g.price).toFixed(2) }}<text class="price-unit">/{{ g.unit }}</text></view>
          <view class="goods-stock">库存 {{ g.total_stock }} · 可订 {{ g.available }}</view>
        </view>
        <view class="goods-action" @tap="quickReserve(g)">一键预定</view>
      </view>
    </view>

    <!-- 一键预定 sheet -->
    <view class="sheet-mask" v-if="showSheet" @tap="closeSheet"></view>
    <view class="sheet" :class="{ 'sheet-show': showSheet }" v-if="bookGoods">
      <view class="sheet-handle"></view>
      <view class="sheet-header">
        <view class="sheet-title">
          <view class="title-main">预订货品</view>
          <view class="title-sub">{{ bookGoods.name }}</view>
        </view>
        <view class="sheet-close" @tap="closeSheet">×</view>
      </view>
      <view class="sheet-body">
        <view class="sheet-price">
          <text class="price-label">单价</text>
          <text class="price-value">¥{{ Number(bookGoods.price).toFixed(2) }}</text>
          <text class="price-unit">/{{ bookGoods.unit }}</text>
        </view>
        <view class="sheet-stock">
          <text class="stock-label">可订库存</text>
          <text class="stock-value">{{ bookGoods.available.toLocaleString() }} {{ bookGoods.unit }}</text>
        </view>
        <view class="qty-section">
          <text class="qty-label">预订数量</text>
          <view class="qty-control">
            <view class="qty-btn" @tap="decQty">-</view>
            <input class="qty-input" type="number" v-model.number="bookQty" :min="1" :max="bookGoods.available" />
            <text class="qty-unit">{{ bookGoods.unit }}</text>
            <view class="qty-btn" @tap="incQty">+</view>
          </view>
          <view class="qty-total">
            <text>预计金额</text>
            <text class="total-amount">¥{{ ((Number(bookGoods.price) || 0) * bookQty).toFixed(2) }}</text>
          </view>
        </view>
      </view>
      <button class="sheet-submit" :disabled="submitting" @tap="submitBook">
        {{ submitting ? '提交中...' : '提交预订' }}
      </button>
      <view class="sheet-tip">
        📌 提交后由批发商确认,确认前可取消
      </view>
    </view>
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'
import userStore from '@/store/user.js'

export default {
  data() {
    return {
      id: 0,
      shop: null,
      goodsList: [],
      loading: false,
      favorited: false,
      fromQrcode: false,
      showSheet: false,
      bookGoods: null,
      bookQty: 1,
      submitting: false
    }
  },
  computed: {
    isBuyer() { return userStore.isBuyer },
    totalToday() {
      const todayStart = Math.floor(new Date().setHours(0, 0, 0, 0) / 1000)
      return this.goodsList.filter(g => g.publish_time >= todayStart).length
    }
  },
  onLoad(q) {
    // 兼容三种入口:
    //   1. 普通跳转:?id=5
    //   2. 扫码进入:?scene=id%3D5  (微信 wxacode getUnlimited 把 scene 整个丢到 query.scene)
    //   3. 旧式 wxacode:?id=5(直接带 query.id)
    if (q.id) {
      this.id = q.id
      this.fromQrcode = !!q.scene || q.from === 'qrcode'
    } else if (q.scene) {
      // scene 格式 "id=5" 或 base64 编码,解析出 id
      let sceneStr = q.scene
      try {
        const decoded = decodeURIComponent(q.scene)
        if (/^\d+$/.test(decoded)) {
          this.id = parseInt(decoded, 10)
        } else {
          const m = decoded.match(/id=(\d+)/)
          this.id = m ? parseInt(m[1], 10) : 0
        }
      } catch (e) {
        const m = String(q.scene).match(/id=(\d+)/)
        this.id = m ? parseInt(m[1], 10) : 0
      }
      this.fromQrcode = true
    }
    if (this.fromQrcode && this.id) {
      uni.showToast({ title: '已为您打开店铺', icon: 'none' })
    }
    if (!this.id) {
      uni.showToast({ title: '店铺参数错误', icon: 'none' })
      return
    }
    this.loadShop()
    this.loadGoods()
  },
  methods: {
    isToday(ts) {
      if (!ts) return false
      const todayStart = Math.floor(new Date().setHours(0, 0, 0, 0) / 1000)
      return ts >= todayStart
    },

    async loadShop() {
      try {
        this.shop = await http.get(api.shopDetail(this.id), {}, { hideError: true })
        if (this.shop && this.shop.fav !== undefined) {
          this.favorited = this.shop.fav
        }
      } catch (e) {}
    },

    async loadGoods() {
      this.loading = true
      try {
        const data = await http.get(api.goodsList, {
          page: 1, limit: 50, shop_id: this.id
        }, { hideError: true })
        this.goodsList = (data.list || []).map(g => ({
          ...g,
          publish_time_label: this.formatDate(g.publish_time)
        }))
      } catch (e) {}
      this.loading = false
    },

    goDetail(id) { uni.navigateTo({ url: `/pages/goods/goods?id=${id}` }) },

    async toggleFav() {
      if (!userStore.token) return uni.navigateTo({ url: '/pages/login/account?role=buyer' })
      try {
        await http.post(api.favoriteShop, { shop_id: this.id }, { hideError: true })
        this.favorited = !this.favorited
        uni.showToast({ title: this.favorited ? '已收藏' : '已取消收藏', icon: 'none' })
      } catch (e) {}
    },

    callShop() {
      if (!this.shop || !this.shop.contact_phone) {
        return uni.showToast({ title: '店铺信息不存在', icon: 'none' })
      }
      uni.showToast({ title: `正在拨打 ${this.shop.name} 的电话…`, icon: 'none' })
      setTimeout(() => uni.makePhoneCall({ phoneNumber: this.shop.contact_phone }), 600)
    },

    quickReserve(g) {
      if (!userStore.canOrder) {
        return uni.showModal({
          title: '请先登录',
          content: '下单需使用账号密码登录采购商账号',
          confirmText: '去登录',
          success: ({ confirm }) => {
            if (confirm) uni.navigateTo({ url: '/pages/login/account?role=buyer' })
          }
        })
      }
      this.bookGoods = g
      // 智能默认数量:可订库存的 5%(至少 1,最多 500)
      const defaultQty = Math.max(1, Math.min(500, Math.floor(g.available / 20) || 10))
      this.bookQty = defaultQty
      this.showSheet = true
    },
    closeSheet() { this.showSheet = false },

    incQty() {
      if (!this.bookGoods) return
      const max = this.bookGoods.available
      if (this.bookQty < max) this.bookQty++
      else uni.showToast({ title: '已达可订库存上限', icon: 'none' })
    },
    decQty() {
      if (this.bookQty > 1) this.bookQty--
    },

    async submitBook() {
      if (!this.bookGoods) return
      const qty = parseInt(this.bookQty, 10)
      if (!qty || qty < 1) return uni.showToast({ title: '请填写正确的预订数量', icon: 'none' })
      if (qty > this.bookGoods.available) return uni.showToast({ title: '预订数量超过库存', icon: 'none' })
      this.submitting = true
      try {
        const order = await http.post(api.reservationCreate, {
          goods_id: this.bookGoods.id, quantity: qty
        }, { hideError: true })
        uni.showModal({
          title: '预订成功!',
          content: `编号 ${order.reservation_no}\n等待批发商确认`,
          showCancel: false,
          success: () => {
            this.showSheet = false
            uni.switchTab({ url: '/pages/orders/orders' })
          }
        })
      } catch (e) { uni.showToast({ title: e.message || '操作失败', icon: 'none' }) }
      this.submitting = false
    },

    formatDate(ts) {
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
    }
  }
}
</script>

<style scoped>
page { background-color: #f5f5f5; }
.container { padding-bottom: 40rpx; }

/* === 头部 === */
.header {
  background: linear-gradient(135deg, #ff6600, #ff8a5b);
  padding: 36rpx 30rpx 80rpx;
  color: #fff;
}
.header-row {
  display: flex;
  align-items: center;
  gap: 24rpx;
}
.shop-logo {
  width: 112rpx;
  height: 112rpx;
  border-radius: 24rpx;
  background: rgba(255,255,255,.25);
  flex-shrink: 0;
}
.shop-info { flex: 1; min-width: 0; }
.shop-name {
  font-size: 38rpx;
  font-weight: 700;
  line-height: 1.2;
}
.shop-meta {
  display: flex;
  align-items: center;
  font-size: 24rpx;
  opacity: .9;
  margin-top: 8rpx;
  gap: 6rpx;
}
.meta-text {
  flex: 1;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}
.shop-intro {
  margin-top: 16rpx;
  padding: 16rpx 20rpx;
  background: rgba(255,255,255,.18);
  border-radius: 12rpx;
  font-size: 24rpx;
  line-height: 1.5;
}

/* === 操作栏(向上覆盖头部底) === */
.action-bar {
  display: flex;
  gap: 16rpx;
  padding: 20rpx 24rpx 0;
  margin: -40rpx 24rpx 0;
  position: relative;
  z-index: 2;
}
.action-btn {
  flex: 1;
  background: #fff;
  border-radius: 16rpx;
  padding: 24rpx 0;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8rpx;
  box-shadow: 0 4rpx 16rpx rgba(0,0,0,.06);
  transition: all .15s;
}
.action-btn:active { transform: scale(0.98); }
.action-btn.fav { color: #999; }
.action-btn.fav.active {
  background: #fff2e8;
  color: #ff6600;
}
.action-btn.call { color: #ff6600; }
.action-icon { font-size: 36rpx; line-height: 1; }
.action-label { font-size: 24rpx; }

/* === 货盘列表(简洁) === */
.goods-list {
  padding: 20rpx 24rpx 0;
}
.state-empty {
  text-align: center;
  padding: 100rpx 0;
  color: #999;
  font-size: 28rpx;
}
.state-icon {
  font-size: 80rpx;
  display: block;
  margin-bottom: 16rpx;
}

/* === 货品卡片(简洁大气) === */
.goods-card {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  background: #fff;
  border-radius: 16rpx;
  padding: 24rpx;
  margin-bottom: 20rpx;
  box-shadow: 0 2rpx 12rpx rgba(0,0,0,.04);
}
.goods-cover {
  width: 160rpx;
  height: 160rpx;
  border-radius: 12rpx;
  background: #f5f5f5;
  flex-shrink: 0;
}
.goods-info {
  flex: 1;
  margin-left: 24rpx;
  min-width: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  height: 160rpx;
}
.goods-name {
  font-size: 32rpx;
  font-weight: 600;
  color: #111;
  line-height: 1.3;
  margin-bottom: 12rpx;
}
.goods-price {
  color: #ff6600;
  font-size: 36rpx;
  font-weight: 700;
  margin-bottom: 6rpx;
}
.price-unit {
  font-size: 24rpx;
  font-weight: 400;
  color: #999;
  margin-left: 2rpx;
}
.goods-stock {
  color: #999;
  font-size: 22rpx;
}
.goods-action {
  width: 100%;
  margin-top: 24rpx;
  background: #ff6600;
  color: #fff;
  border-radius: 44rpx;
  padding: 20rpx 0;
  text-align: center;
  font-size: 30rpx;
  font-weight: 500;
  transition: background .15s;
}
.goods-action:active { background: #e55a00; }

/* === Sheet 弹层 === */
.sheet-mask {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.55);
  z-index: 999;
}
.sheet {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  background: #fff;
  border-top-left-radius: 24rpx;
  border-top-right-radius: 24rpx;
  z-index: 1000;
  padding: 16rpx 24rpx 36rpx;
  transform: translateY(100%);
  transition: transform .25s ease-out;
}
.sheet-show { transform: translateY(0); }
.sheet-handle {
  width: 60rpx;
  height: 6rpx;
  background: #e0e0e0;
  border-radius: 3rpx;
  margin: 0 auto 16rpx;
}
.sheet-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding-bottom: 20rpx;
  border-bottom: 1rpx solid #f2f2f2;
}
.sheet-title { flex: 1; }
.title-main {
  font-size: 24rpx;
  color: #999;
  margin-bottom: 4rpx;
}
.title-sub {
  font-size: 32rpx;
  font-weight: 700;
  color: #111;
  line-height: 1.4;
}
.sheet-close {
  font-size: 44rpx;
  color: #ccc;
  width: 50rpx;
  height: 50rpx;
  line-height: 44rpx;
  text-align: center;
  flex-shrink: 0;
}

.sheet-body { padding: 24rpx 0 8rpx; }
.sheet-price, .sheet-stock {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  padding: 16rpx 0;
  border-bottom: 1rpx dashed #f2f2f2;
}
.sheet-price .price-label, .sheet-stock .stock-label {
  color: #999;
  font-size: 26rpx;
}
.sheet-price .price-value {
  color: #ff6600;
  font-size: 36rpx;
  font-weight: 700;
}
.sheet-price .price-unit {
  color: #999;
  font-size: 22rpx;
}
.sheet-stock .stock-value {
  color: #111;
  font-size: 28rpx;
  font-weight: 500;
}

/* 数量控制 */
.qty-section {
  padding: 24rpx 0 8rpx;
}
.qty-label {
  color: #999;
  font-size: 26rpx;
  display: block;
  margin-bottom: 16rpx;
}
.qty-control {
  display: flex;
  align-items: center;
  border: 1rpx solid #e5e5e5;
  border-radius: 12rpx;
  height: 72rpx;
  overflow: hidden;
}
.qty-btn {
  width: 80rpx;
  height: 100%;
  background: #fafafa;
  color: #ff6600;
  font-size: 40rpx;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background .15s;
}
.qty-btn:active { background: #f0f0f0; }
.qty-input {
  flex: 1;
  height: 70rpx;
  font-size: 32rpx;
  text-align: center;
  color: #111;
}
.qty-unit {
  color: #999;
  font-size: 24rpx;
  padding: 0 20rpx;
}
.qty-total {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-top: 20rpx;
  padding: 16rpx;
  background: #fff7eb;
  border-radius: 12rpx;
}
.qty-total text:first-child {
  color: #999;
  font-size: 24rpx;
}
.total-amount {
  color: #ff6600;
  font-size: 36rpx;
  font-weight: 700;
}

.sheet-submit {
  width: 100%;
  background: #ff6600;
  color: #fff;
  border-radius: 40rpx;
  padding: 24rpx 0;
  font-size: 32rpx;
  font-weight: 500;
  margin-top: 24rpx;
}
.sheet-submit[disabled] { background: #ccc; }
.sheet-submit:active:not([disabled]) { background: #e55a00; }
.sheet-tip {
  color: #999;
  font-size: 22rpx;
  text-align: center;
  margin-top: 16rpx;
  line-height: 1.5;
}
</style>
