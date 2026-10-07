<?php
namespace app\admin\controller;

use app\common\controller\Backend;
use app\api\model\ProductCategory as CategoryModel;

/**
 * 货品分类
 */
class ProductCategory extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new CategoryModel();
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model->where($where)->count();
            $list  = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                'total' => $total,
                'rows'  => $list,
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params['name'])) return $this->error('分类名不能为空');
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
        $ids = explode(',', $ids);
        $count = 0;
        foreach ($ids as $id) {
            $hasGoods = \think\Db::name('goods')->where('category_id', $id)->count();
            if ($hasGoods > 0) {
                // 有关联货盘，改为停用
                $this->model->where('id', $id)->update(['status' => 0]);
            } else {
                $this->model->where('id', $id)->delete();
            }
            $count++;
        }
        return $this->success('已处理 ' . $count . ' 条（有关联货盘的已停用）');
    }
}
