<?php
namespace app\admin\controller;

use app\common\controller\Backend;
use app\api\model\Shop as ShopModel;

/**
 * 店铺管理
 */
class Shop extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new ShopModel();
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            // TAB 状态筛选 — buildparams() 返回的 $where 是 Closure 不能直接改数组,需在外层再包一层追加条件
            $status = $this->request->param('status', '');
            $where = function ($query) use ($where, $status) {
                $where($query);   // 先应用 buildparams 原始条件
                if ($status !== '' && $status !== null && in_array($status, ['0', '1'], true)) {
                    $query->where('status', (int)$status);   // ← tinyint 字段,需强转 int
                }
            };

            $total = $this->model->where($where)->count();
            $list  = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)->select();
            foreach ($list as &$shop) {
                $shop->goods_count = \think\Db::name('goods')->where('shop_id', $shop->id)->count();
            }
            return json(['total' => $total, 'rows' => $list]);
        }
        return $this->view->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params['name'])) return $this->error('店铺名不能为空');
            try {
                $this->model->create($params);
            } catch (\Exception $e) {
                return $this->error('保存失败：' . $e->getMessage());
            }
            return $this->success('保存成功');
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
}
