<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Community as CommunityModel;

/**
 * 小区接口（经纪人操作）
 */
class Community extends Api
{
    protected $noNeedLogin = ['list', 'detail'];
    protected $noNeedRight = '*';

    /**
     * 小区列表（供下拉选择）
     * GET /community/list
     * @param keyword  搜索关键词
     * @param page     页码
     * @param limit    每页数量（传0或不传则返回全部）
     */
    public function list()
    {
        $keyword = $this->request->get('keyword', '');
        $page    = intval($this->request->get('page', 1));
        $limit   = intval($this->request->get('limit', 0));

        $where = ['status' => 1];
        if ($keyword) {
            $where['name'] = ['like', "%{$keyword}%"];
        }

        if ($limit > 0) {
            $list = CommunityModel::where($where)
                ->where("status", 1)
                ->order('id', 'desc')
                ->page($page, $limit)
                ->select();

            $total = CommunityModel::where($where)->count();

            foreach ($list as &$row) {
                if ($row['cover_image']) {
                    $row['cover_image'] = cdnurl($row['cover_image'], true);
                }
            }

            $this->success('', [
                'list'       => $list,
                'total'      => $total,
                'page'       => $page,
                'page_count' => ceil($total / $limit),
            ]);
        } else {
            // 不传 limit 时返回全部（供选择器使用）
            $list = CommunityModel::where($where)
                ->order('name', 'asc')
                ->where("status", 1)
                ->select();

            foreach ($list as &$row) {
                if ($row['cover_image']) {
                    $row['cover_image'] = cdnurl($row['cover_image'], true);
                }
            }

            // 统一返回 {list: [...]} 格式，与分页分支保持一致
            $this->success('', [
                'list'  => $list,
                'total' => count($list),
            ]);
        }
    }

    /**
     * 小区详情
     * GET /community/detail?id=xxx
     */
    public function detail()
    {
        $id = intval($this->request->get('id'));
        if (!$id) {
            $this->error('缺少小区ID');
        }

        $community = CommunityModel::get($id);
        if (!$community) {
            $this->error('小区不存在');
        }

        if ($community['cover_image']) {
            $community['cover_image'] = cdnurl($community['cover_image'], true);
        }

        $this->success('', $community);
    }

    /**
     * 新增小区（经纪人操作）
     * POST /community/add
     */
    public function add()
    {
        $data = $this->request->post();

        if (empty($data['name'])) {
            $this->error('请输入小区名称');
        }

        $data['createtime'] = time();
        $data['updatetime'] = time();

        $community = new CommunityModel();
        $result = $community->allowField(true)->save($data);
        if ($result) {
            $this->success('添加成功', ['id' => $community->id]);
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 编辑小区（经纪人操作）
     * POST /community/edit
     */
    public function edit()
    {
        $data = $this->request->post();

        if (empty($data['id'])) {
            $this->error('缺少小区ID');
        }

        $community = CommunityModel::get($data['id']);
        if (!$community) {
            $this->error('小区不存在');
        }

        $data['updatetime'] = time();

        $result = $community->allowField(true)->save($data);
        if ($result !== false) {
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

    /**
     * 删除小区（经纪人操作）
     * POST /community/delete
     */
    public function delete()
    {
        $id = intval($this->request->post('id'));
        if (!$id) {
            $this->error('缺少小区ID');
        }

        $community = CommunityModel::get($id);
        if (!$community) {
            $this->error('小区不存在');
        }

        $result = $community->delete();
        if ($result) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}
