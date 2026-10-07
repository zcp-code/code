<?php
namespace app\admin\controller;

use app\common\controller\Backend;
use app\api\model\Reservation as ReservationModel;

/**
 * 预订管理
 */
class Reservation extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new ReservationModel();
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $alias = 'r';
            $join = [
                ['shop s', 's.id = r.shop_id', 'LEFT'],
                ['buyer b', 'b.id = r.buyer_id', 'LEFT'],
                ['wholesaler w', 'w.id = r.wholesaler_id', 'LEFT'],
            ];
            $field = 'r.*, s.name as shop_name, b.real_name as buyer_name, w.real_name as wholesaler_name';

            $total = $this->model->alias($alias)->join($join)->where($where)->count();
            $list  = $this->model->alias($alias)->join($join)
                ->where($where)->order($sort, $order)->limit($offset, $limit)
                ->field($field)->select();
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

    public function del($ids = '')
    {
        $this->model->where('id', 'in', $ids)->delete();
        return $this->success('删除成功');
    }

    /**
     * 导出 Excel（简化版 CSV）
     */
    public function export()
    {
        $list = $this->model->alias('r')
            ->join(['shop s', 's.id = r.shop_id', 'LEFT'])
            ->join(['buyer b', 'b.id = r.buyer_id', 'LEFT'])
            ->join(['wholesaler w', 'w.id = r.wholesaler_id', 'LEFT'])
            ->order('r.id desc')
            ->limit(1000)
            ->field('r.reservation_no, r.goods_name, r.price, r.unit, r.quantity, s.name as shop_name, b.real_name as buyer_name, w.real_name as wholesaler_name, r.status, r.createtime')
            ->select();

        $filename = 'reservation_' . date('YmdHis') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // BOM
        fputcsv($out, ['预订编号', '货品名', '单价', '单位', '数量', '店铺', '采购商', '批发商', '状态', '创建时间']);
        foreach ($list as $r) {
            fputcsv($out, [
                $r['reservation_no'],
                $r['goods_name'],
                $r['price'],
                $r['unit'],
                $r['quantity'],
                $r['shop_name'],
                $r['buyer_name'],
                $r['wholesaler_name'],
                $r['status'],
                date('Y-m-d H:i:s', $r['createtime']),
            ]);
        }
        fclose($out);
        exit;
    }
}
