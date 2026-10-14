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
     * 列表（只返回有在售货品的分类 + 附带每个分类下的货盘数 + 均价）
     */
    public static function listWithCount()
    {
        $list = self::alias('c')
            ->field('c.*, COUNT(g.id) AS count, AVG(g.price) AS avg_price, MIN(g.price) AS min_price, MAX(g.price) AS max_price')
            ->join('fy_goods g', 'g.category_id = c.id AND g.status = 1', 'LEFT')
            ->where('c.status', 1)
            ->group('c.id')
            ->having('count > 0')
            ->order('c.sort asc, c.id asc')
            ->select();
        return $list;
    }
}
