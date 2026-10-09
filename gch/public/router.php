<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

// ============================================================
// 安全: FastAdmin 改名后台入口(MaQbwKnULZ.php)识别
// PHP built-in server 用 router.php 时,对 /MaQbwKnULZ.php/xxx 形式 URL,
// 原 FastAdmin router 会 is_file(DOCUMENT_ROOT + SCRIPT_NAME) 找不到 → fallback 到 index.php,
// 导致 TP5 把 "MaQbwKnULZ.php" 当成 module 名 → "模块不存在:maqbwknulz.php"
// 这里手动识别 /xxx.php/yyy 形式,直接 require 真正的入口脚本(由脚本内 Route::bind('admin') 接管)
// ============================================================
$requestPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
if (preg_match('#^/([^/]+\.php)(/|$)#i', $requestPath, $matches)) {
    $scriptFile = __DIR__ . '/' . $matches[1];
    if (is_file($scriptFile)) {
        $_SERVER['SCRIPT_NAME'] = '/' . $matches[1];
        $_SERVER['SCRIPT_FILENAME'] = $scriptFile;
        require $scriptFile;
        return;
    }
}

if (is_file($_SERVER["DOCUMENT_ROOT"] . $_SERVER["SCRIPT_NAME"])) {
    return false;
} else {
    $_SERVER["SCRIPT_FILENAME"] = __DIR__ . '/index.php';

    require __DIR__ . "/index.php";
}
