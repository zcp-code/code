<template>
  <view class="container">
    <!-- 顶部橙色搜索区域 -->
    <view class="search-header">
      <view class="search-box">
        <text class="search-icon iconfont icon-sousuo"></text>
        <input class="search-input" placeholder="搜索店铺名称" v-model="keyword" @confirm="load(true)" />
      </view>
    </view>

    <!-- 店铺列表 -->
    <view class="shop-list">
      <view v-for="s in list" :key="s.id" class="shop-card" @tap="goDetail(s.id)">
        <image class="shop-logo" :src="s.logo || '/static/placeholder.png'" mode="aspectFill"></image>
        <view class="shop-info">
          <view class="shop-title-line">
            <text class="shop-name">{{ s.name }}</text>
            <text class="shop-badge">认证商家</text>
          </view>
          <view class="shop-address"><text class="iconfont icon-dizhi"></text> {{ s.position || '暂无地址' }}</view>
          <view class="shop-bottom-line">
            <view class="new-tag" v-if="s.today_count">今日到货 {{ s.today_count }} 种</view>
          </view>
        </view>
        <view class="phone-circle iconfont icon-dianhua" @tap.stop="handleCall(s)"></view>
      </view>

      <view v-if="loading" class="tip-text">加载中...</view>
      <view v-else-if="list.length === 0" class="tip-text">暂无店铺</view>
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
    goDetail(id) { uni.navigateTo({ url: `/pages/shop/shop?id=${id}` }) },
    // 新增电话拨打
    callPhone(shop) {
      if(!shop.contact_phone) return uni.showToast({title:"暂无联系电话",icon:"none"})
      uni.makePhoneCall({phoneNumber: shop.contact_phone})
    }
  }
}

</script>

<style scoped>
page {
  background-color: #f6f7f9;
}
.container {
  min-height: 100vh;
  padding-bottom: 120rpx;
  background-color: #f6f7f9;
}

/* 顶部橙色搜索栏 */
.search-header {
  background-color: #ff7722;
  padding: 30rpx 28rpx;
}
.search-box {
  display: flex;
  align-items: center;
  background: #ffffff;
  border-radius: 40rpx;
  padding: 16rpx 24rpx;
}
.search-icon {
  font-size: 32rpx;
  color: #ff7722;
  margin-right:12rpx;
}
.search-input {
  flex: 1;
  font-size: 28rpx;
  background: transparent;
}

/* 店铺列表容器 */
.shop-list {
  padding: 20rpx 24rpx;
}
.shop-card {
  display: flex;
  align-items: flex-start;
  background: #ffffff;
  border-radius: 24rpx;
  padding: 24rpx;
  margin-bottom: 20rpx;
}
.shop-logo {
  width:140rpx;
  height:140rpx;
  border-radius: 20rpx;
  background: #fff2e8;
  flex-shrink: 0;
}
.shop-info {
  flex: 1;
  margin-left:20rpx;
}
.shop-title-line {
  display: flex;
  align-items: center;
  gap:12rpx;
}
.shop-name {
  font-size:34rpx;
  font-weight:700;
  color:#222;
}
.shop-badge {
  background-color:#fff2e8;
  color:#ff7722;
  font-size:24rpx;
  padding:4rpx 10rpx;
  border-radius:6rpx;
}
.shop-address {
  font-size:24rpx;
  color:#666;
  margin-top:8rpx;
}
.shop-bottom-line {
  margin-top:12rpx;
}
.new-tag {
  background-color:#e8f8ef;
  color:#27ae60;
  font-size:24rpx;
  padding:6rpx 10rpx;
  border-radius:6rpx;
}
.phone-circle {
  width:72rpx;
  height:72rpx;
  border-radius: 50%;
  background:#fff2e8;
  color:#ff7722;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:32rpx;
  flex-shrink:0;
  margin-left:16rpx;
  margin-top:32rpx;
}
.tip-text {
  text-align:center;
  color:#999;
  font-size:26rpx;
  padding:80rpx 0;
}
</style>

