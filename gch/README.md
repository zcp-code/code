# 仓货盘小程序（GCH）

> 微信小程序：采购商浏览/下单 + 批发商发布/处理预订 + PC 管理员后台
> 技术栈：**FastAdmin（ThinkPHP 5.1）+ MySQL + Redis + 微信小程序**

---

## 📁 项目结构

```
gch/
├── application/        # 应用模块
│   ├── admin/          # PC 后台（FastAdmin 自带 + 业务模块）
│   ├── api/            # 小程序 API（待开发）
│   ├── common/         # 公共模块
│   └── index/          # 前台
├── public/             # 入口
│   ├── index.php       # API 入口
│   └── admin/          # PC 后台入口
├── sql/
│   └── install.sql     # 数据库初始化脚本
├── thinkphp/           # 框架核心
├── extend/             # 扩展目录
├── addons/             # 插件目录
├── vendor/             # composer 依赖（需自行安装）
├── runtime/            # 运行时缓存（自动生成）
├── .env                # 环境配置（**不入 git**）
├── .env.sample         # 环境配置示例
├── composer.json       # composer 配置
├── think               # 命令行入口
└── README.md
```

---

## 🚀 快速开始（开发环境）

### 1. 准备环境

- PHP >= 7.4（扩展：fpm、mysqlnd、redis、gd、mbstring、bcmath、curl、openssl、zip）
- MySQL >= 5.7
- Redis（可选）
- Composer 2.x
- Nginx 或 Apache

### 2. 安装依赖

```bash
cd gch/
composer install
```

### 3. 配置环境

复制 `.env.sample` 为 `.env` 并修改：

```bash
cp .env.sample .env
```

编辑 `.env`，填入：
- 数据库连接（hostname、database、username、password）
- Redis 连接（如使用）
- 微信小程序 AppID / AppSecret（用于游客登录和店铺二维码）
- 短信配置（如使用）

### 4. 创建数据库

```bash
mysql -u root -p
> CREATE DATABASE gch_dev DEFAULT CHARSET utf8mb4;
> exit

mysql -u root -p gch_dev < sql/install.sql
```

### 5. 配置 Web 服务器

#### Nginx 示例

```nginx
server {
    listen 80;
    server_name gch.local;
    root /path/to/gch/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php-fpm/www.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 6. 启动验证

- 访问：`http://gch.local/`  → API 入口
- 访问：`http://gch.local/admin/` → PC 后台（默认账号 admin / 123456）
- 命令行：`php think` 测试

---

## 🛒 业务模块

| 模块 | 控制器 | 状态 |
|---|---|---|
| 用户管理 | `application/admin/controller/{Buyer,Wholesaler}.php` | 待开发 |
| 货盘管理 | `application/admin/controller/Goods.php` | 待开发 |
| 预订管理 | `application/admin/controller/Reservation.php` | 待开发 |
| 货品分类 | `application/admin/controller/Category.php` | 待开发 |
| 店铺管理 | `application/admin/controller/Shop.php` | 待开发 |
| 控制台 | `application/admin/controller/Dashboard.php` | 待开发 |
| 小程序 API | `application/api/controller/` | 待开发 |

---

## 📊 数据库

### 表前缀：`fy_`

### 系统表（沿用 FastAdmin）

| 表名 | 说明 |
|---|---|
| `fy_admin` | 管理员 |
| `fy_admin_log` | 管理员操作日志 |
| `fy_auth_group` / `fy_auth_group_access` | 角色与成员 |
| `fy_auth_rule` | 权限规则 |
| `fy_attachment` | 附件 |
| `fy_config` | 配置 |

### 业务表（本项目）

| 表名 | 说明 |
|---|---|
| `fy_visitor` | 游客（微信登录用户）|
| `fy_buyer` | 采购商（账号密码登录）|
| `fy_wholesaler` | 批发商（账号密码登录）|
| `fy_category` | 货品分类 |
| `fy_shop` | 店铺 |
| `fy_goods` | 货盘 |
| `fy_goods_image` | 货盘图片 |
| `fy_reservation` | 预订（=订单）|
| `fy_favorite_goods` | 收藏货品 |
| `fy_favorite_shop` | 收藏店铺 |
| `fy_call_log` | 拨打电话日志 |

---

## 🔐 安全

- `.env` 含敏感信息，**不可入 git**，已在 `.gitignore` 中排除
- 生产环境务必修改默认管理员密码（`admin / 123456`）
- API Token 有效期默认 7 天（可在 `.env` 中调整）
- 数据库密码使用强密码
- 微信 AppSecret 仅存服务端
- 启动 `debug = false`（生产环境）

---

## 📝 开发规范

- 命名：表名 `fy_xxx`、控制器 `Xxx.php`、模型 `Xxx.php`、字段 `snake_case`
- 控制器命名与模型同名
- 子模块用子目录（如 `api/controller/agent/`）
- 业务代码遵循 FastAdmin 规范
- 关键业务写单测（参考方案 M2 阶段）

---

## 📚 相关文档

- [果仓货盘小程序开发方案V1.0.md](../果仓货盘小程序开发方案V1.0.md) — 完整功能设计
- [仓货盘小程序部署方案V1.0.md](../仓货盘小程序部署方案V1.0.md) — 部署架构与运维
- [仓货盘小程序系统功能需求与遗漏补充说明_V1.1.md](../仓货盘小程序系统功能需求与遗漏补充说明_V1.1.md) — 需求基线
- [FastAdmin 文档](https://doc.fastadmin.net)
- [ThinkPHP 5.1 文档](https://www.kancloud.cn/manual/thinkphp5_1)

---

## 📅 开发进度

- [x] M1：基础搭建
- [ ] M2：用户体系（登录、Token）
- [ ] M3：核心业务（货盘、预订、库存）
- [ ] M4：辅助业务（店铺、收藏、拨号）
- [ ] M5：PC 后台
- [ ] M6：联调测试
- [ ] M7：上线部署
