<template>
  <view class="container">
    <view v-if="qrcodeUrl" class="qr-card">
      <view class="title">店铺二维码</view>
      <view class="qr-wrap">
        <image :src="qrcodeUrl" mode="aspectFit" class="qr-img"></image>
      </view>
      <view class="desc">采购商/客户扫码直接进入「{{shopName}}」</view>
      <view class="shop-name">{{shopName}}</view>
    </view>
    <view v-else class="loading">加载中...</view>

    <button v-if="qrcodeUrl" class="btn-primary" @tap="saveImage">保存到相册</button>
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'

export default {
  data() { return { qrcodeUrl: '', shopName: '' } },
  onShow() { this.load() },
  methods: {
    async load() {
      try {
        const data = await http.get(api.wholesalerQrcode, {}, { hideError: true })
        this.qrcodeUrl = data.qrcode_url
        this.shopName = data.shop_name || ''
      } catch (e) {
        this.shopName = '请在 PC 后台配置店铺'
      }
    },
    saveImage() {
      uni.downloadFile({
        url: this.qrcodeUrl,
        success: ({ tempFilePath }) => {
          uni.saveImageToPhotosAlbum({
            filePath: tempFilePath,
            success: () => uni.showToast({ title: '已保存', icon: 'success' }),
            fail: () => uni.showToast({ title: '保存失败,请授权相册权限', icon: 'none' })
          })
        }
      })
    }
  }
}
</script>

<style scoped>
.qr-card { background: #fff; border-radius: 16rpx; padding: 60rpx 40rpx; text-align: center; }
.title { font-size: 36rpx; font-weight: 600; color: #333; margin-bottom: 40rpx; }
.qr-wrap { width: 480rpx; height: 480rpx; margin: 0 auto; padding: 20rpx; border: 2rpx solid #eee; border-radius: 12rpx; }
.qr-img { width: 100%; height: 100%; }
.desc { color: #999; font-size: 26rpx; margin-top: 30rpx; }
.shop-name { font-size: 32rpx; font-weight: 500; color: #ff6b35; margin-top: 16rpx; }
.btn-primary { margin-top: 40rpx; }
.loading { text-align: center; color: #999; padding: 100rpx 0; }
</style>
