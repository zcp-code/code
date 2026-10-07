<?php
/**
 * 密码加密工具
 * 使用 PHP 推荐的 password_hash（bcrypt）
 */

namespace extend\gch;

class Password
{
    /**
     * 生成密码哈希
     */
    public static function hash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * 验证密码
     */
    public static function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * 生成随机密码
     *
     * @param int $length 长度 8-20
     * @return string
     */
    public static function generate($length = 10)
    {
        $length = max(8, min(20, (int)$length));
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        $hasLetter = false;
        $hasDigit = false;
        $max = strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $c = $chars[random_int(0, $max)];
            $password .= $c;
            if (ctype_alpha($c)) $hasLetter = true;
            if (ctype_digit($c)) $hasDigit = true;
        }

        // 保证含字母和数字
        if (!$hasLetter || !$hasDigit) {
            return self::generate($length);
        }
        return $password;
    }
}
