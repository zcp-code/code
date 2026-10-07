<?php
namespace app\api\controller;

use extend\gch\Token;
use think\Db;

/**
 * 开发辅助接口(M2 之前演示用)
 * 前端 wxlogin.vue 直接调这个生成 buyer/wholesaler 真 token,
 * 写到 fy_user_token 表,后续预订/发布 API 都能用
 */
class Dev extends ApiBase
{
    protected $requireLogin = false;

    /**
     * POST /api/dev/login
     * 入参:role = buyer | wholesaler
     * 入参:account(可选,默认 buyer001 / wh001)
     * 出参:{token, role, user_id, expire}
     */
    public function login()
    {
        $role = $this->request->param('role', 'buyer');
        $account = $this->request->param('account', '');

        // 找默认账号
        if ($role === 'wholesaler') {
            $account = $account ?: 'wh001';
            $user = Db::name('wholesaler')->where('account', $account)->find();
        } else {
            $account = $account ?: 'buyer001';
            $user = Db::name('buyer')->where('account', $account)->find();
            $role = 'buyer';
        }

        if (!$user) {
            return $this->error('演示账号不存在: ' . $account);
        }
        if ($user['status'] != 1) {
            return $this->error('账号已停用');
        }

        // 生成 token 写 user_token 表
        $tk = Token::create($role, $user['id'], $this->clientIp());

        return $this->success([
            'token'   => $tk['token'],
            'expire'  => $tk['expire_time'],
            'role'    => $role,
            'user_id' => $user['id'],
            'account' => $account
        ], 'dev 登录成功');
    }
}
