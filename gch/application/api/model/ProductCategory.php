<?php
namespace app\api\model;

use think\Model;

class ProductCategory extends Model
{
    protected $name = 'product_category';
    protected $pk = 'id';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 列表（含每个分类下的在售货盘数）
     */
    public static function listWithCount()
    {
        $list = self::where('status', 1)->order('sort asc, id asc')->select();
        foreach ($list as &$cat) {
            $count = \think\Db::name('goods')
                ->where('category_id', $cat->id)
                ->where('status', 1)
                ->count();
            $cat->count = $count;
        }
        return $list;
    }
}
