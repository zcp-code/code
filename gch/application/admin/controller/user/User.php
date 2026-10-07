<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\common\library\Auth;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    protected $relationSearch = true;
    protected $searchFields = 'id,username,nickname';

    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\User;
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model
                ->with('group')
                ->where($where)
                ->where('group_id', 1)
                ->order($sort, $order)
                ->paginate($limit);
            foreach ($list as $k => $v) {
                $v->avatar = $v->avatar ? cdnurl($v->avatar, true) : letter_avatar($v->nickname);
                $v->hidden(['password', 'salt']);
            }
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $this->token();
        }
        return parent::add();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        if ($this->request->isPost()) {
            $this->token();
        }
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));
        return parent::edit($ids);
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        Auth::instance()->delete($row['id']);
        $this->success();
    }

    /**
     * 设置/取消经纪人
     */
    public function toggleAgent($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error('会员不存在');
        }

        if ($row['is_agent'] == 1) {
            // 取消经纪人
            $row->is_agent = 0;
            // $row->agent_id = 0;
            $row->save();
            $this->success('已取消经纪人身份');
        } else {
            // 设置为经纪人：查找或创建经纪人记录
            // $agentModel = model('Agent');
            // $agent = $agentModel->where('phone', $row['phone'])->find();
            // if (!$agent) {
            //     $agent = $agentModel->create([
            //         'name'   => $row['nickname'] ?: '未设置',
            //         'avatar' => $row['avatar'] ?: '',
            //         'phone'  => $row['phone'],
            //         'status' => 'normal',
            //     ]);
            // }
            $row->is_agent = 1;
            // $row->agent_id = $agent->id;
            $row->save();
            $this->success('已设置为经纪人');
        }
    }

}
