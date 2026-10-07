<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\House as HouseModel;
use app\common\model\Agent as AgentModel;
use app\common\model\Community as CommunityModel;
use think\Db;

/**
 * 房源接口
 */
class House extends Api
{
    protected $noNeedLogin = ['list', 'detail', 'recommend'];
    protected $noNeedRight = '*';

    /**
     * 新增房源（经纪人登录后操作）
     * POST /house/add
     * @param community_id  小区ID（优先）
     * @param community_name 小区名称（兼容旧版，community_id不存在时使用）
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

        // 设置经纪人ID（从当前登录用户获取）
        $userId = $this->auth->id ?? 0;
        if ($userId) {
            $data['agent_user_id'] = $userId;
        }
        $data['agent_id'] = AgentModel::where('user_id', $userId)->value('id');

        // 核验与配套设施字段处理
        $data['verify_status'] = intval($data['verify_status'] ?? 0);
        // verify_time：兼容日期字符串与时间戳
        $verifyTime = $data['verify_time'] ?? '';
        if ($verifyTime && !is_numeric($verifyTime)) {
            $data['verify_time'] = strtotime($verifyTime) ?: null;
        } elseif (empty($verifyTime)) {
            $data['verify_time'] = null;
        }
        // facilities：兼容数组/字符串/空值，字符串原样保存
        if (!isset($data['facilities']) || $data['facilities'] === '' || $data['facilities'] === null) {
            $data['facilities'] = '[]';
        } elseif (is_array($data['facilities'])) {
            $data['facilities'] = json_encode($data['facilities'], JSON_UNESCAPED_UNICODE);
        } elseif (is_string($data['facilities'])) {
            // ThinkPHP 默认 htmlspecialchars 转义了双引号 → 反转义
            $data['facilities'] = htmlspecialchars_decode($data['facilities']);
        } else {
            $data['facilities'] = '[]';
        }

        $data['publish_time'] = time();
        $data['createtime'] = time();
        $data['updatetime'] = time();

        $house = new HouseModel();
        // 显式赋值确保 facilities 等字段不被 allowField 过滤
        if (isset($data['facilities'])) {
            $house->facilities = $data['facilities'];
        }
        $result = $house->allowField(true)->save($data);
        if ($result) {
            $this->success('添加成功', ['id' => $house->id]);
        } else {
            $this->error('添加失败');
        }
    }

    // house_up 同步字段：编辑时需要同步到 house_up 的业务字段
    protected $houseUpFields = [
        'community_id', 'title', 'type', 'cover_image', 'images',
        'house_type', 'area', 'orientation', 'decoration', 'floor', 'total_floor',
        'has_elevator', 'price', 'unit_price', 'monthly_rent',
        'house_use', 'house_nature', 'co_ownership', 'mortgage',
        'commission_type', 'inner_area', 'house_code', 'house_code_qrcode',
        'verify_status', 'verify_time', 'publish_time',
        'description', 'facilities', 'address',
    ];

    /**
     * 编辑房源（经纪人登录后操作）
     * POST /house/edit
     *
     * 逻辑：
     *  - check_status = 0（未审核）→ 直接更新 house 表
     *  - check_status != 0 → 写入 house_up 表：
     *      与 house 表现有数据对比，仅保存有变更的字段；
     *      已有未审核记录则更新，无则新增；同时 house.check_status 改为 9
     */
    public function edit()
    {
        $data = $this->request->post();

        if (empty($data['id'])) {
            $this->error('缺少房源ID');
        }

        $house = HouseModel::get($data['id']);
        if (!$house) {
            $this->error('房源不存在');
        }

        // 校验归属：只能操作自己的房源
        $userId = $this->auth->id ?? 0;
        $agent = model("agent")->where("user_id", $userId)->find();
        if ($house->agent_id != $agent->id) {
            $this->error('无权限操作');
        }

        // 核验与配套设施字段处理
        $data['verify_status'] = intval($data['verify_status'] ?? 0);
        $verifyTime = $data['verify_time'] ?? '';
        if ($verifyTime && !is_numeric($verifyTime)) {
            $data['verify_time'] = strtotime($verifyTime) ?: null;
        } elseif (empty($verifyTime)) {
            $data['verify_time'] = null;
        }
        if (!isset($data['facilities']) || $data['facilities'] === '' || $data['facilities'] === null) {
            $data['facilities'] = '[]';
        } elseif (is_array($data['facilities'])) {
            $data['facilities'] = json_encode($data['facilities'], JSON_UNESCAPED_UNICODE);
        } elseif (is_string($data['facilities'])) {
            $data['facilities'] = htmlspecialchars_decode($data['facilities']);
        } else {
            $data['facilities'] = '[]';
        }

        $data['updatetime'] = time();

        // 未审核 → 直接更新 house 表
        if ($house->check_status == 0) {
            if (isset($data['facilities'])) {
                $house->facilities = $data['facilities'];
            }
            $result = $house->allowField(true)->save($data);
            if ($result !== false) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
            return;
        }

        // check_status != 0 → 写入 house_up 表，仅保存有变更的字段
        $upData = ['house_id' => $house->id, 'agent_id' => $house->agent_id];
        foreach ($this->houseUpFields as $field) {
            if (array_key_exists($field, $data)) {
                $newVal = $data[$field];
                $oldVal = $house->getData($field);
                // 仅保存与 house 表中值不同的字段
                if ($newVal != $oldVal) {
                    $upData[$field] = $newVal;
                }
            }
        }

        // 没有实际变更 → 不操作
        if (count($upData) <= 2) {
            $this->success('没有检测到数据变更');
            return;
        }

        $houseUpModel = model('Houseup');
        $existing = $houseUpModel->where('house_id', $house->id)->where('check_status', 0)->find();

        if ($existing) {
            $upData['updatetime'] = time();
            $result = $existing->allowField(true)->save($upData);
        } else {
            $result = $houseUpModel->allowField(true)->save($upData);
        }

        if ($result !== false) {
            $house->save(['check_status' => 9, 'updatetime' => time()]);
            $this->success('修改已提交，等待审核');
        } else {
            $this->error('修改提交失败');
        }
    }

