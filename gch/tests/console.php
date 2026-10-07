<?php
/**
 * 通过 ThinkPHP 启动运行测试
 * 用法：php tests/console.php [TestName]
 *
 * 此脚本在 ThinkPHP 完全启动后再运行测试
 */

// 1. 设置应用路径
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';
define('APP_PATH', __DIR__ . '/../application/');

// 2. 加载 ThinkPHP 基础（已包含 composer autoload_static）
require __DIR__ . '/../thinkphp/base.php';

// 3. 注册我们的业务扩展 autoloader
spl_autoload_register(function ($class) {
    if (strpos($class, 'extend\\gch\\') === 0) {
        $rel = str_replace('extend\\gch\\', '', $class);
        $file = __DIR__ . '/../extend/gch/' . $rel . '.php';
        if (file_exists($file)) require_once $file;
    }
});

// 4. 初始化 ThinkPHP 应用（加载配置、连接数据库等）
\think\App::initCommon();

// 6. 加载测试基础
require_once __DIR__ . '/TestCase.php';

// 7. 运行测试
$tests = [
    'PasswordTest',
    'TokenTest',
    'InventoryTest',
    'ReservationTest',
    'VisitorTest',
    'BuyerTest',
];

$arg = $argv[1] ?? '';
$toRun = $arg ? [$arg] : $tests;

echo "\n=== 仓货盘单元测试 ===\n\n";

$totalPass = 0;
$totalFail = 0;
$startTime = microtime(true);

foreach ($toRun as $name) {
    $file = __DIR__ . '/' . $name . '.php';
    if (!file_exists($file)) {
        echo "⚠️  测试文件不存在：$name\n";
        continue;
    }
    require_once $file;
    if (!class_exists($name)) {
        echo "⚠️  类不存在：$name\n";
        continue;
    }
    $cls = new $name();
    $result = $cls->run();
    $totalPass += $result['pass'];
    $totalFail += $result['fail'];
}

$duration = round(microtime(true) - $startTime, 2);

echo "\n=== 总计 ===\n";
echo "通过：$totalPass\n";
echo "失败：$totalFail\n";
echo "耗时：{$duration}s\n";
exit($totalFail > 0 ? 1 : 0);
