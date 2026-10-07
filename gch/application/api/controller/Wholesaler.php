<?php
namespace app\api\controller;

use app\api\model\Buyer;
use app\api\model\Goods as GoodsModel;
use app\api\model\GoodsImage;
use app\api\model\ProductCategory;
use app\api\model\Reservation as ReservationModel;
use app\api\model\Shop;
use app\api\model\Wholesaler as WholesalerModel;
use extend\gch\Token;
use think\Db;

/**
 * 批发商(合并所有批发商子业务:登录、账户、货盘、预订)
 *
 * 设计说明:原 WholesalerGoods / WholesalerReservation 因为
 * application/route.php 数组路由未被 pathinfo 覆盖,
 * 导致 /api/wholesaler/goods 等 404。统一合并到 Wholesaler.php 后,
 * 路由条目也指向这个类,问题彻底解决。
 */
class Wholesaler extends ApiBase
{
    // 默认不需要登录(login/logout 不需要)
    // 其他方法开头第一行:$this->requireLogin = true;$this->requireRole = 'wholesaler';
    protected $requireLogin = false;
    protected $requireRole  = null;

    /**
     * 拦截器:子类 _initialize 之前统一开启鉴权(除 login/logout)
     * ThinkPHP 5.1 的 _initialize() 是钩子,会在 action 之前调用
     */
    protected function _requireAuth()
    {
        $this->requireLogin = true;
        $this->requireRole  = 'wholesaler';
        $this->_initialize();
    }

    // === 账户相关 ===

    public function login()
    {
        $account  = $this->request->param('account', '');
        $password = $this->request->param('password', '');

        if (empty($account) || empty($password)) {
            return $this->error('账号或密码不能为空');
        }

        list($ok, $wh, $msg) = WholesalerModel::login($account, $password);
        if (!$ok) return $this->error($msg, 400);

        $tk = Token::create('wholesaler', $wh->id, $this->clientIp());
        return $this->success([
            'token'  => $tk['token'],
            'expire' => $tk['expire_time'],
            'role'   => 'wholesaler',
            'wholesaler' => [
                'id'        => $wh->id,
                'account'   => $wh->account,
                'real_name' => $wh->real_name,
                'shop_id'   => $wh->shop_id,
            ],
        ], '登录成功');
    }

    public function logout()
    {
        $token = $this->request->header('Token', '');
        if ($token) Token::destroy($token);
        return $this->success(null, '已退出');
    }

    public function profile()
    {
        $this->_requireAuth();
        $wh = WholesalerModel::with('shop')->find($this->user['user_id']);
        if (!$wh) return $this->error('用户不存在', 401);

        return $this->success([
            'id'        => $wh->id,
            'account'   => $wh->account,
            'real_name' => $wh->real_name,
            'mobile'    => $wh->mobile,
            'shop'      => $wh->shop ? [
                'id'   => $wh->shop->id,
                'name' => $wh->shop->name,
            ] : null,
            'role'      => 'wholesaler',
        ]);
    }

    public function changePwd()
    {
        $this->_requireAuth();
        $old = $this->request->param('old_password', '');
        $new = $this->request->param('new_password', '');

        if (empty($old) || empty($new)) return $this->error('原密码和新密码必填');
        if (strlen($new) < 8 || strlen($new) > 20) return $this->error('新密码长度需 8-20 位');

        $wh = WholesalerModel::get($this->user['user_id']);
        if (!$wh) return $this->error('用户不存在', 401);

        list($ok, $msg) = $wh->changePassword($old, $new);
        if (!$ok) return $this->error($msg);

        $token = $this->request->header('Token', '');
        if ($token) Token::destroy($token);
        return $this->success(null, '密码已修改，请重新登录');
    }

