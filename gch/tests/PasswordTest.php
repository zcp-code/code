<?php
/**
 * 密码加密测试
 */
require_once __DIR__ . '/TestCase.php';

class PasswordTest extends TestCase
{
    public function run()
    {
        $this->startTest('PasswordTest - 密码加密/验证/生成');

        // 测试 1: 加密后能验证
        $pwd = 'mypassword123';
        $hash = \extend\gch\Password::hash($pwd);
        $this->assertTrue(\extend\gch\Password::verify($pwd, $hash), '加密后应能验证通过');

        // 测试 2: 错误密码验证失败
        $this->assertFalse(\extend\gch\Password::verify('wrongpassword', $hash), '错误密码应验证失败');

        // 测试 3: 每次加密哈希值不同（bcrypt salt）
        $hash2 = \extend\gch\Password::hash($pwd);
        $this->assertTrue($hash !== $hash2, '相同密码两次加密哈希值应不同');

        // 测试 4: 生成密码长度正确
        $gen = \extend\gch\Password::generate(10);
        $this->assertEquals(10, strlen($gen), '生成密码长度应为 10');

        // 测试 5: 生成密码含字母和数字
        $this->assertTrue(ctype_alnum($gen), '生成密码应只含字母和数字');
        $this->assertTrue(preg_match('/[a-zA-Z]/', $gen) && preg_match('/[0-9]/', $gen), '生成密码应同时含字母和数字');

        // 测试 6: 长度边界（8-20）
        $gen8 = \extend\gch\Password::generate(8);
        $this->assertEquals(8, strlen($gen8), '最小长度 8');
        $gen20 = \extend\gch\Password::generate(20);
        $this->assertEquals(20, strlen($gen20), '最大长度 20');

        // 测试 7: 异常长度（>20 应被截断为 20）
        $genOver = \extend\gch\Password::generate(30);
        $this->assertTrue(strlen($genOver) <= 20, '超长应截断');

        return ['pass' => $this->pass, 'fail' => $this->fail];
    }
}
