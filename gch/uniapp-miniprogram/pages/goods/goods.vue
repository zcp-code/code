<template>
  <view class="container" v-if="goods">
    <swiper class="banner" indicator-dots autoplay circular>
      <swiper-item v-for="img in images" :key="img.id">
        <image :src="img.url" mode="aspectFill" class="banner-img"></image>
      </swiper-item>
      <swiper-item v-if="images.length === 0">
        <view class="banner-empty">暂无图片</view>
      </swiper-item>
    </swiper>

    <view class="card">
      <view class="price-row">
        <text class="price">¥{{ Number(goods.price).toFixed(2) }}</text>
        <text class="unit">/{{ goods.unit }}</text>
        <text class="available">可订 {{ goods.available }}</text>
      </view>
      <view class="name">{{ goods.name }}</view>
      <view class="meta">店铺:{{ goods.shop ? goods.shop.name : '-' }}</view>
    </view>

    <view class="card">
      <view class="section-title">货品描述</view>
      <view class="desc">{{ goods.description || '暂无描述' }}</view>
    </view>

    <view class="action-bar">
      <view class="action-btn call" @tap="callWholesaler">📞 联系批发商</view>
      <view class="action-btn fav" :class="favorited ? 'active' : ''" @tap="toggleFav">
        {{ favorited ? '★ 已收藏' : '☆ 收藏' }}
      </view>
      <view class="action-btn primary" @tap="openBookSheet">一键预订</view>
    </view>

    <!-- 规格 §7.2 一键预定 sheet -->
    <view class="sheet-mask" v-if="showSheet" @tap="closeBookSheet"></view>
    <view class="sheet" :class="{ 'sheet-show': showSheet }" v-if="goods">
      <view class="sheet-header">
        <text class="sheet-title">一键预定 · {{ goods.name }}</text>
        <text class="sheet-close" @tap="closeBookSheet">×</text>
      </view>
      <view class="sheet-body">
        <view class="sheet-row">
          <text class="sheet-label">单价</text>
          <text class="sheet-value price">¥{{ Number(goods.price).toFixed(2) }}/{{ goods.unit }}</text>
        </view>
        <view class="sheet-row">
          <text class="sheet-label">可订库存</text>
          <text class="sheet-value">{{ goods.available.toLocaleString() }} {{ goods.unit }}</text>
        </view>
        <view class="sheet-row">
          <text class="sheet-label">预订数量</text>
          <view class="qty-input-wrap">
            <input class="qty-input" type="number" v-model="bookQty" />
            <text class="qty-unit">{{ goods.unit }}</text>
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

export default {
  data() {
    return {
      id: 0,
      goods: null,
      images: [],
      favorited: false,
      showSheet: false,
      bookQty: 500,
      submitting: false
    }
  },
  computed: {
    isBuyer() { return userStore.isBuyer }
  },
  onLoad(q) {
    this.id = q.id
    this.load()
  },
  methods: {
    async load() {
      try {
        const goods = await http.get(api.goodsDetail(this.id), {}, { hideError: true })
        this.goods = goods
        this.images = goods.images || []
        // 默认数量:库存 5% 取整,但不少于 1,不超过可订库存
        this.bookQty = Math.max(1, Math.min(500, Math.floor(goods.available / 20) || 500))
      } catch (e) {}
    },
    openBookSheet() {
      if (!this.goods) return
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
      this.showSheet = true
    },
    closeBookSheet() { this.showSheet = false },
    async submitBook() {
      if (!this.goods) return
      const qty = parseInt(this.bookQty, 10)
      // 校验(规格 8.4):整数 ≥ 1 且 ≤ 可订库存
      if (!qty || qty < 1) {
        return uni.showToast({ title: '请填写正确的预订数量', icon: 'none' })
      }
      if (qty > this.goods.available) {
        return uni.showToast({ title: '预订数量超过库存', icon: 'none' })
      }
      this.submitting = true
      try {
        const order = await http.post(api.reservationCreate, {
          goods_id: this.goods.id, quantity: qty
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

    async toggleFav() {
      if (!userStore.token) return uni.navigateTo({ url: '/pages/login/account?role=buyer' })
      try {
        await http.post(api.favoriteGoods, { goods_id: this.id }, { hideError: true })
        this.favorited = !this.favorited
        uni.showToast({ title: this.favorited ? '已收藏' : '已取消收藏', icon: 'none' })
      } catch (e) {}
    },

    async callWholesaler() {
      try {
        const data = await http.post(api.callDial, { shop_id: this.goods.shop_id }, { hideError: true })
        uni.makePhoneCall({ phoneNumber: data.phone })
      } catch (e) {}
    }
  }
}
</script>

<style scoped>
.container { padding-bottom: 180rpx; }

.banner { width: 100%; height: 400rpx; background: #f5f5f5; }
.banner-img { width: 100%; height: 400rpx; }
.banner-empty { display: flex; align-items: center; justify-content: center; height: 400rpx; color: #ccc; font-size: 30rpx; }

.price-row { display: flex; align-items: baseline; margin-bottom: 12rpx; }
.price { color: #ff4d00; font-size: 48rpx; font-weight: 700; }
.unit { color: #999; font-size: 28rpx; margin-left: 6rpx; }
.available { margin-left: auto; color: #999; font-size: 24rpx; }
.name { font-size: 32rpx; font-weight: 600; line-height: 1.4; }
.meta { color: #999; font-size: 24rpx; margin-top: 12rpx; }

.section-title {
  font-size: 30rpx;
  font-weight: 700;
  padding-bottom: 16rpx;
  border-bottom: 1rpx solid #f2f2f2;
  margin-bottom: 16rpx;
}
.desc { color: #666; font-size: 28rpx; line-height: 1.6; }

.action-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: #fff;
  display: flex;
  padding: 16rpx 20rpx;
  box-shadow: 0 -2rpx 8rpx rgba(0,0,0,.04);
  z-index: 100;
}
.action-btn {
  flex: 1;
  text-align: center;
  padding: 20rpx 0;
  border-radius: 16rpx;
  margin: 0 6rpx;
  font-size: 28rpx;
}
.action-btn.call { background: #f2f2f2; color: #333; }
.action-btn.fav { background: #fff1e6; color: #ff6600; }
.action-btn.fav.active { background: #ff6600; color: #fff; }
.action-btn.primary { background: #ff6600; color: #fff; flex: 1.5; }

/* ===== Sheet 弹层(规格 §7) ===== */
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
.sheet-value.price { color: #ff4d00; font-weight: 600; }

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
