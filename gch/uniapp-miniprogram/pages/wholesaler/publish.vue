<template>
  <view class="container">
    <view class="form-card">
      <view class="form-row">
        <text class="label">分类</text>
        <picker :range="categories" range-key="name" :value="categoryIndex" @change="onCategoryChange">
          <view class="picker">{{ categories[categoryIndex] ? categories[categoryIndex].name : '请选择' }}</view>
        </picker>
      </view>
      <view class="form-row">
        <text class="label">品名</text>
        <input class="input" v-model="form.name" placeholder="如：烟台红富士苹果" />
      </view>
      <view class="form-row">
        <text class="label">单价(元)</text>
        <input class="input" type="digit" v-model="form.price" placeholder="0.00" />
      </view>
      <view class="form-row">
        <text class="label">单位</text>
        <picker :range="units" :value="unitIndex" @change="onUnitChange">
          <view class="picker">{{ unitIndex >= 0 ? units[unitIndex] : '请选择' }}</view>
        </picker>
      </view>
      <view class="form-row">
        <text class="label">库存</text>
        <input class="input" type="number" v-model="form.total_stock" placeholder="≥1" />
      </view>
      <view class="form-row column">
        <text class="label">图片(选填)</text>
        <view class="image-list">
          <view v-for="(img, idx) in images" :key="idx" class="image-item">
            <image :src="img" mode="aspectFill" class="image"></image>
            <view class="del" @tap="removeImage(idx)">×</view>
          </view>
          <view class="image-item add" @tap="chooseImage">+</view>
        </view>
      </view>
    </view>

    <button class="btn-primary submit" :disabled="submitting" @tap="submit">
      {{ submitting ? '提交中...' : '发布货盘' }}
    </button>
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'

// 单位选项（picker 单选）
const UNITS = ['斤', '公斤', '箱', '袋', '盒', '件', '个', '只', '把', '捆']

export default {
  data() {
    return {
      categories: [],
      categoryIndex: -1,
      units: UNITS,
      unitIndex: -1,
      form: {
        category_id: 0,
        name: '',
        price: '',
        unit: '',
        total_stock: ''
      },
      images: [],
      submitting: false
    }
  },
  onLoad() { this.loadCategories() },
  methods: {
    async loadCategories() {
      try {
        this.categories = await http.get(api.wholesalerCategories, {}, { hideError: true })
      } catch (e) { uni.showToast({ title: e.message || "操作失败", icon: "none" }) }
    },
    onCategoryChange(e) {
      this.categoryIndex = e.detail.value
      this.form.category_id = this.categories[this.categoryIndex].id
    },
    onUnitChange(e) {
      this.unitIndex = e.detail.value
      this.form.unit = UNITS[this.unitIndex]
    },
    chooseImage() {
      uni.chooseImage({
        count: 9 - this.images.length,
        sizeType: ['compressed'],   // 压缩上传,减少体积
        success: async ({ tempFilePaths }) => {
          for (const p of tempFilePaths) {
            try {
              // 微信开发者工具已知 bug:uni.uploadFile 会触发
              //   reportRealtimeAction:fail not support,导致前端拿不到响应。
              // 直接用 wx.uploadFile (Promise 包装) 可绕过 uni-app 的运行时上报。
              const up = await new Promise((resolve, reject) => {
                wx.uploadFile({
                  url: getApp().globalData.apiBase + api.commonUpload,
                  filePath: p,
                  name: 'file',
                  header: { 'Content-Type': 'multipart/form-data' },
                  success: resolve,
                  fail: reject
                })
              })
              // wx.uploadFile 的 data 始终是字符串,需手动 parse
              let body
              try { body = JSON.parse(up.data) } catch (e) { body = up.data }
              if (body && body.code === 200 && body.data && body.data.url) {
                this.images.push(body.data.url)
                uni.showToast({ title: '上传成功', icon: 'success' })
              } else {
                uni.showToast({ title: (body && body.msg) || '上传失败', icon: 'none' })
              }
            } catch (e) {
              const msg = e && (e.errMsg || e.message) || '上传失败'
              uni.showToast({ title: msg, icon: 'none', duration: 3000 })
              console.error('[upload] failed:', e)
            }
          }
        }
      })
    },
    removeImage(i) { this.images.splice(i, 1) },
    async submit() {
      if (this.submitting) return
      const f = this.form
      if (!f.category_id || !f.name || !f.price || !f.unit || !f.total_stock) {
        return uni.showToast({ title: '请填写必填项', icon: 'none' })
      }
      if (Number(f.total_stock) < 1) {
        return uni.showToast({ title: '库存必须 ≥ 1', icon: 'none' })
      }
      this.submitting = true
      try {
        // 后端 Common::upload 现在返完整 URL,Wholesaler::create 只取 path 字段
        const relativeImages = this.images.map(u => {
          const m = u.match(/\/uploads\/.+/)
          return m ? m[0] : u
        })
        await http.post(api.wholesalerGoodsCreate, {
          category_id: f.category_id,
          name: f.name,
          price: Number(f.price),
          unit: f.unit,
          total_stock: Number(f.total_stock),
          images: relativeImages
        }, { hideError: true })
        uni.showModal({
          title: '发布成功',
          content: '货盘已上架',
          showCancel: false,
          success: () => uni.navigateBack()
        })
      } catch (e) { uni.showToast({ title: e.message || "操作失败", icon: "none" }) }
      this.submitting = false
    }
  }
}
</script>

<style scoped>
.form-card { background: #fff; border-radius: 12rpx; padding: 20rpx; }
.form-row { display: flex; align-items: center; padding: 24rpx 0; border-bottom: 1rpx solid #eee; }
.form-row.column { flex-direction: column; align-items: stretch; }
.form-row .label { width: 160rpx; color: #666; font-size: 28rpx; }
.form-row .input, .form-row .picker, .form-row .textarea { flex: 1; font-size: 28rpx; }
.form-row .picker { padding: 8rpx 0; }
.form-row .textarea { width: 100%; padding: 12rpx 0; box-sizing: border-box; }
.image-list { display: flex; flex-wrap: wrap; gap: 16rpx; margin-top: 12rpx; }
.image-item { position: relative; width: 160rpx; height: 160rpx; border-radius: 8rpx; overflow: hidden; background: #f5f5f5; }
.image-item .image { width: 100%; height: 100%; }
.image-item .del { position: absolute; top: 0; right: 0; background: rgba(0,0,0,.5); color: #fff; width: 40rpx; height: 40rpx; line-height: 40rpx; text-align: center; font-size: 32rpx; }
.image-item.add { display: flex; align-items: center; justify-content: center; font-size: 60rpx; color: #999; border: 2rpx dashed #ddd; box-sizing: border-box; }
.submit { margin-top: 40rpx; margin-bottom: 60rpx; }   /* 底部留白避免被遮挡 */
</style>
