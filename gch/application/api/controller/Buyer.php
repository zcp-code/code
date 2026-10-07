<?php
namespace app\api\controller;

use app\api\model\Buyer as BuyerModel;
use extend\gch\Token;

/**
 * 采购商
 * POST /api/buyer/login
 * POST /api/buyer/changePwd
 * POST /api/buyer/logout
 * GET  /api/buyer/profile
 */
class Buyer extends ApiBase
{
    protected $requireLogin = false;
    protected $requireRole  = null;

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 登录
     */
    public function login()
    {
        if ($this->request->isPost() !== true) {
            return $this->error('请用 POST 请求');
        }
        $account  = $this->request->param('account', '');
        $password = $this->request->param('password', '');

        if (empty($account) || empty($password)) {
            return $this->error('账号或密码不能为空');
        }

        list($ok, $buyer, $msg) = BuyerModel::login($account, $password);
        if (!$ok) {
            return $this->error($msg, 400);
        }

        $tk = Token::create('buyer', $buyer->id, $this->clientIp());
        return $this->success([
            'token'  => $tk['token'],
            'expire' => $tk['expire_time'],
            'role'   => 'buyer',
            'buyer'  => [
                'id'        => $buyer->id,
                'account'   => $buyer->account,
                'real_name' => $buyer->real_name,
            ],
        ], '登录成功');
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        $token = $this->request->header('Token', '');
        if ($token) Token::destroy($token);
        return $this->success(null, '已退出');
    }

    /**
     * 当前信息
     */
    public function profile()
    {
        $this->requireLogin = true;
        $this->requireRole  = 'buyer';
        $this->_initialize();

        $buyer = BuyerModel::get($this->user['user_id']);
        if (!$buyer) return $this->error('用户不存在', 401);
        return $this->success([
            'id'        => $buyer->id,
            'account'   => $buyer->account,
            'real_name' => $buyer->real_name,
            'mobile'    => $buyer->mobile,
            'role'      => 'buyer',
        ]);
    }

    /**
     * 修改密码
     */
    public function changePwd()
    {
        $this->requireLogin = true;
        $this->requireRole  = 'buyer';
        $this->_initialize();

        $old = $this->request->param('old_password', '');
        $new = $this->request->param('new_password', '');

        if (empty($old) || empty($new)) return $this->error('原密码和新密码必填');
        if (strlen($new) < 8 || strlen($new) > 20) return $this->error('新密码长度需 8-20 位');

        $buyer = BuyerModel::get($this->user['user_id']);
        if (!$buyer) return $this->error('用户不存在', 401);

        list($ok, $msg) = $buyer->changePassword($old, $new);
        if (!$ok) return $this->error($msg);

        // 修改密码后销毁当前 Token，强制重新登录
        $token = $this->request->header('Token', '');
        if ($token) Token::destroy($token);
        return $this->success(null, '密码已修改，请重新登录');
    }
}
