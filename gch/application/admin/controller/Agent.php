<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\library\Auth;
use think\Db;
use fast\Random;

/**
 * 经纪人管理
 *
 * @icon fa fa-user-secret
 * @remark 管理经纪人信息、登录密码、状态。密码统一存在 user 表，agent 表通过 user_id 关联。
 */
class Agent extends Backend
{
    /**
     * @var \app\common\model\Agent
     */
    protected $model = null;

    protected $searchFields = 'name,phone';
    protected $multiFields = 'status';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\Agent;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("customerAuthList", $this->model->getCustomerAuthList());
        $this->view->assign("houseAuthList", $this->model->getHouseAuthList());
    }

    /**
     * 查看
     */
    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    /**
     * 添加
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        // 检查手机号是否已存在
        $exist = $this->model->where('phone', $params['phone'])->find();
        if ($exist) {
            $this->error('该手机号已存在');
        }
        // 默认密码
        $password = !empty($params['password']) ? $params['password'] : '123456';
        // 密码不存经纪人表，移除
        unset($params['password']);

        Db::startTrans();
        try {
            // 1. 创建经纪人
            $result = $this->model->allowField(true)->save($params);
            if ($result === false) {
                throw new \Exception(__('No rows were inserted'));
            }
            $agentId = $this->model->id;

            // 2. 同步创建 user 记录（phone 作为 account）
            $salt = Random::alnum();
            $encryptedPassword = \app\common\library\Auth::instance()->getEncryptPassword($password, $salt);
            $time = time();

            $user = \app\common\model\User::getByUsername($params['phone']);
            if ($user) {
                $userId = $user->id;
            } else {
                $user = \app\common\model\User::create([
                    'group_id'  => 2,
                    'username'  => $params['phone'],
                    'nickname'  => $params['name'] ?? $params['phone'],
                    'password'  => $encryptedPassword,
                    'salt'      => $salt,
//                    'mobile'    => $params['phone'],
                    'avatar'    => $params['avatar'] ?? '',
                    'level'     => 1,
                    'score'     => 0,
                    'jointime'  => $time,
                    'joinip'    => $this->request->ip(),
                    'logintime' => $time,
                    'loginip'   => $this->request->ip(),
                    'prevtime'  => $time,
                    'status'    => \app\common\model\Agent::statusToUser($params['status'] ?? '1'),
                ]);
                $userId = $user->id;
            }

            // 3. 将 user_id 存回经纪人表
            $this->model->save(['user_id' => $userId]);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        // phone 不可编辑，移除
        unset($params['phone']);
        // 提取密码（不存经纪人表）
        $password = isset($params['password']) ? $params['password'] : null;
        unset($params['password']);

        Db::startTrans();
        try {
            // 1. 更新经纪人表
            $result = $row->allowField(true)->save($params);
            if ($result === false) {
                throw new \Exception(__('No rows were updated'));
            }

            // 2. 同步更新 user 表（密码、头像、状态）
            if ($row->user_id) {
                $userModel = new \app\common\model\User;
                $userRow = $userModel->find($row->user_id);
                if ($userRow) {
                    $userData = [
                        'nickname' => $params['name'] ?? $row->name,
//                        'mobile'   => $row->phone,
                    ];
                    if (!empty($params['avatar'])) {
                        $userData['avatar'] = $params['avatar'];
                    }
                    if (isset($params['status']) && $params['status'] !== '') {
                        $userData['status'] = \app\common\model\Agent::statusToUser($params['status']);
                    }
                    // 密码有变化才更新
                    if (!empty($password)) {
                        $salt = Random::alnum();
                        $userData['password'] = \app\common\library\Auth::instance()->getEncryptPassword($password, $salt);
                        $userData['salt'] = $salt;
                        Auth::instance()->delete($row->user_id);
                    }
                    $userRow->save($userData);
                }
            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success();
    }
}
