<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use app\common\library\WxUser;
use fast\Random;
use think\Config;
use think\Log;
use think\Validate;

/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['login', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third', 'minilogin', 'info', 'getPhone', 'agentLogin', 'agentHouses', 'agentCustomers'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }
    }

    /**
     * 会员中心
     */
    public function index()
    {
        $user = $this->auth->getUserinfo();
        $user['avatar'] = cdnurl($user['avatar'],true);
        $this->success('', ['data' => $user]);
    }

    /**
     * 会员登录（支持账号密码登录和小程序微信授权登录）
     *
     * @ApiMethod (POST)
     * 账号密码登录: account + password
     * 微信登录: code + nickname(可选) + avatar(可选)
     */
    public function login()
    {
        $code      = $this->request->post('code');
        $phoneCode = $this->request->post('phoneCode', '');
        $account   = $this->request->post('account');
        $password  = $this->request->post('password');

        // 小程序微信授权登录（支持 phoneCode 获取手机号）
        if ($code) {
            $nickname = $this->request->post('nickname', '');
            $avatar   = $this->request->post('avatar', '');

            // 获取微信配置
            $wxapp = db("wxapp")->where(['wxapp_id' => 1])->find();
            if (!$wxapp) {
                $this->error('微信配置不存在');
            }

            // 通过 code 换取 session_key 和 openid
            $WxUser = new WxUser($wxapp['app_id'], $wxapp['app_secret']);
            $session = $WxUser->sessionKey($code);

            if (empty($session) || empty($session['openid'])) {
                $this->error('微信授权失败，请重试');
            }

            $miniopenid = $session['openid'];
            $unionid    = isset($session['unionid']) ? $session['unionid'] : '';

            // 通过 phoneCode 换取手机号
            $userPhone = '';
            if ($phoneCode) {
                $userPhone = $WxUser->getPhoneNumber($phoneCode);
            }

            // 优先按 mini_openid 查找用户
            $user = \app\common\model\User::getByMiniOpenid($miniopenid);

            // 未找到则按 unionid 查找
            if (!$user && $unionid) {
                $user = \app\common\model\User::getByUnionid($unionid);
            }

            // 如果微信没找到，但拿到了手机号，尝试按手机号查找
            if (!$user && $userPhone) {
                $user = \app\common\model\User::where('mobile', $userPhone)->find();
            }

            if ($user) {
                // 已注册用户：更新微信信息和手机号
                $updateData = [
                    'nickname'    => $nickname ?: $user->nickname,
                    'avatar'      => $avatar ?: $user->avatar,
                    'mini_openid' => $miniopenid,
                    'unionid'     => $unionid,
                ];
                if ($userPhone) {
                    $updateData['mobile'] = $userPhone;
                }
                $user->save($updateData);

                $ret = $this->auth->direct($user->id);
                if ($ret) {
                    $data = $this->auth->getUserinfo();
                    $data['userPhone'] = $userPhone ?: $user->mobile;
                    $data['avatar'] = cdnurl($data['avatar'],true);
                    $this->success('登录成功', [
                        'token'    => $data['token'],
                        'userinfo' => $data,
                    ]);
                } else {
                    $this->error($this->auth->getError());
                }
            } else {
                // 新用户：注册
                $info = [
                    'mini_openid' => $miniopenid,
                    'unionid'     => $unionid,
                    'nickname'    => $nickname ?: '微信用户',
                    'avatar'      => $avatar ?: '',
                ];
                if ($userPhone) {
                    $info['mobile'] = $userPhone;
                }

                $ret = $this->auth->miniregister($info);
                if ($ret) {
                    $data = $this->auth->getUserinfo();
                    $data['userPhone'] = $userPhone;
                    $data['avatar'] = cdnurl($data['avatar'],true);
                    $this->success('注册成功', [
                        'token'    => $data['token'],
                        'userinfo' => $data,
                    ]);
                } else {
                    $this->error($this->auth->getError());
                }
            }
        }

        // 账号密码登录
        if ($account && $password) {
            $ret = $this->auth->login($account, $password);
            if ($ret) {
                $data = $this->auth->getUserinfo();
                $data['avatar'] = cdnurl($data['avatar'],true);
                $this->success(__('Logged in successful'), [
                    'token'    => $data['token'],
                    'userinfo' => $data,
                ]);
            } else {
                $this->error($this->auth->getError());
            }
        }

        $this->error(__('Invalid parameters'));
    }

    /**
     * 手机验证码登录
     *
     * @ApiMethod (POST)
     * @ApiParams (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams (name="captcha", type="string", required=true, description="验证码")
     */
    public function mobilelogin()
    {
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注册会员
     *
     * @ApiMethod (POST)
     * @ApiParams (name="username", type="string", required=true, description="用户名")
     * @ApiParams (name="password", type="string", required=true, description="密码")
     * @ApiParams (name="email", type="string", required=true, description="邮箱")
     * @ApiParams (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams (name="code", type="string", required=true, description="验证码")
     */
    public function register()
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        $email = $this->request->post('email');
        $mobile = $this->request->post('mobile');
        $code = $this->request->post('code');
        if (!$username || !$password) {
            $this->error(__('Invalid parameters'));
        }
        if ($email && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $ret = Sms::check($mobile, $code, 'register');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }
        $ret = $this->auth->register($username, $password, $email, $mobile, []);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 退出登录
     * @ApiMethod (POST)
     */
    public function logout()
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     *
     * @ApiMethod (POST)
     * @ApiParams (name="avatar", type="string", required=true, description="头像地址")
     * @ApiParams (name="username", type="string", required=true, description="用户名")
     * @ApiParams (name="nickname", type="string", required=true, description="昵称")
     * @ApiParams (name="bio", type="string", required=true, description="个人简介")
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->post('username');
        $nickname = $this->request->post('nickname');
        $bio = $this->request->post('bio');
        $avatar = $this->request->post('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Username already exists'));
            }
            $user->username = $username;
        }
        if ($nickname) {
            $exists = \app\common\model\User::where('nickname', $nickname)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Nickname already exists'));
            }
            $user->nickname = $nickname;
        }
        if ($avatar) {
            //判断是否匹配config('upload.cdnurl')开头以及当前$SERVER['HTTP_HOST']开头。
            if (preg_match('/^' . preg_quote(config('upload.cdnurl') . '/', '/') . '/i', $avatar)
                || preg_match('/^' . preg_quote(substr(config('upload.savekey'), 0, strpos(config('upload.savekey'), '{')), '/') . '/i', $avatar)
                || preg_match('/^' . preg_quote($_SERVER['HTTP_HOST'] . '/', '/') . '/i', $avatar)) {
                $user->avatar = $avatar;
            }
        }
        $user->bio = $bio;
        $user->save();
        $this->success();
    }

    /**
     * 修改邮箱
     *
     * @ApiMethod (POST)
     * @ApiParams (name="email", type="string", required=true, description="邮箱")
     * @ApiParams (name="captcha", type="string", required=true, description="验证码")
     */
    public function changeemail()
    {
        $user = $this->auth->getUser();
        $email = $this->request->post('email');
        $captcha = $this->request->post('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号
     *
     * @ApiMethod (POST)
     * @ApiParams (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams (name="captcha", type="string", required=true, description="验证码")
     */
    public function changemobile()
    {
        $user = $this->auth->getUser();
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
     * 第三方登录
     *
     * @ApiMethod (POST)
     * @ApiParams (name="platform", type="string", required=true, description="平台名称")
     * @ApiParams (name="code", type="string", required=true, description="Code码")
     */
    public function third()
    {
        $url = url('user/index');
        $platform = $this->request->post("platform");
        $code = $this->request->post("code");
        $config = get_addon_config('third');
        if (!$config || !isset($config[$platform])) {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定会员
        $result = $app->{$platform}->getUserInfo(['code' => $code]);
        if ($result) {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret) {
                $data = [
                    'userinfo'  => $this->auth->getUserinfo(),
                    'thirdinfo' => $result
                ];
                $this->success(__('Logged in successful'), $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }

    /**
     * 重置密码
     *
     * @ApiMethod (POST)
     * @ApiParams (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams (name="newpassword", type="string", required=true, description="新密码")
     * @ApiParams (name="captcha", type="string", required=true, description="验证码")
     */
    public function resetpwd()
    {
        $type = $this->request->post("type", "mobile");
        $mobile = $this->request->post("mobile");
        $email = $this->request->post("email");
        $newpassword = $this->request->post("newpassword");
        $captcha = $this->request->post("captcha");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        //验证Token
        if (!Validate::make()->check(['newpassword' => $newpassword], ['newpassword' => 'require|regex:\S{6,30}'])) {
            $this->error(__('Password must be 6 to 30 characters'));
        }
        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'resetpwd');
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 微信小程序授权登录
     */
    public function minilogin()
    {
        $code     = $this->request->post('code');
        $userInfo = $this->request->post('user_info');

        if (!$code) {
            $this->error(__('Invalid parameters'));
        }

        // 解析用户信息
        $userInfo = json_decode(htmlspecialchars_decode($userInfo), true);

        // 获取微信配置
        $wxapp = db("wxapp")->where(['wxapp_id' => 1])->find();
        if (!$wxapp) {
            $this->error('微信配置不存在');
        }

        // 通过 code 换取 session_key 和 openid
        $WxUser = new WxUser($wxapp['app_id'], $wxapp['app_secret']);
        $session = $WxUser->sessionKey($code);

        Log::info("=== mini login session ===");
        Log::info($session);

        if (empty($session) || empty($session['openid'])) {
            $this->error('微信授权失败，请重试');
        }

        $miniopenid = $session['openid'];
        $unionid    = isset($session['unionid']) ? $session['unionid'] : '';

        // 优先按 mini_openid 查找用户（最准确）
        $user = \app\common\model\User::getByMiniOpenid($miniopenid);

        // 未找到则按 unionid 查找（同一微信主体下多端通用）
        if (!$user && $unionid) {
            $user = \app\common\model\User::getByUnionid($unionid);
        }

        if ($user) {
            // 已注册用户：同步更新微信信息后直接登录
            $updateData = [
                'nickname'    => $userInfo['nickName'] ?? $user->nickname,
                'avatar'      => $userInfo['avatarUrl'] ?? $user->avatar,
                'mini_openid' => $miniopenid,
                'unionid'     => $unionid,
            ];
            $user->save($updateData);

            $ret = $this->auth->direct($user->id);
            if ($ret) {
                $data = $this->auth->getUserinfo();
                $this->success("登录成功", ['token' => $data['token'], 'userinfo' => $data]);
            } else {
                $this->error($this->auth->getError());
            }
        } else {
            // 新用户：注册并存入 fy_user
            $info = [
                'mini_openid' => $miniopenid,
                'unionid'     => $unionid,
                'nickname'    => $userInfo['nickName'] ?? '',
                'avatar'      => $userInfo['avatarUrl'] ?? '',
            ];

            $ret = $this->auth->miniregister($info);
            if ($ret) {
                $data = $this->auth->getUserinfo();
                $this->success("注册成功", ['token' => $data['token'], 'userinfo' => $data]);
            } else {
                $this->error($this->auth->getError());
            }
        }
    }

    /**
     * 获取当前登录用户信息
     * GET /user/info
     */
    public function info()
    {
        if (!$this->auth->isLogin()) {
            $this->error(__('Please login first'), null, 401);
        }

        $userinfo = $this->auth->getUserinfo();
        $userinfo['avatar'] = cdnurl($userinfo['avatar'], true);

        $this->success('', $userinfo);
    }

    /**
     * 微信手机号获取（小程序 button open-type="getPhoneNumber"）
     * POST /user/getPhone
     * @param phone  手机号
     */
    public function getPhone()
    {
        if (!$this->auth->isLogin()) {
            $this->error(__('Please login first'), null, 401);
        }

        $phone = $this->request->post('phone', '');
        if (!$phone || !preg_match('/^1\d{10}$/', $phone)) {
            $this->error('手机号格式不正确');
        }

        $user = $this->auth->getUser();
        $user->mobile = $phone;
        $user->save();

        $this->success('手机号绑定成功', ['phone' => $phone]);
    }

    /**
     * 经纪人账号密码登录
     * POST /user/agentLogin
     * @param phone    手机号
     * @param password 密码（明文）
     */
    public function agentLogin()
    {
        $phone    = $this->request->post('phone', '');
        $password = $this->request->post('password', '');

        if (!$phone || !$password) {
            $this->error('请填写手机号和密码');
        }

        // 查找经纪人
        $agent = \app\common\model\Agent::where('phone', $phone)
            ->where('status', 1)
            ->find();
        if (!$agent) {
            $this->error('经纪人账号不存在或已被禁用');
        }

        // 验证密码：md5(md5(password) . salt)
        $agentUser = \app\common\model\User::where('agent_id', $agent->id)->find();
        if (!$agentUser) {
            $this->error('经纪人账号未绑定用户，请联系管理员');
        }
        $encryptedPwd = md5(md5($password) . $agentUser['salt']);
        if ($agentUser['password'] !== $encryptedPwd) {
            $this->error('密码错误');
        }

        // 执行登录
        $ret = $this->auth->direct($agentUser->id);
        if ($ret) {
            $data = $this->auth->getUserinfo();
            $data['agent'] = [
                'id'           => $agent['id'],
                'name'         => $agent['name'],
                'avatar'       => cdnurl($agent['avatar'], true),
                'phone'        => $agent['phone'],
                'house_count'  => $agent['house_count'],
                'customer_auth' => $agent['customer_auth'],
                'house_auth'    => $agent['house_auth'],
            ];
            $this->success('登录成功', [
                'token'    => $data['token'],
                'userinfo' => $data,
            ]);
        } else {
            $this->error($this->auth->getError());
        }
    }

}
