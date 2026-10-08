<template>
  <view class="container" v-if="shop">
    <!-- 顶部:橙底 + logo + 店名 + 📍 地址(规格 §6.3) -->
    <view class="header">
      <view class="header-row">
        <image class="shop-logo" :src="shop.logo || '/static/placeholder.png'" mode="aspectFill"></image>
        <view class="shop-info">
          <view class="shop-name">{{ shop.name }}</view>
          <view class="shop-loc">📍 {{ shop.position || '暂无地址' }}</view>
        </view>
      </view>
    </view>

    <!-- 双按钮(行内):收藏 + 拨号(规格 §6.3) -->
    <view class="action-row">
      <view class="btn-plain" @tap="toggleFav">
        {{ favorited ? '★ 已收藏' : '☆ 收藏店铺' }}
      </view>
      <view class="btn-green" @tap="callShop">📞 拨打电话</view>
    </view>

    <!-- 店铺货盘 N 个(规格 §6.3:仅保留「一键预定」) -->
    <view class="card">
      <view class="section-title">店铺货盘 {{ goodsList.length }} 个</view>

      <view v-if="loading" class="loading">加载中...</view>
      <view v-else-if="goodsList.length === 0" class="empty">📦 该店铺暂无货盘</view>

      <view v-for="g in goodsList" :key="g.id" class="goods-card">
        <image class="emoji-cover" :src="g.cover || '/static/placeholder.png'" mode="aspectFill"></image>
        <view class="info">
          <view class="name ellipsis-2">{{ g.name }}</view>
          <view class="meta">{{ g.publish_time_label || '今日上新' }}</view>
          <view class="stock">库存 {{ g.total_stock.toLocaleString() }} {{ g.unit }}</view>
          <view class="price">¥{{ Number(g.price).toFixed(2) }}/{{ g.unit }}</view>
          <view class="card-actions">
            <view class="btn-primary" @tap="quickReserve(g)">一键预定</view>
          </view>
        </view>
      </view>
    </view>

    <!-- 一键预定 sheet(规格 §7.2) -->
    <view class="sheet-mask" v-if="showSheet" @tap="closeSheet"></view>
    <view class="sheet" :class="{ 'sheet-show': showSheet }" v-if="bookGoods">
      <view class="sheet-header">
        <text class="sheet-title">一键预定 · {{ bookGoods.name }}</text>
        <text class="sheet-close" @tap="closeSheet">×</text>
      </view>
      <view class="sheet-body">
        <view class="sheet-row">
          <text class="sheet-label">单价</text>
          <text class="sheet-value price">¥{{ Number(bookGoods.price).toFixed(2) }}/{{ bookGoods.unit }}</text>
        </view>
        <view class="sheet-row">
          <text class="sheet-label">可订库存</text>
          <text class="sheet-value">{{ bookGoods.available.toLocaleString() }} {{ bookGoods.unit }}</text>
        </view>
        <view class="sheet-row">
          <text class="sheet-label">预订数量</text>
          <view class="qty-input-wrap">
            <input class="qty-input" type="number" v-model="bookQty" />
            <text class="qty-unit">{{ bookGoods.unit }}</text>
          </view>
        </view>
        <button class="sheet-submit" :disabled="submitting" @tap="submitBook">
          {{ submitting ? '提交中...' : '提交预订' }}
        </button>
        <view class="sheet-tip">提交后由批发商确认，确认前可取消</view>
      </view>
    </view>
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'
import userStore from '@/store/user.js'

const FOOD_EMOJI = ['🍎', '🍌', '🍇', '🍊', '🍓', '🍑', '🥬', '🥦', '🥕', '🍅', '🌽', '🥔', '🥒', '🍆', '🌶️', '🧅', '🥭', '🍍', '🥥', '🦐', '🦀', '🐟', '🐠', '🍤', '🥚', '🍚', '🥟']

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
      bookQty: 500,
      submitting: false
    }
  },
  computed: {
    isBuyer() { return userStore.isBuyer }
  },
  onLoad(q) {
    this.id = q.id
    this.fromQrcode = q.from === 'qrcode'
    if (this.fromQrcode) {
      uni.showToast({ title: '已为您打开店铺', icon: 'none' })
    }
    this.loadShop()
    this.loadGoods()
  },
  methods: {
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
        // 用货盘列表 API 过滤本店
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

    async toggleFav() {
      if (!userStore.token) return uni.navigateTo({ url: '/pages/login/account?role=buyer' })
      try {
        await http.post(api.favoriteShop, { shop_id: this.id }, { hideError: true })
        this.favorited = !this.favorited
        uni.showToast({ title: this.favorited ? '已收藏店铺' : '已取消收藏', icon: 'none' })
      } catch (e) {}
    },

    callShop() {
      if (!this.shop || !this.shop.contact_phone) {
        return uni.showToast({ title: '店铺信息不存在', icon: 'none' })
      }
      // 规格 8.5 toast:正在拨打 {店名} 的电话…
      uni.showToast({ title: `正在拨打 ${this.shop.name} 的电话…`, icon: 'none' })
      setTimeout(() => {
        uni.makePhoneCall({ phoneNumber: this.shop.contact_phone })
      }, 600)
    },

    // 一键预定
    quickReserve(g) {
      // 必须真 token + buyer 角色
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
      this.bookQty = Math.max(1, Math.min(500, Math.floor(g.available / 20) || 500))
      this.showSheet = true
    },
    closeSheet() { this.showSheet = false },
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
      } catch (e) {}
      this.submitting = false
    },

    emojiOf(name) {
      const code = (name || '').split('').reduce((s, c) => s + c.charCodeAt(0), 0)
      return FOOD_EMOJI[code % FOOD_EMOJI.length]
    },

    formatDate(ts) {
      if (!ts) return ''
      const d = new Date(ts * 1000)
      const today = new Date()
      if (d.toDateString() === today.toDateString()) return '今日上新'
      return `${d.getMonth() + 1}月${d.getDate()}日`
    }
  }
}
</script>

