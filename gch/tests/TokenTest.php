<?php
/**
 * Token 服务测试
 */
require_once __DIR__ . '/TestCase.php';

class TokenTest extends TestCase
{
    public function run()
    {
        $this->startTest('TokenTest - Token 创建/校验/销毁');

        // 测试 1: 创建 Token 返回正确的结构
        $userId = 9999; // 测试用户 ID
        $tk = \extend\gch\Token::create('buyer', $userId, '127.0.0.1');
        $this->assertNotEmpty($tk['token'] ?? '', 'Token 应返回非空 token');
        $this->assertNotEmpty($tk['expire_time'] ?? '', 'Token 应返回 expire_time');
        $this->assertTrue($tk['expire_time'] > time(), 'Token 过期时间应大于当前时间');

        // 测试 2: 校验 Token
        $info = \extend\gch\Token::check($tk['token'], 'buyer');
        $this->assertEquals('buyer', $info['role'], 'Token 角色应为 buyer');
        $this->assertEquals($userId, $info['user_id'], 'Token user_id 应匹配');

        // 测试 3: 角色不匹配应返回 false
        $wrong = \extend\gch\Token::check($tk['token'], 'wholesaler');
        $this->assertFalse($wrong, '角色不匹配应返回 false');

        // 测试 4: 不校验角色（null）应通过
        $any = \extend\gch\Token::check($tk['token']);
        $this->assertNotEmpty($any, '不指定角色校验应通过');

        // 测试 5: 空 Token 返回 false
        $this->assertFalse(\extend\gch\Token::check(''), '空 Token 应返回 false');
        $this->assertFalse(\extend\gch\Token::check(null), 'null Token 应返回 false');

        // 测试 6: 不存在的 Token 返回 false
        $this->assertFalse(\extend\gch\Token::check('not_exist_token_xyz'), '不存在 Token 应返回 false');

        // 测试 7: 销毁 Token
        \extend\gch\Token::destroy($tk['token']);
        $this->assertFalse(\extend\gch\Token::check($tk['token']), '销毁后 Token 应失效');

        // 清理
        \think\Db::name('user_token')->where('user_id', $userId)->delete();

        return ['pass' => $this->pass, 'fail' => $this->fail];
    }
}
