<template>
  <view class="container">

    <!-- 顶部标题 -->
    <view class="hero">
      <view class="hero-title">📦 发布新货盘</view>
      <view class="hero-sub">填写后即刻上架,买家可在首页看到</view>
    </view>

    <!-- 分组1：基础信息 -->
    <view class="card">
      <view class="card-title">
        <text class="card-num">1</text>
        <text class="card-title-text">选择分类</text>
      </view>
      <view class="picker-row" :class="{ filled: form.category_id }" @tap="showCatPicker = true">
        <text class="picker-label">分类</text>
        <view class="picker-value">
          <text v-if="form.category_id">{{ categoryName }}</text>
          <text v-else class="placeholder">请选择商品分类</text>
          <text class="picker-arrow">›</text>
        </view>
      </view>
    </view>

    <!-- 分组2：货品信息 -->
    <view class="card">
      <view class="card-title">
        <text class="card-num">2</text>
        <text class="card-title-text">货品信息</text>
      </view>

      <view class="input-row">
        <text class="input-label"><text class="required">*</text> 品名</text>
        <input class="input" v-model="form.name" placeholder="如:烟台红富士苹果" maxlength="40" />
      </view>

      <view class="input-row">
        <text class="input-label"><text class="required">*</text> 单价</text>
        <view class="price-input-wrap">
          <text class="price-symbol">¥</text>
          <input class="input price-input" type="digit" v-model="form.price" placeholder="0.00" />
          <text class="price-unit">/{{ form.unit || '?' }}</text>
        </view>
      </view>

      <view class="input-row" @tap="showUnitPicker = true">
        <text class="input-label"><text class="required">*</text> 单位</text>
        <view class="picker-value">
          <text v-if="form.unit">{{ form.unit }}</text>
          <text v-else class="placeholder">选择单位</text>
          <text class="picker-arrow">›</text>
        </view>
      </view>

      <view class="input-row">
        <text class="input-label"><text class="required">*</text> 库存</text>
        <input class="input" type="number" v-model="form.total_stock" placeholder="本次可预订数量" />
      </view>
    </view>

    <!-- 分组3：图片(选填) -->
    <view class="card">
      <view class="card-title">
        <text class="card-num">3</text>
        <text class="card-title-text">商品图片</text>
        <text class="card-tip">最多 9 张,买家更爱看</text>
      </view>

      <view class="image-list">
        <view v-for="(img, idx) in images" :key="idx" class="image-item">
          <image :src="img" mode="aspectFill" class="image"></image>
          <view class="del" @tap="removeImage(idx)">×</view>
        </view>
        <view v-if="images.length < 9" class="image-item add" @tap="chooseImage">
          <text class="add-icon">+</text>
          <text class="add-text">{{ images.length === 0 ? '添加图片' : '继续添加' }}</text>
        </view>
      </view>
    </view>

    <!-- 预览卡(上架后样子) -->
    <view v-if="canPreview" class="preview-card">
      <view class="preview-title">📱 上架预览</view>
      <view class="preview-body">
        <view class="preview-tag">¥{{ formatPrice(form.price) }}/{{ form.unit || '?' }}</view>
        <view class="preview-name">{{ form.name || '品名' }}</view>
        <view class="preview-meta">库存 {{ form.total_stock || 0 }} {{ form.unit || '' }}</view>
      </view>
    </view>

    <!-- 提交按钮 -->
    <view class="submit-bar">
      <view class="submit-tip" v-if="!isValid">⚠ 请检查必填项</view>
      <button class="btn-solid" :disabled="submitting || !isValid" @tap="submit">
        {{ submitting ? '提交中...' : '立即上架' }}
      </button>
    </view>

    <!-- 分类选择弹层 -->
    <view v-if="showCatPicker" class="modal-mask" @tap="showCatPicker = false">
      <view class="modal" @tap.stop>
        <view class="modal-title">选择分类</view>
        <scroll-view scroll-y class="modal-body">
          <view
            v-for="c in categories"
            :key="c.id"
            class="modal-item"
            :class="{ active: form.category_id === c.id }"
            @tap="selectCat(c)"
          >
            <text class="modal-item-text">{{ c.name }}</text>
            <text v-if="form.category_id === c.id" class="modal-item-check">✓</text>
          </view>
          <view v-if="categories.length === 0" class="modal-empty">暂无分类,请联系管理员</view>
        </scroll-view>
        <view class="modal-cancel" @tap="showCatPicker = false">取消</view>
      </view>
    </view>

    <!-- 单位选择弹层 -->
    <view v-if="showUnitPicker" class="modal-mask" @tap="showUnitPicker = false">
      <view class="modal" @tap.stop>
        <view class="modal-title">选择单位</view>
        <view class="modal-body">
          <view
            v-for="u in units"
            :key="u"
            class="modal-item"
            :class="{ active: form.unit === u }"
            @tap="selectUnit(u)"
          >
            <text class="modal-item-text">{{ u }}</text>
            <text v-if="form.unit === u" class="modal-item-check">✓</text>
          </view>
        </view>
        <view class="modal-cancel" @tap="showUnitPicker = false">取消</view>
      </view>
    </view>
  </view>
