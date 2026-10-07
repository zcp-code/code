<template>
  <view class="container">
    <!-- 橙色头部 -->
    <view class="header">
      <text class="title">果仓货盘 🍎</text>
      <text class="subtitle">生产档口直连</text>
    </view>

    <!-- 搜索框(可点击跳转搜索页) -->
    <view class="search-bar" @tap="goSearch">
      <text class="search-icon iconfont icon-sousuo"></text>
      <text class="search-placeholder">搜索货品 / 店铺</text>
    </view>

    <!-- 三宫格统计 -->
    <view class="stats-row">
      <view class="stat-card">
        <text class="stat-num">{{ stats.goods || 0 }}</text>
        <text class="stat-label">在售货盘</text>
      </view>
      <view class="stat-card">
        <text class="stat-num">{{ stats.shops || 0 }}</text>
        <text class="stat-label">入驻店铺</text>
      </view>
      <view class="stat-card">
        <text class="stat-num">{{ stats.myReservations || 0 }}</text>
        <text class="stat-label">我的预订</text>
      </view>
    </view>

    <!-- 品类横滑 chip -->
    <scroll-view class="category-scroll" scroll-x>
      <view class="chip" :class="{ active: !categoryId }" @tap="selectCat('')">全部</view>
      <view v-for="c in categories" :key="c.id" class="chip" :class="{ active: categoryId == c.id }" @tap="selectCat(c.id)">
        {{ c.name }}
      </view>
    </scroll-view>

    <!-- 货盘列表 -->
    <view class="goods-list">
		<!-- @tap="goDetail(g.id)" -->
      <view v-for="g in list" :key="g.id" class="goods-card" >
        <view class="emoji-cover">{{ emojiOf(g.name) }}</view>
        <view class="goods-info">
          <view class="goods-name ellipsis-2">{{ g.name }}</view>
          <view class="goods-meta">{{ g.shop_name }} · {{ formatTime(g.publish_time) }}</view>
          <view class="goods-stock">库存 {{ g.total_stock }} {{ g.unit }}</view>
          <view class="goods-bottom">
            <view class="price">¥{{ g.price }}/{{ g.unit }}</view>
          </view>
          <view class="goods-actions">
            <view class="btn-outline" @tap.stop="goShop(g.shop_id)">进入店铺</view>
            <view class="btn-primary" @tap.stop="quickReserve(g)">一键预定</view>
          </view>
        </view>
      </view>
      <view v-if="loading" class="loading">加载中...</view>
      <view v-else-if="finished && list.length > 0" class="loading">— 没有更多了 —</view>
      <view v-else-if="list.length === 0" class="empty">暂无货盘</view>
    </view>

    <!-- 一键预定 sheet(规格 §7.2) -->
    <view class="sheet-mask" v-if="showSheet" @tap="closeBookSheet"></view>
    <view class="sheet" :class="{ 'sheet-show': showSheet }" v-if="bookGoods">
      <view class="sheet-header">
        <text class="sheet-title">一键预定 · {{ bookGoods.name }}</text>
        <text class="sheet-close" @tap="closeBookSheet">×</text>
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

    <custom-tabbar v-if="isLogin" />
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'
import CustomTabbar from '@/components/custom-tabbar/custom-tabbar.vue'
import userStore from '@/store/user.js'

const FOOD_EMOJI = ['🍎', '🍌', '🍇', '🍊', '🍓', '🍑', '🥬', '🥦', '🥕', '🍅', '🌽', '🥔', '🥒', '🍆', '🌶️', '🧅', '🥭', '🍍', '🥥', '🦐', '🦀', '🐟', '🐠', '🍤', '🥚', '🍚', '🥟']

