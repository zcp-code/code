<?php
namespace app\api\model;

use extend\gch\Password;
use think\Model;

class Wholesaler extends Model
{
    protected $name = 'wholesaler';
    protected $pk = 'id';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 账号密码登录
     */
    public static function login($account, $password)
    {
        $wh = self::where('account', $account)->find();
        if (!$wh) return [false, null, '账号不存在'];
        if ($wh->status != 1) return [false, null, '账号已停用'];
        if (!Password::verify($password, $wh->password)) {
            self::where('id', $wh->id)->setInc('loginfailure');
            return [false, null, '密码错误'];
        }
        $wh->loginfailure = 0;
        $wh->last_login_time = time();
        $wh->last_login_ip = request()->ip();
        $wh->save();
        return [true, $wh, 'ok'];
    }

    public function changePassword($oldPwd, $newPwd)
    {
        if (!Password::verify($oldPwd, $this->password)) {
            return [false, '原密码错误'];
        }
        $this->password = Password::hash($newPwd);
        $this->save();
        return [true, 'ok'];
    }

    /**
     * 关联店铺
     */
    public function shop()
    {
        return $this->belongsTo('Shop', 'shop_id', 'id');
    }
}
