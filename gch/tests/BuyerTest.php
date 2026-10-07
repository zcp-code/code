<?php
/**
 * 采购商登录测试
 */
require_once __DIR__ . '/TestCase.php';

class BuyerTest extends TestCase
{
    private $account;
    private $password = 'test123456';

    public function setUp()
    {
        $this->account = 'test_buyer_' . uniqid();
        \app\api\model\Buyer::create([
            'account'   => $this->account,
            'password'  => \extend\gch\Password::hash($this->password),
            'real_name' => '测试采购商',
            'status'    => 1,
        ]);
    }

    public function tearDown()
    {
        \think\Db::name('buyer')->where('account', $this->account)->delete();
    }

    public function run()
    {
        $this->startTest('BuyerTest - 采购商登录');

        $this->setUp();
        try {
            // 测试 1: 正确账号密码登录
            list($ok, $buyer, $msg) = \app\api\model\Buyer::login($this->account, $this->password);
            $this->assertTrue($ok, '正确密码应登录成功');
            $this->assertEquals($this->account, $buyer->account, '返回的账号应匹配');

            // 测试 2: 错误密码登录
            list($ok2, $_, $msg2) = \app\api\model\Buyer::login($this->account, 'wrong_password');
            $this->assertFalse($ok2, '错误密码应登录失败');
            $this->assertTrue(strpos($msg2, '密码') !== false, '错误消息应包含「密码」');

            // 测试 3: 不存在的账号
            list($ok3, $_, $msg3) = \app\api\model\Buyer::login('not_exist_account_xyz', 'anything');
            $this->assertFalse($ok3, '不存在账号应失败');
            $this->assertTrue(strpos($msg3, '账号不存在') !== false, '错误消息应包含「账号不存在」');

            // 测试 4: 停用账号
            \think\Db::name('buyer')->where('account', $this->account)->update(['status' => 0]);
            list($ok4, $_, $msg4) = \app\api\model\Buyer::login($this->account, $this->password);
            $this->assertFalse($ok4, '停用账号应登录失败');
            \think\Db::name('buyer')->where('account', $this->account)->update(['status' => 1]);

            // 测试 5: 修改密码
            $buyer = \app\api\model\Buyer::where('account', $this->account)->find();
            list($ok5, $msg5) = $buyer->changePassword($this->password, 'newpass123');
            $this->assertTrue($ok5, '正确原密码应能修改');

            // 测试 6: 修改后新密码可用，旧密码失效
            list($ok6, $_, $msg6) = \app\api\model\Buyer::login($this->account, $this->password);
            $this->assertFalse($ok6, '旧密码应失效');
            list($ok7, $_, $msg7) = \app\api\model\Buyer::login($this->account, 'newpass123');
            $this->assertTrue($ok7, '新密码应能登录');

            // 测试 7: 错误原密码改密应失败
            list($ok8, $msg8) = $buyer->changePassword('wrong_old', 'whatever123');
            $this->assertFalse($ok8, '错误原密码改密应失败');
        } finally {
            $this->tearDown();
        }

        return ['pass' => $this->pass, 'fail' => $this->fail];
    }
}
