<?php
namespace app\admin\controller;

use app\common\controller\Backend;
use app\api\model\Wholesaler as WholesalerModel;
use app\api\model\Shop as ShopModel;
use extend\gch\Password;

/**
 * 批发商管理
 */
class Wholesaler extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new WholesalerModel();
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $join = [
                ['shop s', 's.id = w.shop_id', 'LEFT'],
            ];
            $total = $this->model->alias('w')->join($join)->where($where)->count();
            $list  = $this->model->alias('w')->join($join)
                ->where($where)
                ->order($sort, $order)->limit($offset, $limit)
                ->field('w.*, s.name as shop_name')
                ->select();
            foreach ($list as &$w) {
                if ($w->last_login_time) {
                    $w->last_login_time = date('Y-m-d H:i:s', $w->last_login_time);
                }
            }
            return json(['total' => $total, 'rows' => $list]);
        }
        return $this->view->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params['account'])) return $this->error('账号不能为空');
            if (empty($params['password'])) {
                $params['password'] = Password::generate(10);
            } else {
                if (strlen($params['password']) < 8) return $this->error('密码至少 8 位');
                $params['password'] = Password::hash($params['password']);
            }
            if (empty($params['shop_id'])) return $this->error('请选择店铺');
            try {
                $this->model->create($params);
            } catch (\Exception $e) {
                return $this->error('保存失败：' . $e->getMessage());
            }
            return $this->success('保存成功');
        }
        $shops = ShopModel::where('status', 1)->select();
        $this->view->assign('shops', $shops);
        return $this->view->fetch();
    }

    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) return $this->error('记录不存在');

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            unset($params['password']);
            try {
                $row->save($params);
            } catch (\Exception $e) {
                return $this->error('保存失败：' . $e->getMessage());
            }
            return $this->success('保存成功');
        }
        $shops = ShopModel::where('status', 1)->select();
        $this->view->assign(['row' => $row, 'shops' => $shops]);
        return $this->view->fetch();
    }

    public function del($ids = '')
    {
        $this->model->where('id', 'in', $ids)->delete();
        return $this->success('删除成功');
    }

    public function resetPwd($ids = '')
    {
        $newPwd = Password::generate(10);
        $hash = Password::hash($newPwd);
        $this->model->where('id', $ids)->update(['password' => $hash]);
        return $this->success('密码已重置为：' . $newPwd);
    }

    public function status($ids = '')
    {
        $row = $this->model->get($ids);
        if (!$row) return $this->error('记录不存在');
        $row->status = $row->status == 1 ? 0 : 1;
        $row->save();
        return $this->success('操作成功');
    }
}
