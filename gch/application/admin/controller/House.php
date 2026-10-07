<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 房源管理
 *
 * @icon fa fa-circle-o
 */
class House extends Backend
{

    /**
     * House模型对象
     * @var \app\common\model\House
     */
    protected $model = null;

    // 快速搜索字段
    protected $searchFields = 'title,community_name,address';

    // 关闭关联查询（经纪人信息通过with预加载获取，不参与搜索）
    protected $relationSearch = false;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\House;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("hasElevatorList", $this->model->getHasElevatorList());
        $this->view->assign("isRecommendList", $this->model->getIsRecommendList());
        $this->view->assign("verifyStatusList", $this->model->getVerifyStatusList());
        $this->view->assign("checkStatusList", $this->model->getCheckStatusList());
        $this->assignconfig("checkStatusList", $this->model->getCheckStatusList());

        // 预设配套设施列表
        $this->view->assign("facilitiesList", [
            '床', '空调', '冰箱', '洗衣机', '电视', '热水器',
            '沙发', '衣柜', '橱柜', '燃气灶', '油烟机', '微波炉',
            '饮水机', '宽带', '阳台', '车位', '地下室', '电梯'
        ]);
    }


    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


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
                    ->with(['community','agent'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                // 格式化显示
                $row->visible(['id', 'title', 'address', 'type', 'price', 'status', 'cover_image',
                    'has_elevator', 'is_recommend', 'verify_status','check_status',
                    'createtime', 'updatetime']);
                $row->visible(['community','agent']);
                $row->getRelation('community')->visible(['name']);
                $row->getRelation('agent')->visible(['name']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    public function toggle($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error('房源不存在');
        }

        $column = input("params");

        if ($row[$column] == 1) {
            // 取消经纪人
            $row[$column] = 0;
            $row->save();
            $this->success('操作成功');
        } else {
            $row[$column] = 1;
            $row->save();
            $this->success('操作成功');
        }
    }

    /**
     * 审核
     *
     * 逻辑：
     *  - 普通审核（check_status 0 → 1/2）：直接更新审核状态
     *  - 编辑审核（check_status 9 → 1）：从 house_up 同步数据到 house，再更新审核状态为 1
     *  - 编辑审核拒绝（check_status 9 → 2）：仅更新审核状态为 2，house_up 数据不写入 house
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function check($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            // 编辑审核（check_status=9）：加载 house_up 数据供对比展示
            if (intval($row['check_status']) == 9) {
                $houseUp = \app\common\model\Houseup::where('house_id', $row['id'])->where('check_status', 0)->find();
                if ($houseUp) {
                    $this->view->assign('houseUp', $houseUp);
                    $syncFields = [
                        'community_id', 'title', 'type', 'cover_image', 'images',
                        'house_type', 'area', 'orientation', 'decoration', 'floor', 'total_floor',
                        'has_elevator', 'price', 'unit_price', 'monthly_rent',
                        'house_use', 'house_nature', 'co_ownership', 'mortgage',
                        'commission_type', 'inner_area', 'house_code', 'house_code_qrcode',
                        'verify_status', 'verify_time', 'publish_time',
                        'description', 'facilities', 'address',
                    ];

                    // 保存原始 house 数据（转为数组方便取值）
                    $originalRow = $row->toArray();
                    $this->view->assign('originalRow', $originalRow);

                    // house_up 表只保存了有修改的字段，未修改字段为空。
                    // 使用原始数据取值，仅把 house_up 中"有值（非空）"的字段视为变更字段。
                    $houseUpData = $houseUp->getData();

                    // 计算变更字段及新旧值
                    $changedFields = [];
                    foreach ($syncFields as $field) {
                        $newVal = $houseUpData[$field] ?? null;
                        // house_up 中为空（null / 空字符串）表示该字段未修改，跳过
                        if ($newVal === null || $newVal === '') {
                            continue;
                        }
                        $oldVal = $originalRow[$field] ?? '';
                        if ($newVal != $oldVal) {
                            $changedFields[$field] = ['old' => $oldVal, 'new' => $newVal];
                        }
                    }
                    $this->view->assign('changedFields', $changedFields);
                    // 预先编码为 JSON 字符串，交由前端从隐藏域读取，避免模板 { } 与 JS 冲突
                    $this->view->assign('changedFieldsJson', $changedFields ? json_encode($changedFields, JSON_UNESCAPED_UNICODE) : '{}');

                    // 审核页展示规则（控制端同时取 house 与 house_up 两张表）：
                    //  - 未变更的字段：保留 house 表原值
                    //  - 有变更的字段：使用 house_up 表的新值（待审核）
                    // 仅把真正发生变更的字段用 house_up 的值覆盖，避免误覆盖未变更字段
                    foreach ($changedFields as $field => $info) {
                        $row[$field] = $info['new'];
                    }
                }
            }
            // 配套设施统一转换为数组，供前端直接渲染（避免依赖文本解析失败）
            $facilitiesVal = $row['facilities'] ?? '';
            if (is_string($facilitiesVal)) {
                $facilitiesVal = json_decode($facilitiesVal, true);
                if (!is_array($facilitiesVal)) {
                    $facilitiesVal = [];
                }
            } elseif (!is_array($facilitiesVal)) {
                $facilitiesVal = [];
            }
            $this->view->assign('facilitiesArr', $facilitiesVal);

            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;

        $newCheckStatus = intval($params['check_status']);
        $oldCheckStatus = intval($row['check_status']);

        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }

            // 编辑审核（check_status 9）：处理 house_up 记录
            if ($oldCheckStatus == 9) {
                $houseUp = \app\common\model\Houseup::where('house_id', $row['id'])->where('check_status', 0)->find();
                if ($houseUp) {
                    if ($newCheckStatus == 1) {
                        // 审核通过：仅将"有变更的数据"从 house_up 同步到 house
                        $syncFields = [
                            'community_id', 'title', 'type', 'cover_image', 'images',
                            'house_type', 'area', 'orientation', 'decoration', 'floor', 'total_floor',
                            'has_elevator', 'price', 'unit_price', 'monthly_rent',
                            'house_use', 'house_nature', 'co_ownership', 'mortgage',
                            'commission_type', 'inner_area', 'house_code',
                            'verify_status', 'verify_time', 'publish_time',
                            'description', 'facilities', 'address',
                        ];
                        // house_up 只保存有修改的字段，未修改字段为空；
                        // 仅同步 house_up 中"非空且与 house 原值不同"的变更字段，避免把有效数据覆盖为空。
                        $houseUpData = $houseUp->getData();
                        $updateData = [];
                        foreach ($syncFields as $field) {
                            $newVal = $houseUpData[$field] ?? null;
                            if ($newVal === null || $newVal === '') {
                                continue;
                            }
                            $oldVal = $row[$field] ?? '';
                            if ($newVal != $oldVal) {
                                $updateData[$field] = $newVal;
                            }
                        }
                        if (!empty($updateData)) {
                            $updateData['updatetime'] = time();
                            $row->allowField(true)->save($updateData);
                        }
                    }
                    // 更新 house_up 的审核状态（通过=1 / 不通过=2），避免重复展示待审核记录
                    $houseUp->save(['check_status' => $newCheckStatus]);
                }
            }

            // 更新审核状态和备注
            $result = $row->allowField(['check_status', 'remark'])->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

}