    /**
     * 更新房源状态（上下架）
     * POST /house/updateStatus
     */
    public function updateStatus()
    {
        $houseId = intval($this->request->post('houseId'));
        $status = $this->request->post('status');

        if (!$houseId) {
            $this->error('缺少房源ID');
        }

        $house = HouseModel::get($houseId);
        if (!$house) {
            $this->error('房源不存在');
        }

        // 校验归属：只能操作自己的房源
        $userId = $this->auth->id ?? 0;
        $agent = model("agent")->where('user_id', $userId)->find();
        if ($agent && $house['agent_id'] != $agent['id']) {
            $this->error('无权操作该房源');
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
     * 删除房源（经纪人登录后操作）
     * POST /house/delete
     */
    public function delete()
    {
        $houseId = intval($this->request->post('houseId'));

        if (!$houseId) {
            $this->error('缺少房源ID');
        }

        $house = HouseModel::get($houseId);
        if (!$house) {
            $this->error('房源不存在');
        }

        // 校验归属：只能删除自己的房源
        $userId = $this->auth->id ?? 0;
        $agent = model("agent")->where('user_id', $userId)->find();
        if ($agent && $house['agent_id'] != $agent['id']) {
            $this->error('无权删除该房源');
        }

        $result = $house->delete();
        if ($result) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 房源列表
     * GET /house/list
     * @param type      房源类型: sale=二手房, rent=租房, new=新房
     * @param keyword   搜索关键词（标题/小区）
     * @param page      页码
     * @param limit     每页数量
     * @param bedRoom   居室筛选，如：1室,2室,3室,单身公寓
     * @param bathRoom  卫生间筛选，如：1卫,2卫
     * @param priceRange 价格区间筛选，如：15万以下,15-30万,50-80万
     * @param areaRange  面积区间筛选，如：50㎡以下,50-70㎡,70-90㎡
     * @param sortDesc   是否降序排序
     */
    public function list()
    {
        $type      = $this->request->get('type', 'sale');
        $keyword   = $this->request->get('keyWord', '');
        $page      = max(1, intval($this->request->get('page', 1)));
        $pageSize  = min(50, max(1, intval($this->request->get('limit', 10))));
        $bedRooms  = $this->request->get('bedRoom', '');
        $bathRooms = $this->request->get('bathRoom', '');
        $priceRange = $this->request->get('priceRange', '');
        $monthlyRent = $this->request->get('monthlyRent', '');
        $areaRange  = $this->request->get('areaRange', '');
        $sortDesc   = $this->request->get('sortDesc', false);
        $communityId = intval($this->request->get('communityId', 0));

        $where = [
            'type'   => $type,
            'status' => '1',
        ];

        if ($communityId) {
            $where['community_id'] = $communityId;
        }

        if ($keyword) {
            // 同时搜索房源标题、小区名、以及关联的社区表名称
            $communityIds = CommunityModel::where('name', 'like', "%{$keyword}%")->column('id');
            if (!empty($communityIds)) {
                $where[] = function($query) use ($keyword, $communityIds) {
                    $query->where('title', 'like', "%{$keyword}%")
                        ->whereOr('community_id', 'in', $communityIds);
                };
            }
        }

        // 居室筛选（支持多选，如：1室,2室,3室）
        if ($bedRooms) {
            $bedList = is_array($bedRooms) ? $bedRooms : explode(',', $bedRooms);
            $bedList = array_filter($bedList);
            if (!empty($bedList)) {
                // 使用闭包实现 OR 筛选
                $where[] = function($query) use ($bedList) {
                    foreach ($bedList as $bed) {
                        $bed = trim($bed);
                        if ($bed === '单身公寓') {
                            $query->whereOr('house_type', 'like', '%单身公寓%');
                        } else {
                            $query->whereOr('house_type', 'like', '%' . $bed . '%');
                        }
                    }
                };
            }
        }

        // 卫生间筛选（支持多选，如：1卫,2卫）
        if ($bathRooms) {
            $bathList = is_array($bathRooms) ? $bathRooms : explode(',', $bathRooms);
            $bathList = array_filter($bathList);
            if (!empty($bathList)) {
                $where[] = function($query) use ($bathList) {
                    foreach ($bathList as $bath) {
                        preg_match('/(\d+)卫/', trim($bath), $matches);
                        if ($matches) {
                            $num = intval($matches[1]);
                            // 匹配 "X卫" 格式
                            $query->whereOr('house_type', 'like', '%' . $num . '卫%');
                        }
                    }
                };
            }
        }

        // 价格区间筛选（如：15-30万, 50万以下, 100万以上）
        if ($priceRange) {
            $priceList = is_array($priceRange) ? $priceRange : explode(',', $priceRange);
            $priceList = array_filter($priceList);
            if (!empty($priceList)) {
                $where[] = function($query) use ($priceList) {
                    foreach ($priceList as $range) {
                        $range = trim($range);
                        if (preg_match('/^(\d+)-(\d+)万$/', $range, $matches)) {
                            // 15-30万 格式
                            $min = floatval($matches[1]);
                            $max = floatval($matches[2]);
                            $query->whereOr(function($q) use ($min, $max) {
                                $q->where('price', '>=', $min)->where('price', '<=', $max);
                            });
                        } elseif (preg_match('/^(\d+)万以下$/', $range, $matches)) {
                            // 15万以下
                            $max = floatval($matches[1]);
                            $query->whereOr('price', '<=', $max);
                        } elseif (preg_match('/^(\d+)万以上$/', $range, $matches)) {
                            // 100万以上
                            $min = floatval($matches[1]);
                            $query->whereOr('price', '>=', $min);
                        }
                    }
                };
            }
        }

        // 租房价格区间筛选（单位：元/月，如：1000-1500元, 5000元以上）
        if ($monthlyRent) {
            $rentList = is_array($monthlyRent) ? $monthlyRent : explode(',', $monthlyRent);
            $rentList = array_filter($rentList);
            if (!empty($rentList)) {
                $where[] = function($query) use ($rentList) {
                    foreach ($rentList as $range) {
                        $range = trim($range);
                        if (preg_match('/^(\d+)-(\d+)元$/', $range, $matches)) {
                            // 1000-1500元 格式
                            $min = floatval($matches[1]);
                            $max = floatval($matches[2]);
                            $query->whereOr(function($q) use ($min, $max) {
                                $q->where('monthly_rent', '>=', $min)->where('monthly_rent', '<=', $max);
                            });
                        } elseif (preg_match('/^(\d+)元以下$/', $range, $matches)) {
                            // 1000元以下
                            $max = floatval($matches[1]);
                            $query->whereOr('monthly_rent', '<=', $max);
                        } elseif (preg_match('/^(\d+)元以上$/', $range, $matches)) {
                            // 5000元以上
                            $min = floatval($matches[1]);
                            $query->whereOr('monthly_rent', '>=', $min);
                        }
                    }
                };
            }
        }

        // 面积区间筛选（如：50-70㎡, 50㎡以下, 100㎡以上）
        if ($areaRange) {
            $areaList = is_array($areaRange) ? $areaRange : explode(',', $areaRange);
            $areaList = array_filter($areaList);
            if (!empty($areaList)) {
                $where[] = function($query) use ($areaList) {
                    foreach ($areaList as $range) {
                        $range = trim($range);
                        if (preg_match('/^(\d+)-(\d+)㎡$/', $range, $matches)) {
                            // 50-70㎡ 格式
                            $min = floatval($matches[1]);
                            $max = floatval($matches[2]);
                            $query->whereOr(function($q) use ($min, $max) {
                                $q->where('area', '>=', $min)->where('area', '<=', $max);
                            });
                        } elseif (preg_match('/^(\d+)㎡以下$/', $range, $matches)) {
                            // 50㎡以下
                            $max = floatval($matches[1]);
                            $query->whereOr('area', '<=', $max);
                        } elseif (preg_match('/^(\d+)㎡以上$/', $range, $matches)) {
                            // 100㎡以上
                            $min = floatval($matches[1]);
                            $query->whereOr('area', '>=', $min);
                        }
                    }
                };
            }
        }

        // 排序
        $orderField = $sortDesc ? 'price' : 'createtime';
        $orderType  = $sortDesc ? 'desc' : 'asc';

        $list = HouseModel::where($where)
            ->where('check_status','in', [1,9])
            ->order('weigh', 'desc')
            ->order($orderField, $orderType)
            ->page($page, $pageSize)
            ->select();

        $total = HouseModel::where($where)->count();

        foreach ($list as &$row) {
            $this->formatHouse($row);
        }

        $this->success('', [
            'list'      => $list,
            'total'     => $total,
            'page'      => $page,
            'page_size' => $pageSize,
            'page_count' => ceil($total / $pageSize),
        ]);
    }

    /**
     * 房源详情
     * GET /house/detail?id=xxx
     */
    public function detail()
    {
        $id = $this->request->get('id');
        if (!$id) {
            $this->error(__('Invalid parameters'));
        }

        $house = HouseModel::get($id);

        // 判断当前请求者是否为该房源所属经纪人（编辑场景）
        $isOwnerAgent = false;
        if ($this->auth->isLogin()) {
            $agent = model("agent")->where('user_id', $this->auth->id)->find();
            if ($agent && $agent['id'] == $house['agent_id']) {
                $isOwnerAgent = true;
            }
        }

        if (!$house || $house['status'] !== 1 || $house['check_status'] == 0 || $house['check_status'] == 2) {
            if(!$this->auth->isLogin() || !$isOwnerAgent) {
                $this->error('房源不存在或已下架');
            }
        }

        // 增加浏览量
        Db::name('house')->where('id', $id)->setInc('view_count');

        

        // 房主经纪人编辑：用未审核的 house_up 变更字段替换 house 中的被改变项
        if ($isOwnerAgent) {
            $houseUp = model('Houseup')->where('house_id', $house['id'])->where('check_status', 0)->find();
            if ($houseUp) {
                $upData = $houseUp->getData();
                foreach ($this->houseUpFields as $field) {
                    if (array_key_exists($field, $upData)) {
                        $val = $upData[$field];
                        // 仅覆盖非空的变更字段
                        if ($val !== null && $val !== '') {
                            $house[$field] = $val;
                        }
                    }
                }
            }
        }

        $this->formatHouse($house);

        // 房主经纪人：附加审核状态与备注，供编辑页展示审核不通过原因
        if ($isOwnerAgent) {
            $house['check_status'] = $house->getData('check_status');
            $house['remark'] = $house->getData('remark');
        }

        // 关联经纪人信息
        if ($house['agent_id']) {
            $agent = AgentModel::get($house['agent_id']);
            if ($agent) {
                $house['agent'] = [
                    'id'     => $agent['id'],
                    'name'   => $agent['name'],
                    'avatar' => cdnurl($agent['avatar'], true),
                    'phone'  => $agent['phone'],
                    'house_count' => $agent['house_count'],
                ];
            }
        }

        // 关联小区完整信息
        if ($house['community_id']) {
            $community = CommunityModel::find($house['community_id']);
            if ($community) {
                if ($community['cover_image']) {
                    $community['cover_image'] = cdnurl($community['cover_image'], true);
                }
                $house['community'] = $community;
            }
        }

        $this->success('', $house);
    }

    /**
     * 推荐房源
     * GET /house/recommend
     * @param type  房源类型，默认全部
     * @param limit 返回数量，默认10
     */
    public function recommend()
    {
        $type  = $this->request->get('type', '');
        $limit = min(20, max(1, intval($this->request->get('limit', 10))));

        $where = ['status' => '1', 'is_recommend' => 1];
        if ($type) {
            $where['type'] = $type;
        }

        $list = HouseModel::where($where)
            ->order('weigh', 'desc')
            ->order('createtime', 'desc')
            ->limit($limit)
            ->select();

        foreach ($list as &$row) {
            $this->formatHouse($row);
        }

        $this->success('', $list);
    }

    /**
     * 格式化房源数据（图片路径、显示文本）
     */
    protected function formatHouse(&$row)
    {
        // 封面图
        if ($row['cover_image']) {
            $row['cover_image_url'] = cdnurl($row['cover_image'], true);
        }

        // 房源码图片
        if (!empty($row['house_code_qrcode'])) {
            $row['house_code_qrcode_url'] = cdnurl($row['house_code_qrcode'], true);
        }

        // 图片列表
        if ($row['images']) {
            $images = explode(",", $row['images']);
            $imgs=[];
            foreach ($images as &$img) {
                $img = cdnurl($img, true);
                $imgs[] = $img;
            }
            $row['fullimages'] = implode(",",$imgs);
        }

        // 配套设施
        $facilities = $row['facilities'] ?? '';
        $decoded = is_array($facilities) ? $facilities : (is_string($facilities) ? json_decode($facilities, true) : []);
        $row['facilities'] = is_array($decoded) ? $decoded : [];

        // 类型文本
        $typeList = (new HouseModel())->getTypeList();
        $row['type_text'] = $typeList[$row['type']] ?? '';

        // 价格展示
        if ($row['type'] === 'rent' || $row['type'] === 'shop') {
            $row['price_display'] = floatval($row['monthly_rent']) > 0 ? floatval($row['monthly_rent']) . '元/月' : '面议';
        } else {
            $row['price_display'] = floatval($row['price']) > 0 ? floatval($row['price']) . '万' : '面议';
        }

        // 户型面积组合
        $row['room_info'] = $row['house_type'] . ' | ' . floatval($row['area']) . '㎡';

        // 关联小区信息（供列表页展示小区名、封面等）
        if (!empty($row['community_id'])) {
            static $communityCache = [];
            $cid = $row['community_id'];
            if (!isset($communityCache[$cid])) {
                $com = CommunityModel::find($cid);
                $communityCache[$cid] = $com ? $com->toArray() : null;
            }
            if ($communityCache[$cid]) {
                $row['community'] = $communityCache[$cid];
            }
        }
    }
}
