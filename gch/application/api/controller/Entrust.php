<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Entrust as EntrustModel;
use app\common\model\Booking as BookingModel;
use app\common\model\Member as MemberModel;
use think\Db;

/**
 * 委托/预约接口
 */
class Entrust extends Api
{
    protected $noNeedLogin = ['getHouseArea','submit', 'booking', 'myList'];
    protected $noNeedRight = '*';

    public function getHouseArea()
    {
        $houseArea = config("site.house_area");
        $this->success('', [
            'houseAreaList'  => $houseArea,
        ]);
    }

    /**
     * 提交委托找房
     * POST /entrust/submit
     */
    public function submit()
    {
        $name          = $this->request->post('name', '');
        $phone         = $this->request->post('phone', '');
        $type          = $this->request->post('type', 'buy');
        $communityId   = $this->request->post('community_id', '');
        $communityName = $this->request->post('community_name', '');
        $house_area_id = $this->request->post('house_area_id', '');
        $area          = $this->request->post('area', '');
        $houseType     = $this->request->post('house_type', '');
        $remark        = $this->request->post('remark', '');
        $houseId       = $this->request->post('house_id', 0);
        $agentId       = $this->request->post('agent_id', 0);

        if (!$name || !$phone) {
            $this->error('请填写姓名和手机号');
        }
        if (!preg_match('/^1\d{10}$/', $phone)) {
            $this->error('手机号格式不正确');
        }
        $user_id = 0;
        if($this->auth->isLogin()) {
            $user_id = $this->auth->id;
        }

        $agentUserId = 0;
        $source= 'entrust';
        if($houseId) {
            $house = Db::name('house')->where('id', $houseId)->find();
            if($house) {
                $communityId = $house['community_id'];
                $agentId = $house['agent_id'];
                $agentUserId = $house['agent_user_id'];
                $source = 'booking';
            }
        }

        $houseArea = config("site.house_area");
        if($house_area_id){
            $area = $houseArea[$house_area_id];
        } else {
            if($area) {
                $area .= '㎡';
            }
        }

        $entrust = EntrustModel::create([
            'entrust_no'     => 'WT' . date('YmdHis') . mt_rand(1000, 9999),
            'type'           => $type,
            'name'           => $name,
            'phone'          => $phone,
            'community_id'   => $communityId,
            'community_name' => $communityName,
            'house_area_id'  => $house_area_id,
            'area'           => $area,
            'house_type'     => $houseType,
            'remark'         => $remark,
            'user_id'        => $user_id,
            'agent_id'       => $agentId,
            'agent_user_id'  => $agentUserId,
            'house_id'       => $houseId,
            'source'         => $source,
        ]);

        $this->success('委托提交成功，我们会尽快联系您', ['id' => $entrust->id]);
    }

    /**
     * 预约看房
     * POST /entrust/booking
     */
    public function booking()
    {
        $name          = $this->request->post('name', '');
        $phone         = $this->request->post('phone', '');
        $communityName = $this->request->post('community_name', '');
        $area          = $this->request->post('area', '');
        $houseType     = $this->request->post('house_type', '');
        $remark        = $this->request->post('remark', '');
        $houseId       = intval($this->request->post('house_id', 0));
        $agentId       = intval($this->request->post('agent_id', 0));

        if (!$name || !$phone) {
            $this->error('请填写姓名和手机号');
        }
        if (!preg_match('/^1\d{10}$/', $phone)) {
            $this->error('手机号格式不正确');
        }

        $memberId = 0;
        if ($this->auth->isLogin()) {
            $memberId = $this->auth->id;
        }

        // 预约记录存 fy_booking 表
        $booking = BookingModel::create([
            'name'           => $name,
            'phone'          => $phone,
            'community_name' => $communityName,
            'area'           => $area,
            'house_type'     => $houseType,
            'remark'         => $remark,
            'house_id'       => $houseId,
            'agent_id'       => $agentId,
            'member_id'      => $memberId,
            'status'         => '0',
        ]);

        // 同时在 fy_entrust 表也记录一条（便于经纪人统一管理）
        $entrust = EntrustModel::create([
            'entrust_no'     => 'YY' . date('YmdHis') . mt_rand(1000, 9999),
            'type'           => 'buy',
            'name'           => $name,
            'phone'          => $phone,
            'community_name' => $communityName,
            'area'           => $area,
            'house_type'     => $houseType,
            'remark'         => $remark,
            'member_id'      => $memberId,
            'agent_id'       => $agentId,
            'house_id'       => $houseId,
            'source'         => 'booking',
        ]);

        $this->success('预约成功，我们会尽快与您联系', ['booking_id' => $booking->id]);
    }

    /**
     * 获取我的委托列表
     * GET /entrust/myList
     */
    public function myList()
    {
        $page      = intval($this->request->get('page', 1));
        $pageSize  = intval($this->request->get('pageSize', 10));
        $pageSize  = $pageSize > 0 && $pageSize <= 20 ? $pageSize : 10;
        $offset    = ($page - 1) * $pageSize;

        $userId = 0;
        if ($this->auth->isLogin()) {
            $userId = $this->auth->id;
        }

        $list = Db::name('entrust')
            ->where('user_id', $userId)
            ->where('source', 'entrust')
            ->order('id', 'desc')
            ->limit($offset, $pageSize)
            ->select();

        $total = Db::name('entrust')
            ->where('user_id', $userId)
            ->where('source', 'entrust')
            ->count();

        // 处理数据
        foreach ($list as &$item) {
            $item['type_text'] = $item['type'] === 'buy' ? '买房' : '卖房';
            $item['createtime_text'] = date('Y-m-d H:i', $item['createtime']);
        }

        $this->success('', [
            'list'  => $list,
            'total' => $total,
            'page'  => $page,
            'pageSize' => $pageSize,
        ]);
    }
}
