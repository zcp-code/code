<?php
/**
 * 自定义 Token 服务（无状态 JWT 风格）
 * 用于小程序 API 的游客/采购商/批发商
 * 不依赖 FastAdmin Session Token
 */

namespace extend\gch;

use think\Db;
use think\exception\HttpException;

class Token
{
    /**
     * Token 有效期（秒），默认 7 天
     */
    const TOKEN_EXPIRE = 604800;

    /**
     * 生成 Token
     *
     * @param string $role  角色：visitor/buyer/wholesaler
     * @param int    $userId 用户 ID
     * @param string $ip    登录 IP
     * @return array ['token', 'expire_time']
     */
    public static function create($role, $userId, $ip = '')
    {
        $token = self::generate();
        $expire = time() + self::TOKEN_EXPIRE;

        Db::name('user_token')->insert([
            'role'        => $role,
            'user_id'     => $userId,
            'token'       => $token,
            'expire_time' => $expire,
            'ip'          => $ip,
            'createtime'  => time(),
        ]);

        return ['token' => $token, 'expire_time' => $expire];
    }

    /**
     * 校验 Token
     *
     * @param string $token
     * @param string|null $role 期望角色，null 则不校验
     * @return array|false 返回 ['role', 'user_id'] 或 false
     */
    public static function check($token, $role = null)
    {
        if (empty($token)) {
            return false;
        }

        $row = Db::name('user_token')
            ->where('token', $token)
            ->where('expire_time', '>', time())
            ->find();

        if (!$row) {
            return false;
        }

        if ($role !== null && $row['role'] !== $role) {
            return false;
        }

        return ['role' => $row['role'], 'user_id' => (int)$row['user_id']];
    }

    /**
     * 销毁 Token
     */
    public static function destroy($token)
    {
        Db::name('user_token')->where('token', $token)->delete();
    }

    /**
     * 清理过期 Token
     */
    public static function cleanExpired()
    {
        Db::name('user_token')->where('expire_time', '<=', time())->delete();
    }

    /**
     * 生成随机 Token
     */
    private static function generate()
    {
        return bin2hex(random_bytes(16)) . bin2hex(random_bytes(16));
    }
}
