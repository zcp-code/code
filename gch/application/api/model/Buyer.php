<?php
namespace app\api\model;

use extend\gch\Password;
use think\Model;

class Buyer extends Model
{
    protected $name = 'buyer';
    protected $pk = 'id';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 账号密码登录
     */
    public static function login($account, $password)
    {
        $buyer = self::where('account', $account)->find();
        if (!$buyer) {
            return [false, null, '账号不存在'];
        }
        if ($buyer->status != 1) {
            return [false, null, '账号已停用'];
        }
        if (!Password::verify($password, $buyer->password)) {
            self::where('id', $buyer->id)->setInc('loginfailure');
            return [false, null, '密码错误'];
        }
        // 登录成功
        $buyer->loginfailure = 0;
        $buyer->last_login_time = time();
        $buyer->last_login_ip = request()->ip();
        $buyer->save();
        return [true, $buyer, 'ok'];
    }

    /**
     * 修改密码
     */
    public function changePassword($oldPwd, $newPwd)
    {
        if (!Password::verify($oldPwd, $this->password)) {
            return [false, '原密码错误'];
        }
        $this->password = Password::hash($newPwd);
        $this->save();
        return [true, 'ok'];
    }
}