    public function qrcode()
    {
        $this->_requireAuth();
        $wh = WholesalerModel::get($this->user['user_id']);
        if (!$wh || !$wh->shop_id) return $this->error('您尚未绑定店铺');

        $shop = Shop::get($wh->shop_id);
        if (!$shop) return $this->error('店铺不存在');

        // 优先用微信小程序码(wxacode)— 客户扫码直接进入小程序指定页
        list($ok, $png) = \extend\gch\Wxacode::generate('pages/shop/shop', 'id=' . $shop->id, 430);
        if ($ok) {
            $base64 = 'data:image/png;base64,' . base64_encode($png);
            return $this->success([
                'shop_id'    => $shop->id,
                'shop_name'  => $shop->name,
                'qrcode_url' => $base64,
                'is_wxacode' => true,
                'path'       => 'pages/shop/shop?id=' . $shop->id,
            ]);
        }

        // PC 后台已上传的二维码(若有)
        if (!empty($shop->qrcode_url)) {
            return $this->success([
                'shop_id'    => $shop->id,
                'shop_name'  => $shop->name,
                'qrcode_url' => $shop->qrcode_url,
            ]);
        }

        // 兜底:GD 占位图
        $base64 = \extend\gch\QrCode::render('shop:' . $shop->id, $shop->name);
        return $this->success([
            'shop_id'    => $shop->id,
            'shop_name'  => $shop->name,
            'qrcode_url' => $base64,
            'is_placeholder' => true,
            'tip'         => '微信 wxacode 生成失败(' . $png . '),已退回占位图',
        ]);
    }

    // === 货盘管理(原 WholesalerGoods) ===

    public function categories()
    {
        $this->_requireAuth();
        $list = ProductCategory::where('status', 1)->order('sort asc')->select();
        return $this->success($list);
    }

    /**
     * /api/wholesaler/goods:GET=列表 / POST=创建(用 method 分发)
     * /api/wholesaler/goods/:id:GET=详情
     * /api/wholesaler/goods/status:POST=上下架
     */
    public function goods()
    {
        if ($this->request->isPost()) return $this->create();
        return $this->index();
    }

    public function goods_status()
    {
        return $this->status();
    }

    public function index()
    {
        $this->_requireAuth();
        $page  = (int)$this->request->param('page', 1);
        $limit = (int)$this->request->param('limit', 20);
        $status = $this->request->param('status', '');

        $where = ['wholesaler_id' => $this->user['user_id']];
        if ($status !== '') $where['status'] = (int)$status;

        $list  = GoodsModel::where($where)->order('id desc')->page($page, $limit)->select();
        $total = GoodsModel::where($where)->count();

        foreach ($list as &$g) {
            $g->available = max(0, $g->total_stock - $g->reserved_quantity);
        }
        return $this->success(['list' => $list, 'total' => $total]);
    }

    public function detail($id = 0)
    {
        $this->_requireAuth();
        $id = (int)$id;
        $g = GoodsModel::get($id);
        if (!$g || $g->wholesaler_id != $this->user['user_id']) {
            return $this->error('货盘不存在', 404);
        }
        $g->images = GoodsImage::getByGoods($g->id);
        $g->available = max(0, $g->total_stock - $g->reserved_quantity);
        return $this->success($g);
    }

    public function create()
    {
        $this->_requireAuth();
        $raw = file_get_contents('php://input');
        $json = $raw ? json_decode($raw, true) : [];
        if (!is_array($json) || empty($json)) {
            $json = $this->request->post();
            if (!is_array($json)) $json = [];
        }
        $data = [
            'category_id'  => $json['category_id']  ?? 0,
            'name'         => $json['name']         ?? '',
            'price'        => $json['price']        ?? 0,
            'unit'         => $json['unit']         ?? '',
            'total_stock'  => $json['total_stock']  ?? 0,
        ];
        $images = $json['images'] ?? [];

        if (empty($data['category_id'])) return $this->error('分类不能为空');
        if (empty($data['name'])) return $this->error('品名不能为空');
        if (!isset($data['price']) || $data['price'] <= 0) return $this->error('单价必须大于 0');
        if (empty($data['unit'])) return $this->error('单位不能为空');
        if (!isset($data['total_stock']) || $data['total_stock'] < 1) return $this->error('库存必须大于等于 1');

        $wh = WholesalerModel::get($this->user['user_id']);
        if (!$wh || !$wh->shop_id) return $this->error('您尚未绑定店铺');

        Db::startTrans();
        try {
            $g = GoodsModel::create([
                'goods_no'      => GoodsModel::genNo(),
                'wholesaler_id' => $wh->id,
                'shop_id'       => $wh->shop_id,
                'category_id'   => $data['category_id'],
                'name'          => $data['name'],
                'price'         => $data['price'],
                'unit'          => $data['unit'],
                'total_stock'   => $data['total_stock'],
                'status'        => 1,
                'publish_time'  => time(),
            ]);

            foreach ($images as $i => $url) {
                if (!empty($url)) {
                    GoodsImage::create([
                        'goods_id' => $g->id,
                        'url'      => $url,
                        'sort'     => $i,
                    ]);
                }
            }
            Db::commit();
            return $this->success(['id' => $g->id, 'goods_no' => $g->goods_no], '发布成功');
        } catch (\think\exception\HttpResponseException $e) {
            // success() / error() 内部用 throw HttpResponseException 返回,不要当业务异常处理
            throw $e;
        } catch (\Exception $e) {
            Db::rollback();
            // 兜底:若 message 为空,显示 文件:行号 + getCode(),便于排查
            $msg = $e->getMessage() ?: sprintf('[%s:%d] code=%d', basename($e->getFile()), $e->getLine(), $e->getCode());
            return $this->error('发布失败:' . $msg);
        }
    }