</template>

<script>
import http, { api } from '@/utils/request.js'

const UNITS = ['斤', '公斤', '箱', '袋', '盒', '件', '个', '只', '把', '捆']

export default {
  data() {
    return {
      categories: [],
      units: UNITS,
      form: {
        category_id: 0,
        name: '',
        price: '',
        unit: '',
        total_stock: ''
      },
      images: [],
      submitting: false,
      showCatPicker: false,
      showUnitPicker: false
    }
  },
  onLoad() { this.loadCategories() },
  computed: {
    categoryName() {
      const c = this.categories.find(x => x.id === this.form.category_id)
      return c ? c.name : ''
    },
    isValid() {
      const f = this.form
      return !!(f.category_id && f.name && f.price && f.unit && Number(f.total_stock) >= 1)
    },
    canPreview() {
      return this.form.name && this.form.price
    }
  },
  methods: {
    async loadCategories() {
      try {
        this.categories = await http.get(api.wholesalerCategories, {}, { hideError: true })
      } catch (e) { uni.showToast({ title: e.message || '操作失败', icon: 'none' }) }
    },
    selectCat(c) {
      this.form.category_id = c.id
      this.showCatPicker = false
    },
    selectUnit(u) {
      this.form.unit = u
      this.showUnitPicker = false
    },
    async chooseImage() {
      try {
        const { tempFilePaths } = await uni.chooseImage({
          count: 9 - this.images.length,
          sizeType: ['compressed']
        })
        for (const p of tempFilePaths) {
          try {
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
            let body
            try { body = JSON.parse(up.data) } catch (e) { body = up.data }
            if (body && body.code === 200 && body.data && body.data.url) {
              this.images.push(body.data.url)
            } else {
              uni.showToast({ title: (body && body.msg) || '上传失败', icon: 'none' })
            }
          } catch (e) {
            const msg = (e && (e.errMsg || e.message)) || '上传失败'
            uni.showToast({ title: msg, icon: 'none' })
          }
        }
        if (this.images.length > 0) {
          uni.showToast({ title: `已上传 ${this.images.length} 张`, icon: 'success' })
        }
      } catch (e) {
        // 用户取消选择,不提示
      }
    },
    removeImage(i) {
      this.images.splice(i, 1)
    },
    async submit() {
      if (this.submitting || !this.isValid) return
      this.submitting = true
      try {
        const relativeImages = this.images.map(u => {
          const m = u.match(/\/uploads\/.+/)
          return m ? m[0] : u
        })
        await http.post(api.wholesalerGoodsCreate, {
          category_id: this.form.category_id,
          name: this.form.name,
          price: Number(this.form.price),
          unit: this.form.unit,
          total_stock: Number(this.form.total_stock),
          images: relativeImages
        }, { hideError: true })
        uni.showModal({
          title: '发布成功 🎉',
          content: '货盘已上架,买家可在首页看到',
          showCancel: false,
          success: () => uni.navigateBack()
        })
      } catch (e) { uni.showToast({ title: e.message || '操作失败', icon: 'none' }) }
      this.submitting = false
    },
    formatPrice(p) {
      const n = parseFloat(p)
      return isNaN(n) ? '0.00' : n.toFixed(2)
    }
  }
}
</script>

