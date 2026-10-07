@echo off
chcp 65001 > nul
setlocal

:: ============================================================
:: 仓货盘小程序（GCH）phpStudy 一键部署脚本
:: 用法：以管理员身份运行
:: ============================================================

set NGINX_VHOSTS=D:\phpstudy_pro\Extensions\Nginx1.15.11\conf\vhosts
set NGINX_BIN=D:\phpstudy_pro\Extensions\Nginx1.15.11\nginx.exe
set NGINX_PID=D:\phpstudy_pro\Extensions\Nginx1.15.11\logs\nginx.pid
set WINDOWS_HOSTS=%SystemRoot%\System32\drivers\etc\hosts
set PROJECT_ROOT=F:\2026\GCH\code\gch
set VHOST_NAME=www.gch.local_1992.conf
set DOMAIN=www.gch.local
set PORT=1992

echo.
echo ========================================
echo  GCH 部署脚本 (phpStudy + Nginx)
echo ========================================
echo.

:: 1. 复制 vhost 配置
echo [1/5] 复制 Nginx vhost 配置 ...
if not exist "%NGINX_VHOSTS%" (
    echo [错误] Nginx vhosts 目录不存在: %NGINX_VHOSTS%
    pause & exit /b 1
)
copy /Y "%~dp0%VHOST_NAME%" "%NGINX_VHOSTS%\%VHOST_NAME%" > nul
if errorlevel 1 (
    echo [错误] 复制失败，请确认以管理员身份运行
    pause & exit /b 1
)
echo       OK -> %NGINX_VHOSTS%\%VHOST_NAME%

:: 2. 添加 hosts 解析
echo [2/5] 添加 hosts 解析 ...
find /c "%DOMAIN%" "%WINDOWS_HOSTS%" > nul 2>&1
if errorlevel 1 (
    echo 127.0.0.1 %DOMAIN% >> "%WINDOWS_HOSTS%"
    echo       OK -> %DOMAIN% 已写入 hosts
) else (
    echo       跳过（已存在）
)

:: 3. 检查 runtime 目录
echo [3/5] 检查 runtime 目录 ...
if not exist "%PROJECT_ROOT%\runtime" (
    mkdir "%PROJECT_ROOT%\runtime"
)
if not exist "%PROJECT_ROOT%\public\uploads" (
    mkdir "%PROJECT_ROOT%\public\uploads"
)
echo       OK

:: 4. 校验 .env
echo [4/5] 校验 .env ...
if not exist "%PROJECT_ROOT%\.env" (
    if exist "%PROJECT_ROOT%\.env.sample" (
        copy "%PROJECT_ROOT%\.env.sample" "%PROJECT_ROOT%\.env" > nul
        echo       .env.sample 已复制为 .env，请根据实际情况修改
    ) else (
        echo [警告] .env 和 .env.sample 均不存在
    )
) else (
    echo       OK
)

:: 5. 重启 Nginx
echo [5/5] 重启 Nginx ...
if exist "%NGINX_PID%" (
    "%NGINX_BIN%" -s reload > nul 2>&1
    echo       OK -> Nginx 已重载
) else (
    echo       [提示] Nginx 未运行，请手动启动 phpStudy
)

echo.
echo ========================================
echo  部署完成！
echo ========================================
echo.
echo  API 入口:  http://%DOMAIN%:%PORT%/
echo  PC 后台:   http://%DOMAIN%:%PORT%/admin/
echo  默认账号:  admin / 123456
echo.
echo  下一步：
echo    1. 编辑 F:\2026\GCH\code\gch\.env 填入微信 AppID/Secret
echo    2. 打开浏览器访问上面的地址
echo.

pause
