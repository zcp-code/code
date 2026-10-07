<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// [ 应用入口文件 ]
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');

// 判断是否安装
if (!is_file(APP_PATH . 'admin/command/Install/install.lock')) {
    header("location:./install.php");
    exit;
}

// 注册业务扩展命名空间自动加载（extend\gch\*）
// 必须在 start.php 之前注册，否则应用派发阶段触发类加载时找不到
spl_autoload_register(function ($class) {
    if (strpos($class, 'extend\\gch\\') === 0) {
        $rel   = str_replace('extend\\gch\\', '', $class);
        $file  = __DIR__ . '/../extend/gch/' . $rel . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});

// CORS 跨域支持（uniapp H5 / 微信开发者工具调试 / 跨域前端都受益）
// 必须在 start.php 之前 header 输出
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Token, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');

// OPTIONS 预检直接返回 204
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
