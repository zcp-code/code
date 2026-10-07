@echo off
chcp 65001 > nul
setlocal

:: ============================================================
:: GCH 反部署脚本：移除 vhost + hosts + runtime 缓存
:: ============================================================

set NGINX_VHOSTS=D:\phpstudy_pro\Extensions\Nginx1.15.11\conf\vhosts
set NGINX_BIN=D:\phpstudy_pro\Extensions\Nginx1.15.11\nginx.exe
set WINDOWS_HOSTS=%SystemRoot%\System32\drivers\etc\hosts
set PROJECT_ROOT=F:\2026\GCH\code\gch
set VHOST_NAME=www.gch.local_1992.conf
set DOMAIN=www.gch.local

echo.
echo ========================================
echo  GCH 反部署脚本
echo ========================================
echo.

echo [1/3] 移除 Nginx vhost ...
if exist "%NGINX_VHOSTS%\%VHOST_NAME%" (
    del /F /Q "%NGINX_VHOSTS%\%VHOST_NAME%" > nul
    echo       OK
) else (
    echo       跳过（不存在）
)

echo [2/3] 移除 hosts 解析 ...
for /f "tokens=*" %%i in ('findstr /n "^" "%WINDOWS_HOSTS%" ^| findstr /b ".*:%DOMAIN%"') do (
    set "LINE=%%i"
)
:: 简单做法：直接尝试删除整行（带行号解析复杂）
powershell -Command "try { $line = (Get-Content '%WINDOWS_HOSTS%' | Select-String -Pattern '%DOMAIN%' | Select-Object -First 1).Line; (Get-Content '%WINDOWS_HOSTS%') | Where-Object { $_ -notmatch '%DOMAIN%' } | Set-Content '%WINDOWS_HOSTS%'; Write-Host '       OK' } catch { Write-Host '       跳过（无匹配）' }"

echo [3/3] 重载 Nginx ...
if exist "%NGINX_BIN%" (
    "%NGINX_BIN%" -s reload > nul 2>&1
    echo       OK
)

echo.
echo 反部署完成。
echo.
pause
