<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 客户管理
 *
 * @icon fa fa-circle-o
 */
class Customerlog extends Backend
{

    /**
     * Customerlog模型对象
     * @var \app\common\model\Customerlog
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\Customerlog;

    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index($ids=null)
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            if ($ids) {
                $where1['customer_id'] = $ids;
            }
            $list = $this->model
//                    ->with(['customer'])
                    ->with(['agent','user'])
                    ->where($where)
                    ->where($where1)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                
//                $row->getRelation('customer')->visible(['name','phone']);
                $row->visible(['id', 'name', 'type', 'phone', 'agent_id', 'user_id', 'remark', 'createtime', 'updatetime']);
                $row->visible(['agent','user']);
                $row->getRelation('agent')->visible(['name']);
                $row->getRelation('user')->visible(['mobile', 'nickname']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        $this->view->assign("ids", $ids);
        return $this->view->fetch();
    }

}