    public function status()
    {
        $this->_requireAuth();
        $id = (int)$this->request->param('id', 0);
        $status = (int)$this->request->param('status', 0);
        if (!$id) return $this->error('缺少 id');

        $g = GoodsModel::get($id);
        if (!$g || $g->wholesaler_id != $this->user['user_id']) {
            return $this->error('货盘不存在', 404);
        }
        if (!in_array($status, [0, 1, 2])) return $this->error('status 非法');

        if ($status == 0) {
            $pending = Db::name('reservation')
                ->where('goods_id', $id)
                ->where('status', 'pending')
                ->count();
            if ($pending > 0) {
                return $this->error('该货盘有待确认预订（' . $pending . ' 单），请先取消');
            }
        }

        $g->status = $status;
        $g->save();
        return $this->success(null, '操作成功');
    }

    // === 预订管理(原 WholesalerReservation) ===

    /**
     * 别名:pathinfo 解析 /api/wholesaler/reservation/:id → reservation($id) → reservation_detail
     */
    public function reservation($id = 0) { return $this->reservation_detail($id); }

    public function reservations()
    {
        $this->_requireAuth();
        $page  = (int)$this->request->param('page', 1);
        $limit = (int)$this->request->param('limit', 20);
        $status = $this->request->param('status', '');

        $data = ReservationModel::getList([
            'wholesaler_id' => $this->user['user_id'],
            'status'        => $status,
        ], $page, $limit);
        return $this->success($data);
    }

    public function reservation_detail($id = 0)
    {
        $this->_requireAuth();
        $id = (int)$id;
        $r = ReservationModel::get($id);
        if (!$r) return $this->error('订单不存在', 404);
        if ($r->wholesaler_id != $this->user['user_id']) {
            return $this->error('无权访问', 403);
        }
        $buyer = Buyer::get($r->buyer_id);
        $r->buyer = $buyer ? [
            'id'        => $buyer->id,
            'account'   => $buyer->account,
            'real_name' => $buyer->real_name,
            'mobile'    => $buyer->mobile,
        ] : null;
        $r->shop;
        return $this->success($r);
    }

    public function reservation_confirm()
    {
        $this->_requireAuth();
        $id = (int)$this->request->param('id', 0);
        if (!$id) return $this->error('缺少 id');

        $r = ReservationModel::get($id);
        if (!$r) return $this->error('订单不存在', 404);
        if ($r->wholesaler_id != $this->user['user_id']) {
            return $this->error('无权操作', 403);
        }
        list($ok, $msg) = $r->confirm();
        if (!$ok) return $this->error($msg);
        return $this->success(null, '已确认');
    }

    public function reservation_cancel()
    {
        $this->_requireAuth();
        $id = (int)$this->request->param('id', 0);
        $reason = $this->request->param('reason', '');
        if (!$id) return $this->error('缺少 id');

        $r = ReservationModel::get($id);
        if (!$r) return $this->error('订单不存在', 404);
        if ($r->wholesaler_id != $this->user['user_id']) {
            return $this->error('无权操作', 403);
        }
        list($ok, $msg) = $r->cancel($reason, 'wholesaler');
        if (!$ok) return $this->error($msg);
        return $this->success(null, '已取消');
    }
}
