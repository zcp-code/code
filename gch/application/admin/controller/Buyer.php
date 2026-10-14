<?php
namespace app\admin\controller;

use app\common\controller\Backend;
use app\api\model\Buyer as BuyerModel;
use extend\gch\Password;

/**
 * 采购商管理
 */
class Buyer extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new BuyerModel();
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
            $list  = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)
                ->field('id, account, real_name, mobile, status, last_login_time, createtime, updatetime')->select();
            foreach ($list as &$b) {
                if ($b->last_login_time) {
                    $b->last_login_time = date('Y-m-d H:i:s', $b->last_login_time);
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
            // 不允许通过此接口改密码
            unset($params['password']);
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
     * 账号唯一性校验(FastAdmin 表单 remote 验证用)
     * 入参: account (排除自身 id 用,默认无)
     * 出参: true(可用)/ 错误字符串(已被占用)
     */
    public function checkAccountUnique()
    {
        $account = $this->request->param('account', '');
        $id      = (int)$this->request->param('id', 0);
        if (empty($account)) return $this->success(true);

        $query = $this->model->where('account', $account);
        if ($id > 0) $query->where('id', '<>', $id);
        $exists = $query->find();
        return $exists ? $this->success(false, '该账号已存在') : $this->success(true);
    }

    /**
     * 重置密码
     */
    public function resetPwd($ids = '')
    {
        $newPwd = Password::generate(10);
        $hash = Password::hash($newPwd);
        $this->model->where('id', $ids)->update(['password' => $hash]);
        return $this->success('密码已重置为：' . $newPwd);
    }

    /**
     * 启用/停用
     */
    public function status($ids = '')
    {
        $row = $this->model->get($ids);
        if (!$row) return $this->error('记录不存在');
        $row->status = $row->status == 1 ? 0 : 1;
        $row->save();
        return $this->success('操作成功');
    }
}
