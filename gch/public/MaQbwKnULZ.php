<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// [ 后台入口文件 ]
// 使用此文件可以达到隐藏admin模块的效果
// 为了你的安全，强烈不建议将此文件名修改成admin.php

// ============================================================
// 安全: 修正 $_SERVER 变量,防止 web server rewrite 后 PATH_INFO/REQUEST_URI 仍包含脚本名
// (例如 Apache/Nginx 把 /MaQbwKnULZ.php/xxx 重写到 /index.php 后,
//  $_SERVER['PATH_INFO'] = '/MaQbwKnULZ.php/xxx',TP5 解析时第一段是 'MaQbwKnULZ.php' 当 module 名 → "模块不存在")
// 这里主动把脚本名前缀从 PATH_INFO/REQUEST_URI 中剥掉,并把 SCRIPT_NAME 修正为当前入口
// ============================================================
$selfName = '/' . basename(__FILE__);
$_SERVER['SCRIPT_NAME']     = $selfName;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$_SERVER['PHP_SELF']        = $selfName;

if (isset($_SERVER['PATH_INFO']) && stripos($_SERVER['PATH_INFO'], $selfName) === 0) {
    $_SERVER['PATH_INFO'] = substr($_SERVER['PATH_INFO'], strlen($selfName));
}
if (isset($_SERVER['ORIG_PATH_INFO']) && stripos($_SERVER['ORIG_PATH_INFO'], $selfName) === 0) {
    $_SERVER['ORIG_PATH_INFO'] = substr($_SERVER['ORIG_PATH_INFO'], strlen($selfName));
}
if (isset($_SERVER['REQUEST_URI']) && stripos($_SERVER['REQUEST_URI'], $selfName) === 0) {
    $_SERVER['REQUEST_URI'] = $selfName . substr($_SERVER['REQUEST_URI'], strlen($selfName));
}

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');

// 判断是否安装
if (!is_file(APP_PATH . 'admin/command/Install/install.lock')) {
    header("location:./install.php");
    exit;
}

// 加载框架引导文件
require __DIR__ . '/../thinkphp/base.php';

// 注册业务扩展命名空间自动加载(extend\gch\*)
// index.php 里也有这段,MaQbwKnULZ.php 作为独立入口也必须注册,否则 TP5 派发到 controller 时找不到 extend\gch\Inventory 这类类
spl_autoload_register(function ($class) {
    if (strpos($class, 'extend\\gch\\') === 0) {
        $rel   = str_replace('extend\\gch\\', '', $class);
        $file  = __DIR__ . '/../extend/gch/' . $rel . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});

// 绑定到admin模块
\think\Route::bind('admin');

// 关闭路由
\think\App::route(false);

// 设置根url
\think\Url::root('');

// 执行应用
\think\App::run()->send();
