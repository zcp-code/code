<template>
  <view v-if="visible" class="custom-tabbar" :style="{ background: bgColor }">
    <view
      v-for="(tab, idx) in tabs"
      :key="tab.pagePath"
      class="tab-item"
      :class="{ active: currentIdx === idx }"
      @tap="switchTab(tab, idx)"
    >
      <image
        class="tab-icon"
        :src="currentIdx === idx ? tab.iconSelected : tab.iconNormal"
        mode="aspectFit"
      ></image>
      <text class="tab-text" :style="{ color: currentIdx === idx ? selectedColor : color }">{{ tab.text }}</text>
    </view>
  </view>
</template>

<script>
import userStore from '@/store/user.js'

export default {
  data() {
    return {
      color: '#999999',
      selectedColor: '#ff6b35',
      bgColor: '#ffffff'
    }
  },
  computed: {
    visible() {
      // 规格 §4.2:入口页/未登录态不显示 tabBar
      // 必须同时满足:有 token + 有 role(M2 之前 mock token 长度可能不够,所以只判断非空)
      const t = userStore.token
      const r = userStore.role
      return !!t && t.length >= 4 && !!r
    },
    tabs() {
      if (userStore.isWholesaler) {
        // 批发商 tabBar:工作台/货盘/预订/我的
        return [
          { pagePath: 'pages/wholesaler/dashboard/dashboard', text: '工作台', iconNormal: '/static/tabbar/home_normal.png', iconSelected: '/static/tabbar/home_a.png' },
          { pagePath: 'pages/wholesaler/goods',                text: '货盘',   iconNormal: '/static/tabbar/shops_normal.png', iconSelected: '/static/tabbar/shops_a.png' },
          { pagePath: 'pages/wholesaler/reservations',         text: '预订',   iconNormal: '/static/tabbar/favorites_normal.png', iconSelected: '/static/tabbar/favorites_a.png' },
          { pagePath: 'pages/wholesaler/mine',                 text: '我的',   iconNormal: '/static/tabbar/profile_normal.png', iconSelected: '/static/tabbar/profile_a.png' }
        ]
      }
      // 采购商 tabBar:首页/店铺/订单/我的
      return [
        { pagePath: 'pages/index/index',    text: '首页', iconNormal: '/static/tabbar/home_normal.png',      iconSelected: '/static/tabbar/home_a.png' },
        { pagePath: 'pages/shops/shops',    text: '店铺', iconNormal: '/static/tabbar/shops_normal.png',     iconSelected: '/static/tabbar/shops_a.png' },
        { pagePath: 'pages/orders/orders',  text: '订单', iconNormal: '/static/tabbar/favorites_normal.png', iconSelected: '/static/tabbar/favorites_a.png' },
        { pagePath: 'pages/profile/profile',text: '我的', iconNormal: '/static/tabbar/profile_normal.png',   iconSelected: '/static/tabbar/profile_a.png' }
      ]
    },
    currentIdx() {
      const pages = getCurrentPages()
      if (!pages.length) return 0
      const cur = pages[pages.length - 1].route || ''
      // 精确匹配 pagePath(老的 startsWith(last segment) 永远 false 因为 cur 以 pages/ 开头)
      return Math.max(0, this.tabs.findIndex(t => cur === t.pagePath || cur === '/' + t.pagePath))
    }
  },
  methods: {
    switchTab(tab, idx) {
      if (idx === this.currentIdx) return
      // 用 reLaunch 清栈切换,避免 redirectTo 破坏 wx pageStack
      uni.reLaunch({ url: '/' + tab.pagePath })
    }
  }
}
</script>

<style scoped>
.custom-tabbar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 100rpx;
  display: flex;
  border-top: 1rpx solid #eee;
  box-shadow: 0 -2rpx 8rpx rgba(0,0,0,.04);
  z-index: 999;
  padding-bottom: env(safe-area-inset-bottom);
}
.tab-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 8rpx 0;
}
.tab-icon {
  width: 48rpx;
  height: 48rpx;
}
.tab-text {
  font-size: 22rpx;
  margin-top: 4rpx;
}
.tab-item.active .tab-text {
  font-weight: 500;
}
</style>
