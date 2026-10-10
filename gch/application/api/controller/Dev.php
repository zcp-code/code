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
     * 入参:role = buyer | wholesaler | visitor
     * 入参:account(可选,默认 buyer001 / wh001,visitor 时可省)
     * 入参:openid(可选,visitor 用,默认 'mock_visitor_001')
     * 出参:{token, role, user_id, expire}
     */
    public function login()
    {
        $role    = $this->request->param('role', 'buyer');
        $account = $this->request->param('account', '');
        $openid  = $this->request->param('openid', '');

        // 游客角色 — dev 演示模式:复用/创建一个 mock visitor 记录
        if ($role === 'visitor') {
            $openid = $openid ?: 'mock_visitor_' . substr(md5(uniqid('', true)), 0, 16);
            $visitor = Db::name('visitor')->where('openid', $openid)->find();
            if (!$visitor) {
                $vid = Db::name('visitor')->insertGetId([
                    'openid'   => $openid,
                    'unionid'  => '',
                    'nickname' => '演示游客',
                    'avatar'   => '',
                    'status'   => 1,
                    'createtime' => time(),
                    'updatetime' => time(),
                ]);
            } else {
                $vid = $visitor['id'];
            }
            $tk = Token::create('visitor', $vid, $this->clientIp());
            return $this->success([
                'token'   => $tk['token'],
                'expire'  => $tk['expire_time'],
                'role'    => 'visitor',
                'user_id' => $vid,
                'visitor' => [
                    'id'       => $vid,
                    'nickname' => $visitor ? ($visitor['nickname'] ?? '演示游客') : '演示游客',
                    'avatar'   => $visitor['avatar'] ?? '',
                ]
            ], 'dev 游客登录成功');
        }

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
