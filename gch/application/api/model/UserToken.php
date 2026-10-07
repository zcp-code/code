<?php
namespace app\api\model;

use think\Model;

class UserToken extends Model
{
    protected $name = 'user_token';
    protected $pk = 'id';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = false;
}
