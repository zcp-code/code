# 仓货盘小程序 - 完整开发指南

> uniapp 多端客户端,对接 GCH 后端 API。编译后可同时发布微信小程序、H5、iOS/Android App。

---

## 📑 目录

1. [项目概览](#1-项目概览)
2. [技术栈](#2-技术栈)
3. [角色与权限模型](#3-角色与权限模型)
4. [目录结构](#4-目录结构)
5. [状态管理(Pinia 风格)](#5-状态管理pinia-风格)
6. [API 路由总览](#6-api-路由总览)
7. [页面清单](#7-页面清单)
8. [启动流程](#8-启动流程)
9. [开发指南](#9-开发指南)
10. [构建与发布](#10-构建与发布)
11. [故障排查](#11-故障排查)

---

## 1. 项目概览

仓货盘小程序对接 GCH 后端,提供 4 类角色的完整功能:

| 角色 | 入口 | 核心能力 |
|---|---|---|
| **游客**(微信授权) | 默认 | 浏览货盘/店铺/分类 |
| **采购商**(账号密码) | 起始页选「我是采购商」 | 浏览 + 下单 + 收藏 + 改密 |
| **批发商**(账号密码) | 起始页选「我是供应商」 | 发布货盘 + 处理预订 + 店铺二维码 |
| **管理员**(PC 后台) | 浏览器 `http://127.0.0.1:1992/admin/` | 全部管理功能 |

---

## 2. 技术栈

| 项 | 选型 |
|---|---|
| 框架 | uniapp(Vue 2 单文件组件) |
| 编译目标 | 微信小程序 / H5 / iOS / Android |
| 后端 | FastAdmin(ThinkPHP 5.1)+ MySQL + Redis |
| 状态管理 | Pinia 风格轻量 store(基于 Vue.observable,零依赖) |
| HTTP | `uni.request` + Promise 封装 |
| 图标 | 81×81 PNG(tabBar)+ emoji(页面内) |
| 后端地址 | `http://127.0.0.1:1992`(开发)/ 生产域名(部署) |

---

## 3. 角色与权限模型

### 角色定义

```js
// store/user.js
role: 'visitor' | 'buyer' | 'wholesaler' | ''
```

### 权限矩阵

| 操作 | 游客 | 采购商 | 批发商 |
|---|---|---|---|
| 浏览货盘/店铺/分类 | ✅ | ✅ | ✅ |
| 收藏货品/店铺 | ❌ | ✅ | ❌ |
| 一键预订 | ❌ | ✅ | ❌ |
| 发布货盘 | ❌ | ❌ | ✅ |
| 处理预订 | ❌ | ❌ | ✅ |
| 修改密码 | ❌ | ✅ | ✅ |
| 店铺二维码 | ❌ | ❌ | ✅ |

### 后端 Token 校验

```
HTTP Header: Token: <token>
```

后端 `ApiBase::_initialize()` 校验:
- 有 `token` → 鉴权通过
- 无 `token` → 401 → 前端跳登录

角色校验在后端 `requireRole = 'buyer'`(采购商预订)、`'wholesaler'`(批发商端)分别控制。

---

## 4. 目录结构

```
uniapp-miniprogram/
├── App.vue                          # 应用入口 + 启动路由分发
├── main.js                          # vue 启动器
├── manifest.json                    # uniapp 应用配置
├── pages.json                       # 页面路由 + tabBar 配置
├── package.json                     # npm 依赖(vue-cli 模式)
├── vue.config.js                    # vue-cli 配置
│
├── components/
│   └── custom-tabbar/               # 自定义 tabBar(批发商登录时自动隐藏)
│       └── custom-tabbar.vue
│
├── store/                           # Pinia 风格状态管理
│   └── user.js                      # userStore:token / role / profile / getters / actions
│
├── utils/                           # 工具
│   ├── api.js                       # API 路径常量
│   ├── auth.js                      # 兼容旧 auth 工具(逐步替换为 userStore)
│   └── request.js                   # HTTP 封装(自动 Token + 401 跳登录)
│
├── static/
│   └── tabbar/                      # tabBar 图标(8 个 PNG,PHP GD 生成)
│       ├── home_normal.png   home_a.png
│       ├── shops_normal.png  shops_a.png
│       ├── favorites_normal.png  favorites_a.png
│       └── profile_normal.png  profile_a.png
│
└── pages/                           # 17 个页面
    ├── start/start.vue              # ★ 起始页(3 入口)
    │
    ├── index/index.vue              # 首页(货盘列表)
    ├── category/category.vue        # 分类
    ├── goods/goods.vue              # 货盘详情(支持扫码进入)
    ├── shop/shop.vue                # 店铺详情
    ├── shops/shops.vue              # 店铺列表(tabBar)
    ├── reserve/reserve.vue          # 下单
    ├── reservation-detail/          # 订单详情
    │   └── reservation-detail.vue
    ├── favorites/favorites.vue      # 收藏(货品/店铺 tab)
    ├── profile/profile.vue          # 我的(切换身份 + 修改密码 + 批发商中心)
    ├── change-pwd/change-pwd.vue    # 修改密码(采购商/批发商)
    │
    └── wholesaler/                  # ★ 批发商端(登录后无 tabBar)
        ├── publish.vue              # 发布货盘
        ├── goods.vue                # 我的货盘(状态切换)
        ├── reservations.vue         # 预订处理(确认/取消)
        ├── mine.vue                 # 批发商中心
        └── qrcode.vue               # 店铺二维码
```

---

## 5. 状态管理(Pinia 风格)

### 为什么用 Pinia 风格

- ✅ 自动响应式(状态变 → 所有页面立刻更新)
- ✅ 模板里直接 `{{ userStore.isLogin }}`
- ✅ IDE 智能补全(每个 getter/action 都有类型)
- ✅ 模块化(未来可加 cartStore、orderStore)
- ✅ 零依赖(`Vue.observable` 内置,无需 `npm install pinia`)

### 用法

```js
import userStore from '@/store/user.js'

// state
userStore.token     // 'xxx'
userStore.role      // 'buyer'
userStore.profile   // { id, real_name, ... }

// getters
userStore.isLogin      // true / false
userStore.isBuyer      // true
userStore.canOrder     // true
userStore.canPublish   // false

// actions
userStore.setLogin(token, role, profile)
userStore.clearLogin()    // 清 token,保留 role
userStore.clearAll()      // 清全部
userStore.switchRole()    // 切换身份(回起始页用)
userStore.setApiBase(url)
```

### 模板里

```vue
<template>
  <view v-if="userStore.isLogin">已登录</view>
  <view v-else-if="userStore.isVisitor">游客</view>

  <button @tap="userStore.clearLogin()">退出</button>
</template>

<script>
import userStore from '@/store/user.js'
export default {
  data() { return { userStore } }
}
</script>
```

### 持久化

每次调用 `setLogin` / `clearLogin` / `clearAll` / `switchRole` 等 action,**自动写入** `uni.setStorageSync('GCH_USER', ...)`。启动时 Vue.observable 自动从缓存恢复。

---

## 6. API 路由总览

### 公共(无需登录)

| 方法 | 路径 | 用途 |
|---|---|---|
| POST | `/api/wxlogin` | 游客微信登录 |
| GET | `/api/common/config` | 系统配置 |
| POST | `/api/common/upload` | 上传图片 |
| GET | `/api/categories` | 货品分类(含资源数) |
| GET | `/api/goods` | 货盘列表 |
| GET | `/api/goods/:id` | 货盘详情 |
| GET | `/api/shops` | 店铺列表 |
| GET | `/api/shop/:id` | 店铺详情 |
| POST | `/api/call/dial` | 拨打电话(记录日志) |

### 游客

| 方法 | 路径 | 用途 |
|---|---|---|
| GET | `/api/visitor/profile` | 游客信息 |
| POST | `/api/visitor/logout` | 游客登出 |

### 采购商

| 方法 | 路径 | 用途 |
|---|---|---|
| POST | `/api/buyer/login` | 账号密码登录 |
| POST | `/api/buyer/logout` | 登出 |
| GET | `/api/buyer/profile` | 当前采购商信息 |
| POST | `/api/buyer/changePwd` | 修改密码 |
| POST | `/api/reservation` | 创建预订 |
| GET | `/api/reservations` | 我的预订列表 |
| GET | `/api/reservation/:id` | 预订详情 |
| POST | `/api/reservation/cancel` | 取消预订 |
| POST | `/api/favorite/goods` | 收藏/取消货品 |
| POST | `/api/favorite/shop` | 收藏/取消店铺 |
| GET | `/api/favorites` | 我的收藏列表 |

### 批发商

| 方法 | 路径 | 用途 |
|---|---|---|
| POST | `/api/wholesaler/login` | 账号密码登录 |
| POST | `/api/wholesaler/logout` | 登出 |
| GET | `/api/wholesaler/profile` | 批发商信息(含店铺) |
| POST | `/api/wholesaler/changePwd` | 修改密码 |
| GET | `/api/wholesaler/qrcode` | 获取店铺二维码 |
| GET | `/api/wholesaler/categories` | 可选分类 |
| GET | `/api/wholesaler/goods` | 我的货盘列表 |
| GET | `/api/wholesaler/goods/:id` | 货盘详情 |
| POST | `/api/wholesaler/goods/create` | 发布货盘 |
| POST | `/api/wholesaler/goods/status` | 上/下架 |
| GET | `/api/wholesaler/reservations` | 预订列表 |
| GET | `/api/wholesaler/reservation/:id` | 预订详情 |
| POST | `/api/wholesaler/reservation/confirm` | 确认预订 |
| POST | `/api/wholesaler/reservation/cancel` | 取消预订 |

完整路由表在 [后端 application/route.php](../application/route.php)。

---

## 7. 页面清单

| # | 路径 | 说明 | 角色 |
|---|---|---|---|
| 1 | `pages/start/start` | **起始页**(微信登录+采购商+供应商) | 未登录 |
| 2 | `pages/index/index` | 首页(货盘列表) | 全部 |
| 3 | `pages/category/category` | 分类(9 宫格) | 全部 |
| 4 | `pages/shops/shops` | **店铺列表**(tabBar) | 全部 |
| 5 | `pages/goods/goods` | 货盘详情(支持扫码进入) | 全部 |
| 6 | `pages/shop/shop` | 店铺详情 | 全部 |
| 7 | `pages/reserve/reserve` | 确认预订 | 采购商 |
| 8 | `pages/reservation-detail/reservation-detail` | **订单详情** | 采购商 |
| 9 | `pages/favorites/favorites` | 我的收藏 | 全部 |
| 10 | `pages/profile/profile` | 我的(切换身份/修改密码/批发商中心) | 全部 |
| 11 | `pages/change-pwd/change-pwd` | 修改密码 | 采购商/批发商 |
| 12 | `pages/login/login` | 登录页(微信/采购商/批发商) | 未登录 |
| 13 | `pages/wholesaler/publish` | **发布货盘** | 批发商 |
| 14 | `pages/wholesaler/goods` | **我的货盘** | 批发商 |
| 15 | `pages/wholesaler/reservations` | **预订处理** | 批发商 |
| 16 | `pages/wholesaler/mine` | **批发商中心** | 批发商 |
| 17 | `pages/wholesaler/qrcode` | **店铺二维码** | 批发商 |

tabBar 4 入口(采购商/游客):首页 / 店铺 / 收藏 / 我的
批发商登录后:**自动隐藏 tabBar**(用批发商中心页面做导航)

---

## 8. 启动流程

```
用户打开小程序
   ↓
App.vue onLaunch()
   ↓
读 userStore.role(从缓存恢复)
   ↓
├─ role=visitor/buyer  → reLaunch 到 /pages/index/index
├─ role=wholesaler    → reLaunch 到 /pages/wholesaler/mine
└─ 无 role              → reLaunch 到 /pages/start/start(3 入口)
```

### 起始页 → 登录

```
用户选「微信一键登录」   → uni.login() → POST /api/wxlogin → setLogin(visitor) → 首页
用户选「我是采购商」     → /pages/login/login?role=buyer → POST /api/buyer/login → setLogin(buyer) → 首页
用户选「我是供应商」     → /pages/login/login?role=wholesaler → POST /api/wholesaler/login → setLogin(wholesaler) → 批发商中心
```

### 切换身份(profile → 我的)

```
点「切换身份」 → userStore.switchRole()(清 token 保留 role) → reLaunch /pages/start/start
```

### 退出登录

```
点「退出登录」 → 调对应 logout API → userStore.clearAll() → reLaunch /pages/start/start
```

---

## 9. 开发指南

### 准备工作

1. 安装 [HBuilderX 标准版](https://www.dcloud.io/hbuilderx.html)(推荐,**零配置**)
2. 或安装 Node.js + vue-cli(`vue create -p dcloudio/uni-preset-vue`)

### 导入项目

#### 方式 A:HBuilderX(推荐)

1. 打开 HBuilderX
2. **文件 → 打开目录** → 选 `F:\2026\GCH\code\gch\uniapp-miniprogram`
3. **运行 → 运行到小程序模拟器 → 微信开发者工具**
4. 自动编译,微信开发者工具中预览

#### 方式 B:vue-cli(需要 npm)

```bash
cd F:\2026\GCH\code\gch\uniapp-miniprogram
npm install
npm run dev:mp-weixin    # 编译到微信小程序
npm run dev:h5            # 编译到 H5(localhost:8080)
```

### 后端地址配置

`store/user.js` 的默认 `apiBase`:

```js
apiBase: 'http://127.0.0.1:1992'   // 开发
apiBase: 'https://your-domain.com' // 生产
```

或者运行时通过 `userStore.setApiBase(url)` 修改。

### 添加新页面

1. 在 `pages/<module>/` 创建 `<name>.vue`
2. 在 `pages.json` 注册 `pages` 数组
3. 如需 tabBar,在 tabBar.list 加条目并提供 PNG

### 添加新 API

1. 在 `utils/api.js` 添加路径常量
2. 后端 `application/route.php` 注册路由(同步后端)
3. 在页面 `import { api } from '@/utils/request.js'` 后使用 `http.get(api.xxx)`

### 添加新状态字段

`store/user.js`:

```js
const state = Vue.observable({
  token: ...,
  cart: []   // ← 新字段
})

export const userStore = {
  get cart() { return state.cart },
  addToCart(item) { state.cart.push(item) }
}
```

### CORS 配置

后端 `public/index.php` 已加 CORS header(开发期)。生产环境建议在 Nginx 层配置:

```nginx
location /api/ {
    add_header 'Access-Control-Allow-Origin' '*' always;
    add_header 'Access-Control-Allow-Methods' 'GET,POST,OPTIONS,PUT,DELETE' always;
    add_header 'Access-Control-Allow-Headers' 'Content-Type,Token' always;
}
```

---

## 10. 构建与发布

### 微信小程序

1. HBuilderX → **发行 → 微信小程序(仅适用于 uni-app)**
2. 填写小程序 AppID
3. 在微信开发者工具中打开 `unpackage/dist/dev/mp-weixin`
4. 上传代码 → 微信后台提交审核 → 发布

### H5

```bash
npm run build:h5
```

输出在 `unpackage/dist/build/h5/`,部署到 Web 服务器即可。

### App

HBuilderX → **发行 → 原生 APP-云打包**,选 Android/iOS。

---

## 11. 故障排查

| 现象 | 排查 |
|---|---|
| 页面空白 | 看 console 报错,检查 `apiBase` 是否可访问 |
| CORS 报错 | 后端 `public/index.php` CORS header 是否生效 |
| Token 401 | token 过期,跳登录页重新登录 |
| 批发商端不显示 tabBar | 正常行为(批发商登录后用批发商中心做导航) |
| tabBar 图标不显示 | 检查 `static/tabbar/` 文件是否存在,文件大小 > 0 |
| 切换身份失败 | 看 `userStore.switchRole()` 是否被调用,role 缓存保留了吗 |
| 编译报错 keepAliveInclude | 确保 `main.js` 根 data 注入了 `keepAliveInclude: []` |
| 编译报错 setData | uniapp 用 Vue 响应式,直接 `this.x = y`,不要用 `setData` |
| 微信小程序预览白屏 | 微信开发者工具勾选「不校验合法域名」 |
| 真机调试连不上 | 改 `apiBase` 为电脑内网 IP(不是 127.0.0.1) |

---

## 📚 参考文档

- [GCH 后端 README](../application/...)
- [GCH 开发方案 V1.0](../果仓货盘小程序开发方案V1.0.md)
- [uniapp 官方文档](https://uniapp.dcloud.net.cn)
- [Vue 2 文档](https://v2.cn.vuejs.org)
- [ThinkPHP 5.1 文档](https://www.kancloud.cn/manual/thinkphp5_1)
- [FastAdmin 文档](https://doc.fastadmin.net)