<style scoped>
.container { min-height: 100vh; background: #f5f7fa; padding-bottom: 220rpx; }

/* ===== Hero ===== */
.hero {
  background: linear-gradient(135deg, #ff6600, #ff8c42);
  padding: 40rpx 32rpx 60rpx;
  color: #fff;
}
.hero-title { font-size: 40rpx; font-weight: 700; }
.hero-sub { font-size: 24rpx; opacity: 0.9; margin-top: 8rpx; }

/* ===== Cards ===== */
.card {
  background: #fff;
  margin: 24rpx 24rpx 0;
  border-radius: 20rpx;
  padding: 8rpx 28rpx 28rpx;
  box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.04);
}

.card-title {
  display: flex;
  align-items: center;
  padding: 28rpx 0 16rpx;
  border-bottom: 1rpx solid #f3f4f6;
  margin-bottom: 8rpx;
}
.card-num {
  width: 36rpx;
  height: 36rpx;
  background: linear-gradient(135deg, #ff6600, #ff8c42);
  color: #fff;
  border-radius: 50%;
  font-size: 22rpx;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12rpx;
  flex-shrink: 0;
}
.card-title-text { font-size: 30rpx; font-weight: 700; color: #1f2937; flex: 1; }
.card-tip { font-size: 22rpx; color: #9ca3af; }

/* ===== Form Rows ===== */
.input-row {
  display: flex;
  align-items: center;
  padding: 28rpx 0;
  border-bottom: 1rpx solid #f3f4f6;
}
.input-row:last-child { border-bottom: none; }
.input-label {
  width: 160rpx;
  font-size: 28rpx;
  color: #4b5563;
  flex-shrink: 0;
}
.required {
  color: #ef4444;
  margin-right: 4rpx;
  font-weight: 700;
}
.input {
  flex: 1;
  font-size: 28rpx;
  color: #1f2937;
  text-align: right;
}

.price-input-wrap {
  flex: 1;
  display: flex;
  align-items: baseline;
  justify-content: flex-end;
}
.price-symbol { font-size: 28rpx; color: #ff6600; font-weight: 700; margin-right: 8rpx; }
.price-input { text-align: right; flex: none; min-width: 120rpx; }
.price-unit { font-size: 24rpx; color: #9ca3af; margin-left: 8rpx; }

/* ===== Picker Row ===== */
.picker-row {
  display: flex;
  align-items: center;
  padding: 28rpx 0;
  border-bottom: 1rpx solid #f3f4f6;
}
.picker-row:last-child { border-bottom: none; }
.picker-label {
  width: 160rpx;
  font-size: 28rpx;
  color: #4b5563;
  flex-shrink: 0;
}
.picker-value {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  font-size: 28rpx;
  color: #1f2937;
  font-weight: 600;
}
.picker-value.filled { color: #ff6600; }
.picker-value .placeholder { color: #cbd5e1; font-weight: 400; }
.picker-arrow { font-size: 32rpx; color: #cbd5e1; margin-left: 8rpx; }

/* ===== Image List ===== */
.image-list {
  display: flex;
  flex-wrap: wrap;
  gap: 16rpx;
  padding: 16rpx 0;
}
.image-item {
  position: relative;
  width: 180rpx;
  height: 180rpx;
  border-radius: 12rpx;
  overflow: hidden;
  background: #f3f4f6;
}
.image-item .image { width: 100%; height: 100%; }
.image-item .del {
  position: absolute;
  top: 6rpx;
  right: 6rpx;
  width: 44rpx;
  height: 44rpx;
  line-height: 40rpx;
  text-align: center;
  background: rgba(0, 0, 0, 0.55);
  color: #fff;
  font-size: 32rpx;
  border-radius: 50%;
}
.image-item.add {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  font-size: 60rpx;
  color: #9ca3af;
  border: 2rpx dashed #d1d5db;
  box-sizing: border-box;
  background: #f9fafb;
}
.image-item.add .add-icon { font-size: 56rpx; color: #cbd5e1; line-height: 1; }
.image-item.add .add-text { font-size: 22rpx; color: #9ca3af; margin-top: 4rpx; }

/* ===== Preview ===== */
.preview-card {
  margin: 24rpx;
  padding: 28rpx;
  background: linear-gradient(135deg, #fef3c7, #fde68a);
  border-radius: 20rpx;
  border: 2rpx dashed #f59e0b;
}
.preview-title { font-size: 24rpx; color: #92400e; font-weight: 600; margin-bottom: 12rpx; }
.preview-body { display: flex; flex-direction: column; gap: 8rpx; }
.preview-tag {
  font-size: 32rpx;
  color: #ff6600;
  font-weight: 700;
  align-self: flex-start;
}
.preview-name { font-size: 28rpx; color: #1f2937; font-weight: 600; }
.preview-meta { font-size: 24rpx; color: #6b7280; }

/* ===== Submit Bar ===== */
.submit-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 20rpx 32rpx calc(20rpx + env(safe-area-inset-bottom));
  background: rgba(255, 255, 255, 0.96);
  backdrop-filter: blur(20rpx);
  box-shadow: 0 -4rpx 24rpx rgba(0, 0, 0, 0.06);
  z-index: 100;
}
.submit-tip { font-size: 24rpx; color: #ef4444; text-align: center; margin-bottom: 12rpx; }
.btn-solid {
  width: 100%;
  background: linear-gradient(135deg, #ff6600, #ff8c42);
  color: #fff;
  border-radius: 48rpx;
  padding: 26rpx 0;
  font-size: 32rpx;
  font-weight: 700;
  box-shadow: 0 6rpx 20rpx rgba(255, 102, 0, 0.3);
}
.btn-solid[disabled] {
  background: #d1d5db;
  box-shadow: none;
  color: #9ca3af;
}

/* ===== Modal ===== */
.modal-mask {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
}
.modal {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: #fff;
  border-top-left-radius: 24rpx;
  border-top-right-radius: 24rpx;
  z-index: 1000;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
}
.modal-title {
  font-size: 30rpx;
  font-weight: 700;
  color: #1f2937;
  text-align: center;
  padding: 28rpx 0;
  border-bottom: 1rpx solid #f3f4f6;
}
.modal-body {
  max-height: 800rpx;
  padding: 16rpx 0;
}
.modal-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 24rpx 32rpx;
  border-bottom: 1rpx solid #f9fafb;
}
.modal-item.active { background: #fff1e6; }
.modal-item-text { font-size: 30rpx; color: #1f2937; }
.modal-item.active .modal-item-text { color: #ff6600; font-weight: 700; }
.modal-item-check { font-size: 32rpx; color: #ff6600; font-weight: 700; }
.modal-empty {
  text-align: center;
  color: #9ca3af;
  font-size: 26rpx;
  padding: 80rpx 0;
}
.modal-cancel {
  text-align: center;
  padding: 28rpx 0;
  font-size: 30rpx;
  color: #6b7280;
  border-top: 1rpx solid #f3f4f6;
}
</style>