export default {
  components: { CustomTabbar },
  data() {
    return {
      list: [],
      total: 0,
      page: 1,
      limit: 20,
      loading: false,
      finished: false,
      categories: [],
      categoryId: '',
      stats: { goods: 0, shops: 0, myReservations: 0 },
      showSheet: false,
      bookGoods: null,
      bookQty: 500,
      submitting: false
    }
  },
  onLoad() { this.loadList(true); this.loadCategories(); this.loadStats() },
  onShow() {
    // 切回来刷新统计
    this.loadStats()
  },
  onPullDownRefresh() {
    this.loadList(true).then(() => uni.stopPullDownRefresh())
    this.loadCategories()
    this.loadStats()
  },
  onReachBottom() {
    if (!this.finished && !this.loading) this.loadList(false)
  },
  computed: {
    isLogin() { return !!userStore.token }
  },
  methods: {
    async loadList(reset) {
      if (reset) { this.page = 1; this.list = []; this.finished = false }
      this.loading = true
      try {
        const data = await http.get(api.goodsList, {
          page: this.page, limit: this.limit,
          category_id: this.categoryId
        }, { hideError: true })
        const newList = this.list.concat(data.list || [])
        this.list = newList
        this.total = data.total
        this.page++
        this.finished = newList.length >= data.total
      } catch (e) {}
      this.loading = false
    },

    async loadCategories() {
      try {
        this.categories = await http.get(api.categoryList, {}, { hideError: true })
      } catch (e) {}
    },

    async loadStats() {
      // 在售货盘 + 入驻店铺(公开 API,所有用户都能看)
      try {
        const goodsData = await http.get(api.goodsList, { page: 1, limit: 1 }, { hideError: true })
        this.stats.goods = goodsData.total || 0
      } catch (e) {}
      try {
        const shopsData = await http.get(api.shopList, { page: 1, limit: 1 }, { hideError: true })
        this.stats.shops = shopsData.total || 0
      } catch (e) {}

      // 我的预订(必须 token 真实 + buyer)
      // token 长度 < 32 视为 fake(微信 mock 模式),不调真实 API
      const t = userStore.token || ''
      if (!t || t.length < 32 || !userStore.isBuyer) return
      try {
        const myData = await http.get(api.reservationList, { page: 1, limit: 1 }, { hideError: true })
        this.stats.myReservations = (myData.total || 0)
      } catch (e) {}
    },

    selectCat(id) {
      this.categoryId = id
      this.loadList(true)
    },

    goDetail(id) { uni.navigateTo({ url: `/pages/goods/goods?id=${id}` }) },
    goShop(id) { uni.navigateTo({ url: `/pages/shop/shop?id=${id}` }) },
    goSearch() { uni.navigateTo({ url: '/pages/search/search' }) },

    // 一键预定(规格 §7.2)— 直接在首页弹 sheet,不跳转
    quickReserve(g) {
      // 必须真 token + buyer 角色(避免 fake token 提交报 401)
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
    closeBookSheet() { this.showSheet = false },
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
      // 简单按哈希选 emoji
      const code = (name || '').split('').reduce((s, c) => s + c.charCodeAt(0), 0)
      return FOOD_EMOJI[code % FOOD_EMOJI.length]
    },

    formatTime(ts) {
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

.header {
  background: linear-gradient(135deg, #ff6b35, #ff8a5b);
  padding: 30rpx 40rpx 50rpx;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  color: #fff;
}
.title { font-size: 44rpx; font-weight: 700; }
.subtitle { font-size: 24rpx; opacity: .85; margin-top: 8rpx; }

.search-bar {
  background: #fff;
  margin: -30rpx 30rpx 20rpx;
  border-radius: 50rpx;
  padding: 18rpx 30rpx;
  display: flex;
  align-items: center;
  gap: 12rpx;
  box-shadow: 0 4rpx 16rpx rgba(0,0,0,.08);
}
.search-icon { font-size: 32rpx; }
.search-placeholder { color: #999; font-size: 28rpx; }

.stats-row {
  display: flex;
  gap: 20rpx;
  padding: 0 30rpx 20rpx;
}
.stat-card {
  flex: 1;
  background: #fff;
  border-radius: 12rpx;
  padding: 24rpx 0;
  text-align: center;
  box-shadow: 0 2rpx 8rpx rgba(0,0,0,.04);
}
.stat-num { font-size: 36rpx; font-weight: 600; color: #ff6b35; display: block; }
.stat-label { color: #999; font-size: 22rpx; margin-top: 6rpx; }

.category-scroll {
  white-space: nowrap;
  padding: 0 30rpx 20rpx;
}
.chip {
  display: inline-block;
  padding: 12rpx 28rpx;
  background: #fff;
  border-radius: 30rpx;
  font-size: 26rpx;
  color: #666;
  margin-right: 16rpx;
  box-shadow: 0 1rpx 4rpx rgba(0,0,0,.04);
}
.chip.active {
  background: #ff6b35;
  color: #fff;
}

.goods-list { padding: 0 30rpx; }
.goods-card {
  background: #fff;
  border-radius: 16rpx;
  padding: 24rpx;
  margin-bottom: 20rpx;
  display: flex;
  gap: 20rpx;
  box-shadow: 0 2rpx 12rpx rgba(0,0,0,.04);
}
.emoji-cover {
  width: 160rpx;
  height: 160rpx;
  border-radius: 12rpx;
  background: linear-gradient(135deg, #fff3e0, #ffe0b2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 80rpx;
  flex-shrink: 0;
}
.goods-info { flex: 1; display: flex; flex-direction: column; }
.goods-name { font-size: 30rpx; font-weight: 600; line-height: 1.4; }
.goods-meta { color: #999; font-size: 22rpx; margin-top: 6rpx; }
.goods-stock { color: #999; font-size: 22rpx; margin-top: 4rpx; }
.goods-bottom { display: flex; align-items: center; margin-top: 12rpx; }
.price { color: #ff6b35; font-size: 34rpx; font-weight: 700; }
.goods-actions {
  display: flex;
  gap: 12rpx;
  margin-top: 16rpx;
}
.btn-outline {
  flex: 1;
  background: #fff;
  color: #ff6b35;
  border: 2rpx solid #ff6b35;
  border-radius: 8rpx;
  padding: 12rpx 0;
  text-align: center;
  font-size: 26rpx;
}
.btn-primary {
  flex: 1.2;
  background: #ff6b35;
  color: #fff;
  border-radius: 8rpx;
  padding: 12rpx 0;
  text-align: center;
  font-size: 26rpx;
  font-weight: 500;
}

.loading, .empty {
  text-align: center;
  color: #999;
  padding: 60rpx 0;
  font-size: 26rpx;
}

/* ===== Sheet 弹层(规格 §7.2) ===== */
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
