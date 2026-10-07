<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 客户管理接口（经纪人端）
 */
class Customer extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 客户列表
     * GET /api/customer/list
     */
    public function list()
    {
        $page     = max(1, intval($this->request->get('page', 1)));
        $pageSize = min(50, max(1, intval($this->request->get('limit', 10))));

        $agentId = $this->getAgentId();
        if (!$agentId) {
            $this->error('经纪人不存在');
        }

        $where = ['agent_id' => $agentId];

        $list = \app\common\model\Customer::where($where)
            ->order('createtime', 'desc')
            ->page($page, $pageSize)
            ->select();

        $total = \app\common\model\Customer::where($where)->count();

        foreach ($list as &$row) {
            $row['userName']      = $row['name'];
            $row['userPhone']     = $row['phone'];
            $row['entrustType']   = $row['type'];
            $row['communityName'] = $row['community_name'] ?? '';
        }

        $this->success('', [
            'list'       => $list,
            'total'      => $total,
            'page'       => $page,
            'page_size'  => $pageSize,
            'page_count' => ceil($total / $pageSize),
        ]);
    }

    /**
     * 新增客户
     * POST /api/customer/add
     */
    public function add()
    {
        $agentId = $this->getAgentId();
        if (!$agentId) {
            $this->error('经纪人不存在');
        }

        $userName    = $this->request->post('userName', '');
        $userPhone   = $this->request->post('userPhone', '');
        $entrustType = $this->request->post('entrustType', '');
        $remark      = $this->request->post('remark', '');

        if (empty($userName) || empty($userPhone)) {
            $this->error('姓名和手机号不能为空');
        }

        $phone = \app\common\model\Customer::where([
            'agent_id' => $agentId,
            'phone'    => $userPhone,
        ])->find();

        if ($phone) {
            $this->error('该手机号已存在');
        }

        $data = [
            'agent_id'       => $agentId,
            'agent_user_id'  => $this->auth->id,
            'name'           => $userName,
            'phone'          => $userPhone,
            'type'           => $entrustType,
            'remark'         => $remark,
        ];

        \app\common\model\Customer::create($data);

        $this->success('添加成功');
    }

    /**
     * 编辑客户
     * POST /api/customer/edit
     */
    public function edit()
    {
        $agentId = $this->getAgentId();
        if (!$agentId) {
            $this->error('经纪人不存在');
        }

        $id            = intval($this->request->post('id'));
        $userName      = $this->request->post('userName', '');
        $userPhone     = $this->request->post('userPhone', '');
        $entrustType   = $this->request->post('entrustType', '');
        $remark        = $this->request->post('remark', '');

        if (!$id) {
            $this->error('缺少客户ID');
        }
        if (empty($userName) || empty($userPhone)) {
            $this->error('姓名和手机号不能为空');
        }

        $customer = \app\common\model\Customer::where([
            'id'       => $id,
            'agent_id' => $agentId,
        ])->find();

        if (!$customer) {
            $this->error('客户不存在');
        }

        // 检查手机号是否与其他客户重复
        $dup = \app\common\model\Customer::where([
            'agent_id' => $agentId,
            'phone'    => $userPhone,
        ])->where('id', '<>', $id)->find();

        if ($dup) {
            $this->error('该手机号已被其他客户使用');
        }

        $log = [
            'customer_id'      => $id,
            'agent_id'         => $customer['agent_id'],
            'agent_user_id'    => $customer['agent_user_id'],
            'user_id'          => $customer['user_id'],
            'name'             => $customer['name'],
            'phone'            => $customer['phone'],
            'type'             => $customer['type'],
            'remark'           => $customer['remark'],

        ];
        model("customerlog")->create($log);

        $customer->save([
            'name'           => $userName,
            'phone'          => $userPhone,
            'type'           => $entrustType,
            'remark'         => $remark,
        ]);

        $this->success('编辑成功');
    }

    /**
     * 删除客户
     * POST /api/customer/delete
     */
    public function delete()
    {
        $agentId = $this->getAgentId();
        if (!$agentId) {
            $this->error('经纪人不存在');
        }

        $id = intval($this->request->post('id'));

        if (!$id) {
            $this->error('缺少客户ID');
        }

        $customer = \app\common\model\Customer::where([
            'id'       => $id,
            'agent_id' => $agentId,
        ])->find();

        if (!$customer) {
            $this->error('客户不存在');
        }

        $customer->delete();

        $this->success('删除成功');
    }

    /**
     * 获取当前登录用户的经纪人ID
     */
    private function getAgentId()
    {
        $agent = model('agent')->where('user_id', $this->auth->id)->find();
        return $agent ? $agent['id'] : 0;
    }
}
