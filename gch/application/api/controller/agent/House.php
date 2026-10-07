<?php

namespace app\api\controller\agent;

use app\common\controller\Api;
use app\common\model\House as HouseModel;
use app\common\model\Agent as AgentModel;

/**
 * 经纪人房源管理接口
 */
class House extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 经纪人房源列表
     * GET /api/agent/house/list
     */
    public function list()
    {
        $page     = max(1, intval($this->request->get('page', 1)));
        $pageSize = min(50, max(1, intval($this->request->get('page_size', 10))));

        // 获取当前经纪人
        $agent = AgentModel::where('user_id', $this->auth->id)->find();
        if (!$agent) {
            $this->error('经纪人不存在');
        }

        $where = ['agent_id' => $agent['id']];

        $list = HouseModel::where($where)
            ->order('createtime', 'desc')
            ->page($page, $pageSize)
            ->select();

        $total = HouseModel::where($where)->count();

        foreach ($list as &$row) {
            $this->formatHouse($row);
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
     * 房源详情
     * GET /api/agent/house/detail
     */
    public function detail()
    {
        $id = $this->request->get('id');
        if (!$id) {
            $this->error('缺少房源ID');
        }

        // 获取当前经纪人
        $agent = AgentModel::where('user_id', $this->auth->id)->find();
        if (!$agent) {
            $this->error('经纪人不存在');
        }

        $house = HouseModel::where(['id' => $id, 'agent_id' => $agent['id']])->find();
        if (!$house) {
            $this->error('房源不存在或无权限');
        }

        $this->formatHouse($house);

        $this->success('', $house);
    }

    /**
     * 添加房源
     * POST /api/agent/house/add
     */
    public function add()
    {
        $data = $this->request->post();

        if (empty($data['title'])) {
            $this->error('请输入房源名称');
        }
        if (empty($data['type']) || !in_array($data['type'], ['sale', 'rent', 'new'])) {
            $this->error('请选择房源类型');
        }
        if (empty($data['community_name'])) {
            $this->error('请输入小区名称');
        }

        // 获取当前经纪人
        $agent = AgentModel::where('user_id', $this->auth->id)->find();
        if (!$agent) {
            $this->error('经纪人不存在');
        }

        $data['agent_id'] = $agent['id'];
        $data['agent_user_id'] = $this->auth->id;
        $data['createtime'] = time();
        $data['updatetime'] = time();

        $house = new HouseModel();
        $result = $house->allowField(true)->save($data);
        if ($result) {
            $this->success('添加成功', ['id' => $house->id]);
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 编辑房源
     * POST /api/agent/house/edit
     */
    public function edit()
    {
        $data = $this->request->post();

        if (empty($data['id'])) {
            $this->error('缺少房源ID');
        }

        // 获取当前经纪人
        $agent = AgentModel::where('user_id', $this->auth->id)->find();
        if (!$agent) {
            $this->error('经纪人不存在');
        }

        $house = HouseModel::where(['id' => $data['id'], 'agent_id' => $agent['id']])->find();
        if (!$house) {
            $this->error('房源不存在或无权限');
        }

        $data['updatetime'] = time();

        $result = $house->allowField(true)->save($data);
        if ($result !== false) {
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

    /**
     * 更新房源状态（上下架）
     * POST /api/agent/house/updateStatus
     */
    public function updateStatus()
    {
        $id = $this->request->post('id');
        $status = $this->request->post('status');

        if (empty($id)) {
            $this->error('缺少房源ID');
        }

        // 获取当前经纪人
        $agent = AgentModel::where('user_id', $this->auth->id)->find();
        if (!$agent) {
            $this->error('经纪人不存在');
        }

        $house = HouseModel::where(['id' => $id, 'agent_id' => $agent['id']])->find();
        if (!$house) {
            $this->error('房源不存在或无权限');
        }

        // 状态值校验：1=上架, 0=下架
        if (!in_array($status, ['0', '1', 0, 1])) {
            $this->error('状态值非法');
        }

        $house->status = intval($status);
        $house->updatetime = time();
        $result = $house->save();

        if ($result !== false) {
            $this->success($status == 1 ? '上架成功' : '下架成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 删除房源
     * POST /api/agent/house/del
     */
    public function del()
    {
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            $this->error('缺少房源ID');
        }

        // 获取当前经纪人
        $agent = AgentModel::where('user_id', $this->auth->id)->find();
        if (!$agent) {
            $this->error('经纪人不存在');
        }

        $count = HouseModel::where(['agent_id' => $agent['id']])
            ->where('id', 'in', $ids)
            ->delete();

        if ($count) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败或无权限');
        }
    }

    /**
     * 格式化房源数据
     */
    protected function formatHouse(&$row)
    {
        // 封面图
        if ($row['cover_image']) {
            $row['cover_image'] = cdnurl($row['cover_image'], true);
        }

        // 图片列表
        if ($row['images']) {
            $images = is_string($row['images']) ? json_decode($row['images'], true) : $row['images'];
            if (is_array($images)) {
                foreach ($images as &$img) {
                    $img = cdnurl($img, true);
                }
                $row['images'] = $images;
            }
        }

        // 配套设施
        if ($row['facilities']) {
            $facilities = is_string($row['facilities']) ? json_decode($row['facilities'], true) : $row['facilities'];
            $row['facilities'] = is_array($facilities) ? $facilities : [];
        }

        // 类型文本
        $typeList = ['sale' => '二手房', 'rent' => '租房', 'new' => '新房'];
        $row['type_text'] = $typeList[$row['type']] ?? '';

        // 价格展示
        if ($row['type'] === 'rent') {
            $row['price_display'] = floatval($row['monthly_rent']) > 0 ? floatval($row['monthly_rent']) . '元/月' : '面议';
        } else {
            $row['price_display'] = floatval($row['price']) > 0 ? floatval($row['price']) . '万' : '面议';
        }

        // 户型面积组合
        $row['room_info'] = $row['house_type'] . ' | ' . floatval($row['area']) . '㎡';
    }
}
