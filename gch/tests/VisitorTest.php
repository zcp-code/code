<?php
/**
 * 游客登录测试
 */
require_once __DIR__ . '/TestCase.php';

class VisitorTest extends TestCase
{
    public function run()
    {
        $this->startTest('VisitorTest - 游客管理');

        $openid = 'test_openid_' . uniqid();

        // 测试 1: 首次创建游客
        $v1 = \app\api\model\Visitor::create([
            'openid'   => $openid,
            'nickname' => '测试游客1',
            'status'   => 1,
        ]);
        $this->assertNotEmpty($v1->id, '新游客应能创建');

        // 测试 2: 根据 openid 查找
        $v2 = \app\api\model\Visitor::findByOpenid($openid);
        $this->assertEquals($v1->id, (int)$v2->id, '根据 openid 应能找到游客');

        // 测试 3: openid 唯一
        $openid2 = 'test_openid_' . uniqid();
        $v3 = \app\api\model\Visitor::create(['openid' => $openid2, 'status' => 1]);
        $this->assertNotEmpty($v3->id, '不同 openid 应能创建');

        // 测试 4: 游客登录拿 Token
        $tk = \extend\gch\Token::create('visitor', $v1->id, '127.0.0.1');
        $this->assertNotEmpty($tk['token'], '游客应能拿到 Token');

        // 测试 5: 校验 Token
        $info = \extend\gch\Token::check($tk['token'], 'visitor');
        $this->assertEquals('visitor', $info['role'], '游客 Token 角色应为 visitor');
        $this->assertEquals($v1->id, (int)$info['user_id'], '游客 Token user_id 应匹配');

        // 测试 6: 拉黑后还能登录吗？—— 业务逻辑层面会拦截（不在 Token 层）
        // Token 层只验证 expire_time，所以这里只验证 Token 机制本身

        // 清理
        \extend\gch\Token::destroy($tk['token']);
        \think\Db::name('visitor')->where('id', $v1->id)->delete();
        \think\Db::name('visitor')->where('id', $v3->id)->delete();

        return ['pass' => $this->pass, 'fail' => $this->fail];
    }
}
