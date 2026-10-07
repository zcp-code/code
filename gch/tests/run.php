<?php
/**
 * 单元测试运行入口
 * 用法：php tests/run.php [TestName]
 * 例如：php tests/run.php PasswordTest
 */

define('TEST_ROOT', __DIR__ . '/');
require __DIR__ . '/TestCase.php';

// 加载所有测试
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
    $file = TEST_ROOT . $name . '.php';
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
