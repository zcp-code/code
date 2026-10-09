<?php
namespace app\admin\controller;

use app\common\controller\Backend;
use app\api\model\Goods as GoodsModel;

/**
 * 货盘管理
 */
class Goods extends Backend
{
    protected $model = null;
    // 指定搜索字段:join 多张表都有 status 列,必须带别名
    protected $searchFields = 'g.id,g.name';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new GoodsModel();
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            // buildparams 返回 $where 是 Closure 不能当数组用 → 自己构造 where(join 多表时必须带别名)
            $status = $this->request->param('status', '');
            $sort   = $this->request->get('sort', 'id');
            $order  = $this->request->get('order', 'DESC');
            $offset = max(0, $this->request->get('offset/d', 0));
            $limit  = max(0, $this->request->get('limit/d', 20));

            $where = [];
            if ($status !== '' && $status !== null) {
                $where['g.status'] = (int)$status;   // ← 带 g. 别名,避免 join 后 ambiguous
            }

            $alias = 'g';
            $join  = [
                ['shop s', 's.id = g.shop_id', 'LEFT'],
                ['wholesaler w', 'w.id = g.wholesaler_id', 'LEFT'],
                ['product_category c', 'c.id = g.category_id', 'LEFT'],
            ];
            $field = 'g.*, s.name as shop_name, w.real_name as wholesaler_name, c.name as category_name';

            $total = $this->model->alias($alias)->join($join)->where($where)->count();
            $list  = $this->model->alias($alias)->join($join)
                ->where($where)->order($sort, $order)->limit($offset, $limit)
                ->field($field)->select();

            foreach ($list as &$g) {
                $g->available = max(0, $g->total_stock - $g->reserved_quantity);
            }
            return json(['total' => $total, 'rows' => $list]);
        }
        return $this->view->fetch();
    }

    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) return $this->error('记录不存在');

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            try {
                $row->save($params);
            } catch (\Exception $e) {
                return $this->error('保存失败：' . $e->getMessage());
            }
            return $this->success('保存成功');
        }
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }

    /**
     * 强制下架
     */
    public function status($ids = '')
    {
        $row = $this->model->get($ids);
        if (!$row) return $this->error('记录不存在');

        // 取消所有待确认预订
        \think\Db::name('reservation')
            ->where('goods_id', $ids)
            ->where('status', 'pending')
            ->update([
                'status'        => 'cancelled',
                'cancel_time'   => time(),
                'cancel_role'   => 'admin',
                'cancel_reason' => '管理员强制下架',
            ]);

        $row->status = 0;
        $row->save();
        return $this->success('已强制下架并取消待确认预订');
    }

    public function del($ids = '')
    {
        $has = \think\Db::name('reservation')->where('goods_id', 'in', $ids)->count();
        if ($has > 0) {
            return $this->error('存在关联预订，无法删除');
        }
        $this->model->where('id', 'in', $ids)->delete();
        \think\Db::name('goods_image')->where('goods_id', 'in', $ids)->delete();
        return $this->success('删除成功');
    }
}
