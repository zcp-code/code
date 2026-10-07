<?php
namespace app\api\controller;

use app\api\model\Visitor;
use extend\gch\Token;
use extend\gch\Wechat;
use think\Env;

/**
 * 游客微信授权登录
 * POST /api/wxlogin
 * 入参:code, nickname, avatar
 *
 * 测试模式(M2 之前):检测 .env 中 `wxlogin_test_mode=1`,直接 mock openid,
 * 不调真实微信 API(测试 AppID 调 code2Session 会报 invalid appid)
 */
class Wxlogin extends ApiBase
{
    protected $requireLogin = false;

    public function index()
    {
        $code     = $this->request->param('code', '');
        $nickname = $this->request->param('nickname', '游客');
        $avatar   = $this->request->param('avatar', '');

        if (empty($code)) {
            return $this->error('缺少 code');
        }

        // 测试模式:不开通真实微信,直接 mock openid
        if (Env::get('wxlogin_test_mode') == '1') {
            $openid = 'mock_openid_' . substr(md5($code . time()), 0, 24);
        } else {
            $appid  = Env::get('wechat.min_appid', '');
            $secret = Env::get('wechat.min_secret', '');
            if (empty($appid) || empty($secret)) {
                return $this->error('微信小程序未配置（请填写 .env 中的 wechat.min_appid / min_secret）');
            }

            $session = Wechat::code2Session($code, $appid, $secret);
            if (!$session) {
                return $this->error('微信登录失败，请重试');
            }
            $openid = $session['openid'];
        }

        $visitor = Visitor::findByOpenid($openid);
        if (!$visitor) {
            $visitor = Visitor::create([
                'openid'   => $openid,
                'unionid'  => '',
                'nickname' => $nickname,
                'avatar'   => $avatar,
                'status'   => 1,
            ]);
        } else {
            $visitor->nickname = $nickname;
            $visitor->avatar = $avatar;
            $visitor->last_login_time = time();
            $visitor->last_login_ip = $this->clientIp();
            $visitor->save();
        }

        $tk = Token::create('visitor', $visitor->id, $this->clientIp());

        return $this->success([
            'token'    => $tk['token'],
            'expire'   => $tk['expire_time'],
            'role'     => 'visitor',
            'visitor'  => [
                'id'       => $visitor->id,
                'nickname' => $visitor->nickname,
                'avatar'   => $visitor->avatar,
            ],
        ], '登录成功');
    }
}
