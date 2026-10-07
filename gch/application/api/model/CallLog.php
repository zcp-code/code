<?php
namespace app\api\model;

use think\Model;

class CallLog extends Model
{
    protected $name = 'call_log';
    protected $pk = 'id';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = false;

    /**
     * 记录拨号
     */
    public static function record($role, $userId, $shopId, $wholesalerId, $phone)
    {
        return self::create([
            'caller_role'   => $role,
            'caller_id'     => $userId,
            'shop_id'       => $shopId,
            'wholesaler_id' => $wholesalerId,
            'phone'         => $phone,
            'ip'            => request()->ip(),
        ]);
    }
}
