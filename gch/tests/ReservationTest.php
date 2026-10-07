<?php
/**
 * 预订流程测试（核心）
 */
require_once __DIR__ . '/TestCase.php';

class ReservationTest extends TestCase
{
    private $testShopId;
    private $testWholesalerId;
    private $testBuyerId;
    private $testGoodsId;

    public function setUp()
    {
        // 创建测试店铺
        $shop = \app\api\model\Shop::create([
            'name'          => '测试店铺_' . uniqid(),
            'contact_phone' => '13800000000',
            'status'        => 1,
        ]);
        $this->testShopId = $shop->id;

        // 创建测试批发商（密码哈希）
        $wh = \app\api\model\Wholesaler::create([
            'account'   => 'test_wh_' . uniqid(),
            'password'  => \extend\gch\Password::hash('test123456'),
            'shop_id'   => $shop->id,
            'real_name' => '测试批发商',
            'status'    => 1,
        ]);
        $this->testWholesalerId = $wh->id;

        // 创建测试采购商
        $buyer = \app\api\model\Buyer::create([
            'account'   => 'test_buyer_' . uniqid(),
            'password'  => \extend\gch\Password::hash('test123456'),
            'real_name' => '测试采购商',
            'status'    => 1,
        ]);
        $this->testBuyerId = $buyer->id;

        // 创建测试分类
        $cat = \think\Db::name('product_category')->where('name', '其他')->find();
        if (!$cat) {
            $cat = \app\api\model\ProductCategory::create(['name' => '其他', 'status' => 1, 'sort' => 99]);
        }

        // 创建测试货盘
        $goods = \app\api\model\Goods::create([
            'goods_no'      => 'TEST' . uniqid(),
            'wholesaler_id' => $wh->id,
            'shop_id'       => $shop->id,
            'category_id'   => $cat['id'],
            'name'          => '测试货品',
            'price'         => 10.00,
            'unit'          => '斤',
            'total_stock'   => 100,
            'status'        => 1,
            'publish_time'  => time(),
        ]);
        $this->testGoodsId = $goods->id;
    }

    public function tearDown()
    {
        // 清理测试数据
        \think\Db::name('reservation')->where('goods_id', $this->testGoodsId)->delete();
        \think\Db::name('goods')->where('id', $this->testGoodsId)->delete();
        \think\Db::name('wholesaler')->where('id', $this->testWholesalerId)->delete();
        \think\Db::name('buyer')->where('id', $this->testBuyerId)->delete();
        \think\Db::name('shop')->where('id', $this->testShopId)->delete();
    }

    public function run()
    {
        $this->startTest('ReservationTest - 预订创建/确认/取消');

        $this->setUp();
        try {
            // 测试 1: 正常创建预订
            list($ok, $msg, $order) = \app\api\model\Reservation::createOrder(
                $this->testGoodsId, $this->testBuyerId, 10
            );
            $this->assertTrue($ok, '创建预订应成功');
            $this->assertEquals('pending', $order->status, '新建预订状态应为 pending');

            // 测试 2: 库存被锁定（reserved_quantity 增加）
            $goods = \app\api\model\Goods::get($this->testGoodsId);
            $this->assertEquals(10, $goods->reserved_quantity, '已预订数量应为 10');

            // 测试 3: 可预订数量减少
            $available = \extend\gch\Inventory::available($goods->total_stock, $goods->reserved_quantity);
            $this->assertEquals(90, $available, '可预订数量应为 90');

            // 测试 4: 库存不足时应失败
            list($ok2, $msg2, $_) = \app\api\model\Reservation::createOrder(
                $this->testGoodsId, $this->testBuyerId, 999
            );
            $this->assertFalse($ok2, '库存不足应失败');
            $this->assertTrue(strpos($msg2, '库存不足') !== false, '错误消息应包含「库存不足」');

            // 测试 5: 数量为 0 应失败
            list($ok3, $msg3, $_) = \app\api\model\Reservation::createOrder(
                $this->testGoodsId, $this->testBuyerId, 0
            );
            $this->assertFalse($ok3, '数量 0 应失败');

            // 测试 6: 数量为负数应失败
            list($ok4, $msg4, $_) = \app\api\model\Reservation::createOrder(
                $this->testGoodsId, $this->testBuyerId, -1
            );
            $this->assertFalse($ok4, '负数应失败');

            // 测试 7: 确认预订
            list($ok5, $msg5) = $order->confirm();
            $this->assertTrue($ok5, '确认预订应成功');
            $this->assertEquals('confirmed', $order->status, '状态应变为 confirmed');

            // 测试 8: 已确认不能再取消
            list($ok6, $msg6) = $order->cancel('test', 'buyer');
            $this->assertFalse($ok6, '已确认订单不可取消');

            // 测试 9: 创建新预订并取消
            list($ok7, $msg7, $order2) = \app\api\model\Reservation::createOrder(
                $this->testGoodsId, $this->testBuyerId, 5
            );
            $this->assertTrue($ok7, '第二次预订应成功');
            list($ok8, $msg8) = $order2->cancel('测试取消', 'buyer');
            $this->assertTrue($ok8, '取消待确认预订应成功');
            $this->assertEquals('cancelled', $order2->status, '状态应为 cancelled');

            // 测试 10: 取消后库存释放
            $goods2 = \app\api\model\Goods::get($this->testGoodsId);
            $this->assertEquals(10, $goods2->reserved_quantity, '已预订数量应回到 10（释放了 5）');

            // 测试 11: 重复取消应失败
            list($ok9, $msg9) = $order2->cancel('再次取消', 'buyer');
            $this->assertFalse($ok9, '重复取消应失败');
        } finally {
            $this->tearDown();
        }

        return ['pass' => $this->pass, 'fail' => $this->fail];
    }
}
