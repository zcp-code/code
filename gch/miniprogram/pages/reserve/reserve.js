// pages/reserve/reserve.js
const { http, api } = require('../../utils/request.js');

Page({
  data: {
    id: 0,
    goods: null,
    quantity: 1
  },

  onLoad(query) {
    this.setData({ id: query.id });
    this.load();
  },

  async load() {
    try {
      const goods = await http.get(api.goodsDetail(this.data.id), {}, { hideError: true });
      this.setData({ goods, quantity: Math.min(1, goods.available || 1) });
    } catch (e) {}
  },

  // 数量 -/+
  decQty() {
    if (this.data.quantity > 1) this.setData({ quantity: this.data.quantity - 1 });
  },
  incQty() {
    const max = this.data.goods ? (this.data.goods.available || 99) : 99;
    if (this.data.quantity < max) this.setData({ quantity: this.data.quantity + 1 });
  },

  // 提交预订
  async submit() {
    if (!this.data.goods) return;
    try {
      const order = await http.post(api.reservationCreate, {
        goods_id: this.data.id,
        quantity: this.data.quantity
      }, { hideError: true });

      wx.showModal({
        title: '预订成功',
        content: `订单号: ${order.reservation_no}\n等待批发商确认`,
        showCancel: false,
        success: () => {
          wx.switchTab({ url: '/pages/profile/profile' });
        }
      });
    } catch (e) {}
  }
});