<style scoped>
.container { padding-bottom: 40rpx; }

/* 顶部(规格 §6.3) */
.header {
  background: linear-gradient(135deg, #ff6600, #ff6600);
  padding: 40rpx 30rpx;
  color: #fff;
}
.header-row { display: flex; align-items: center; gap: 24rpx; }
.shop-logo {
  width: 104rpx;
  height: 104rpx;
  border-radius: 24rpx;
  background: rgba(255,255,255,.25);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 52rpx;
  flex-shrink: 0;
}
.shop-info { flex: 1; }
.shop-name { font-size: 36rpx; font-weight: 700; }
.shop-loc { font-size: 24rpx; opacity: .92; margin-top: 8rpx; }

/* 双按钮(规格 §6.3:行内) */
.action-row {
  display: flex;
  gap: 20rpx;
  padding: 20rpx 24rpx 0;
  margin-top: -20rpx;
}
.btn-plain {
  flex: 1;
  background: #fff;
  color: #ff6600;
  border: 1rpx solid #ff6600;
  border-radius: 16rpx;
  padding: 24rpx 0;
  text-align: center;
  font-size: 28rpx;
}
.btn-green {
  flex: 1;
  background: #07c160;
  color: #fff;
  border-radius: 16rpx;
  padding: 24rpx 0;
  text-align: center;
  font-size: 28rpx;
}

/* 店铺货盘(规格 §6.3:仅保留「一键预定」) */
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
}
.loading, .empty {
  color: #999;
  text-align: center;
  padding: 40rpx 0;
  font-size: 26rpx;
}

.goods-card {
  display: flex;
  gap: 20rpx;
  padding: 20rpx 0;
  border-bottom: 1rpx solid #f2f2f2;
}
.goods-card:last-child { border-bottom: none; }
.emoji-cover {
  width: 140rpx;
  height: 140rpx;
  border-radius: 12rpx;
  background: linear-gradient(135deg, #fff3e0, #ffe0b2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 70rpx;
  flex-shrink: 0;
}
.info { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.name { font-size: 30rpx; font-weight: 600; color: #111; }
.meta { color: #999; font-size: 22rpx; margin-top: 6rpx; }
.stock { color: #999; font-size: 22rpx; margin-top: 4rpx; }
.price { color: #ff6600; font-size: 32rpx; font-weight: 700; margin-top: 10rpx; }
.card-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 14rpx;
}
.btn-primary {
  background: #ff6600;
  color: #fff;
  border-radius: 16rpx;
  padding: 16rpx 36rpx;
  font-size: 28rpx;
}

/* Sheet 弹层 */
.sheet-mask {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.5);
  z-index: 999;
}
.sheet {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  background: #fff;
  border-top-left-radius: 16rpx;
  border-top-right-radius: 16rpx;
  z-index: 1000;
  padding: 20rpx 18rpx 30rpx;
  transform: translateY(100%);
  transition: transform .25s ease-out;
}
.sheet-show { transform: translateY(0); }
.sheet-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 20rpx;
  border-bottom: 1rpx solid #f2f2f2;
}
.sheet-title { font-size: 32rpx; font-weight: 700; color: #111; flex: 1; }
.sheet-close {
  font-size: 40rpx;
  color: #999;
  width: 50rpx;
  height: 50rpx;
  line-height: 50rpx;
  text-align: center;
}
.sheet-body { padding-top: 20rpx; }
.sheet-row {
  display: flex;
  align-items: center;
  padding: 16rpx 0;
  justify-content: space-between;
}
.sheet-label { color: #666; font-size: 28rpx; }
.sheet-value { color: #111; font-size: 28rpx; }
.sheet-value.price { color: #ff6600; font-weight: 600; }
.qty-input-wrap {
  display: flex;
  align-items: center;
  border: 1rpx solid #e5e5e5;
  border-radius: 12rpx;
  padding: 0 16rpx;
  height: 64rpx;
}
.qty-input {
  flex: 1;
  height: 60rpx;
  font-size: 32rpx;
  text-align: right;
}
.qty-unit { color: #999; font-size: 26rpx; margin-left: 12rpx; }
.sheet-submit {
  width: 100%;
  background: #ff6600;
  color: #fff;
  border-radius: 12rpx;
  padding: 24rpx 0;
  font-size: 32rpx;
  font-weight: 500;
  margin-top: 30rpx;
}
.sheet-submit[disabled] { background: #ccc; }
.sheet-tip {
  color: #999;
  font-size: 22rpx;
  text-align: center;
  margin-top: 16rpx;
  line-height: 1.5;
}
</style>
