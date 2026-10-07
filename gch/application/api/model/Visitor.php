<?php
namespace app\api\model;

use think\Model;

class Visitor extends Model
{
    protected $name = 'visitor';
    protected $pk = 'id';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 根据 openid 查找
     */
    public static function findByOpenid($openid)
    {
        return self::where('openid', $openid)->find();
    }
}
