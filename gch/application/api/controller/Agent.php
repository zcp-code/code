<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 经纪人接口
 */
class Agent extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 经纪人房源管理列表 (list)
     * GET /api/agent/house/list
     */
    public function houses()
    {
        $page     = max(1, intval($this->request->get('page', 1)));
        $pageSize = min(50, max(1, intval($this->request->get('page_size', 10))));

        $agent = model("agent")->where('user_id', $this->auth->id)->find();
        if (!$agent) {
            $this->error('经纪人不存在');
        }
        $agentId = $agent['id'];

        $where = ['agent_id' => $agentId];

        $list = \app\common\model\House::where($where)
            ->order('createtime', 'desc')
            ->page($page, $pageSize)
            ->select();

        $total = \app\common\model\House::where($where)->count();

        // 收集所有 community_id 并批量查询
        $communityIds = [];
        foreach ($list as $row) {
            if (!empty($row['community_id'])) {
                $communityIds[] = $row['community_id'];
            }
        }
        $communityMap = [];
        if (!empty($communityIds)) {
            $communities = \app\common\model\Community::where('id', 'in', array_unique($communityIds))->column('name', 'id');
            $communityMap = $communities ?: [];
        }

        foreach ($list as &$row) {
            if ($row['cover_image']) {
                $row['cover_image'] = cdnurl($row['cover_image'], true);
            }
            // 附加小区名称
            $row['community_name'] = $communityMap[$row['community_id']] ?? '';
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
     * 经纪人信息（从数据库重新拉取，供下拉刷新更新缓存）
     * GET /api/agent/info
     */
    public function info()
    {
        $agent = model("agent")->where('user_id', $this->auth->id)->find();
        if (!$agent) {
            $this->error('经纪人不存在');
        }

        $data = $agent->toArray();
        // 移除敏感字段
        unset($data['password'], $data['salt'], $data['token']);
        // 头像转为完整 URL
        if (!empty($data['avatar'])) {
            $data['avatar'] = cdnurl($data['avatar'], true);
        }

        $this->success('', $data);
    }

    /**
     * 经纪人客户管理列表
     * GET /api/agent/customers
     */
    public function customers()
    {
        $page     = max(1, intval($this->request->get('page', 1)));
        $pageSize = min(50, max(1, intval($this->request->get('limit', 10))));

        $agent = model("agent")->where('user_id', $this->auth->id)->find();
        if (!$agent) {
            $this->error('经纪人不存在');
        }
        $agentId = $agent['id'];

        $where = ['agent_id' => $agentId];

        $list = \app\common\model\Customer::where($where)
            ->order('createtime', 'desc')
            ->page($page, $pageSize)
            ->select();

        $total = \app\common\model\Customer::where($where)->count();

        // 统一前端字段名
        foreach ($list as &$row) {
            $row['userName']    = $row['name'];
            $row['userPhone']   = $row['phone'];
            $row['entrustType'] = $row['type'];
        }

        $this->success('', [
            'list'       => $list,
            'total'      => $total,
            'page'       => $page,
            'page_size'  => $pageSize,
            'page_count' => ceil($total / $pageSize),
        ]);
    }
}

