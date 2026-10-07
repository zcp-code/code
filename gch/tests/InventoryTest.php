<?php
/**
 * 库存计算测试
 */
require_once __DIR__ . '/TestCase.php';

class InventoryTest extends TestCase
{
    public function run()
    {
        $this->startTest('InventoryTest - 库存计算');

        // 测试 1: 正常情况
        $this->assertEquals(80, \extend\gch\Inventory::available(100, 20), '100-20=80');

        // 测试 2: 全部售出
        $this->assertEquals(0, \extend\gch\Inventory::available(100, 100), '100-100=0');

        // 测试 3: 超额预订（不应为负数）
        $this->assertEquals(0, \extend\gch\Inventory::available(100, 150), '100-150 应返回 0，不为负');

        // 测试 4: 0 库存
        $this->assertEquals(0, \extend\gch\Inventory::available(0, 0), '0-0=0');

        // 测试 5: 类型转换（字符串数字）
        $this->assertEquals(50, \extend\gch\Inventory::available('100', '50'), '字符串数字应能计算');

        // 测试 6: 锁函数测试
        $counter = 0;
        $result = \extend\gch\Inventory::lock('test_lock', function () use (&$counter) {
            $counter++;
            return 'ok';
        });
        $this->assertEquals('ok', $result, '锁函数应返回回调结果');
        $this->assertEquals(1, $counter, '锁内回调应执行一次');

        // 测试 7: 锁释放后能再获取
        $result2 = \extend\gch\Inventory::lock('test_lock', function () use (&$counter) {
            $counter++;
            return 'ok2';
        });
        $this->assertEquals('ok2', $result2, '锁释放后可再次获取');
        $this->assertEquals(2, $counter, '回调应执行 2 次');

        return ['pass' => $this->pass, 'fail' => $this->fail];
    }
}
