# 仓货盘小程序 - 客户端 demo

微信小程序前端，对接 `F:\2026\GCH\code\gch` 的 GCH 后端 API。

## 📁 项目结构

```
miniprogram/
├── app.js                    # 全局应用入口（token 管理、HTTP 实例）
├── app.json                  # 页面路由 + tabBar 配置
├── app.wxss                  # 全局样式
├── project.config.json       # 微信开发者工具项目配置
├── sitemap.json              # 索引规则
├── utils/
│   ├── auth.js               # 登录态本地缓存
│   ├── api.js                # API 路径常量（按业务域分组）
│   └── request.js            # HTTP 封装（自动 Token 注入、统一响应处理、401 自动跳登录）
└── pages/
    ├── login/                # 登录页（微信游客 + 采购商账号密码）
    ├── index/                # 首页（货盘列表 + 搜索 + 下拉刷新 + 上拉加载）
    ├── category/             # 分类页（网格 + 跳转首页筛选）
    ├── goods/                # 货盘详情（轮播 + 收藏 + 拨号 + 一键预订）
    ├── shop/                 # 店铺详情（联系 + 收藏）
    ├── reserve/              # 预订页（数量选择 + 提交订单）
    ├── favorites/            # 我的收藏（货品 + 店铺 tab 切换）
    └── profile/              # 个人中心（用户信息 + 我的预订 + 退出登录）
```

## 🚀 快速开始

### 1. 准备

- 微信开发者工具（最新版）
- 一台可访问后端的设备（手机/模拟器）

### 2. 配置后端地址

修改 `app.js`：

```js
globalData: {
  apiBase: 'http://www.gch.local:1992',        // PC 调试
  apiBaseDev: 'http://127.0.0.1:1992',          // 真机调试（手机和电脑同局域网时填电脑 IP）
}
```

### 3. 配置业务域名

登录 [微信公众平台](https://mp.weixin.qq.com) → 开发 → 开发管理 → 服务器域名 → 配置：

- request 合法域名：`www.gch.local`（或你的生产域名）
- uploadFile 合法域名：同上

> 开发期可在微信开发者工具勾选「不校验合法域名」临时绕过。

### 4. 导入项目

1. 打开微信开发者工具
2. 导入项目 → 选择 `miniprogram/` 目录
3. AppID 选择「测试号」或填你自己的小程序 AppID
4. 点击「编译」即可运行

### 5. 真机调试时

修改 `miniprogram/utils/request.js` 的 `buildBaseUrl()` 让手机能访问到电脑：

```js
return app.globalData.apiBaseDev.replace('127.0.0.1', '你的电脑内网 IP');
```

## 🔑 登录流程

### 游客（wx.login）
```
小程序 → wx.login() 拿 code
       → POST /api/wxlogin { code, nickname, avatar }
       → 后端 code2Session 拿 openid，返回 { token, role:'visitor', visitor }
       → wx.setStorage 存 token,后续请求自动加 Token header
```

### 采购商
```
小程序 → POST /api/buyer/login { account, password }
       → 后端 bcrypt 校验,返回 { token, role:'buyer', buyer }
```

## 📦 关键 API 调用示例

```js
const { http, api } = require('../../utils/request.js');

// 货盘列表
const data = await http.get(api.goodsList, { page: 1, limit: 20 });

// 货盘详情
const goods = await http.get(api.goodsDetail(123));

// 创建预订
const order = await http.post(api.reservationCreate, {
  goods_id: 123,
  quantity: 5
});

// 收藏货品
await http.post(api.favoriteGoods, { goods_id: 123 });

// 拨打电话
const r = await http.post(api.callDial, { shop_id: 5 });
wx.makePhoneCall({ phoneNumber: r.phone });
```

## 🔐 鉴权机制

所有需要登录的接口在 header 里带 `Token`：

```
Token: abcdef123456...
```

后端 `ApiBase::_initialize()` 会自动校验。失败抛 401，前端 `request.js` 自动清登录态 + 跳登录页。

## ⚠️ 已知限制

- 头像上传当前用 URL 输入框（demo 简化）。生产环境应接 `wx.chooseMedia` + `/api/common/upload`。
- 没有做采购商/批发商的注册流程（账号需 PC 后台预创建）。
- tabBar 图标需替换为真实 PNG（`images/` 下当前无图，会显示空白）。

## 🎨 tabBar 图标

`images/` 目录下需要准备 4 组 81x81 PNG：
- `home.png` / `home_a.png`（首页）
- `category.png` / `category_a.png`（分类）
- `fav.png` / `fav_a.png`（收藏）
- `me.png` / `me_a.png`（我的）

也可暂时删掉 `app.json` 里的 iconPath/selectedIconPath 字段，不影响功能。

## 📞 后端 API 路由表（已对接）

| URL | Method | 用途 |
|---|---|---|
| `/api/wxlogin` | POST | 微信游客登录 |
| `/api/categories` | GET | 货品分类列表 |
| `/api/goods` | GET | 货盘列表 |
| `/api/goods/:id` | GET | 货盘详情 |
| `/api/shops` | GET | 店铺列表 |
| `/api/shop/:id` | GET | 店铺详情 |
| `/api/buyer/login` | POST | 采购商登录 |
| `/api/reservation` | POST | 创建预订 |
| `/api/reservations` | GET | 我的预订 |
| `/api/reservation/cancel` | POST | 取消预订 |
| `/api/favorite/goods` | POST | 收藏/取消货品 |
| `/api/favorite/shop` | POST | 收藏/取消店铺 |
| `/api/favorites` | GET | 我的收藏列表 |
| `/api/call/dial` | POST | 拨号记录 |
| `/api/visitor/logout` | POST | 游客登出 |
| `/api/buyer/logout` | POST | 采购商登出 |
