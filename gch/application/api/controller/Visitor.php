<?php
namespace app\api\controller;

use app\api\model\Visitor as VisitorModel;
use extend\gch\Token;

/**
 * 游客相关操作
 */
class Visitor extends ApiBase
{
    protected $requireRole = 'visitor';

    /**
     * 当前游客信息
     */
    public function profile()
    {
        $visitor = VisitorModel::get($this->user['user_id']);
        if (!$visitor) {
            return $this->error('用户不存在', 401);
        }
        return $this->success([
            'id'       => $visitor->id,
            'nickname' => $visitor->nickname,
            'avatar'   => $visitor->avatar,
            'role'     => 'visitor',
        ]);
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        $token = $this->request->header('Token', '');
        if ($token) {
            Token::destroy($token);
        }
        return $this->success(null, '已退出');
    }
}
