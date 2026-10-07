# 部署说明

本目录包含 GCH（仓货盘小程序）在 **phpStudy + Nginx** 环境下的一键部署脚本。

## 文件清单

| 文件 | 说明 |
|---|---|
| `www.gch.local_1992.conf` | Nginx vhost 配置（监听 1992 端口，server_name `www.gch.local`） |
| `install.bat` | 一键部署脚本（**右键 → 以管理员身份运行**） |
| `uninstall.bat` | 反部署脚本（移除 vhost + hosts） |

## 部署步骤

### 1. 启动 phpStudy

确保 phpStudy 的 **Nginx 1.15.11** 和 **MySQL 5.7** 已启动。

### 2. 一键部署

右键 `install.bat` → **以管理员身份运行**。脚本会做：

1. 复制 vhost 配置到 `D:\phpstudy_pro\Extensions\Nginx1.15.11\conf\vhosts\`
2. 向 `C:\Windows\System32\drivers\etc\hosts` 添加 `127.0.0.1 www.gch.local`
3. 创建 `runtime/` 和 `public/uploads/` 目录
4. 若 `.env` 不存在则从 `.env.sample` 复制
5. 重载 Nginx

### 3. 修改 `.env`

编辑 `F:\2026\GCH\code\gch\.env`，填入：

```ini
[wechat]
min_appid = 你的小程序 AppID
min_secret = 你的小程序 AppSecret
```

### 4. 访问

| 地址 | 用途 |
|---|---|
| `http://www.gch.local:1992/` | API 入口（小程序对接） |
| `http://www.gch.local:1992/admin/` | PC 管理后台 |
| `http://www.gch.local:1992/admin/index/login` | 登录页（默认 admin / 123456） |

## 故障排查

| 现象 | 排查 |
|---|---|
| 502 Bad Gateway | 检查 Nginx vhost 中 `fastcgi_pass 127.0.0.1:9000` 与 phpStudy PHP 监听端口是否一致（phpStudy 默认 9000） |
| 404 / 空白 | 检查 Nginx vhost 是否被 include,`nginx.conf` 中是否有 `include vhosts/*.conf` |
| 数据库连不上 | 检查 `.env` 中 `hostport`（GCH 用 **3307**，非默认 3306） |
| 文件上传失败 | 检查 `public/uploads/` 目录是否可写（Linux 部署需 `chmod 755`） |

## 反部署

运行 `uninstall.bat` 即可清理 vhost + hosts 条目。
