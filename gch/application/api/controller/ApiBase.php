<?php
/**
 * 小程序 API 基类
 * - 统一响应
 * - Token 校验
 * - 角色校验
 *
 * 注意：success/error 的签名需要与父类 think\Controller（Jump trait）兼容，
 * 5 参数版本与父类一致，但内部通过 func_num_args() 兼容两种调用风格：
 *   - 老风格（业务用）：success($data, $msg) / error($msg, $code, $data)
 *   - 新风格（FastAdmin 标准）：success($msg, $url, $data, $wait, $header) / error($msg, $data, $code, $type, $header)
 */

namespace app\api\controller;

use extend\gch\Response;
use extend\gch\Token;
use think\Controller;
use think\exception\HttpException;
use think\Request;

class ApiBase extends Controller
{
    /**
     * 当前用户信息（来自 Token）
     * ['role' => ..., 'user_id' => ...]
     */
    protected $user = null;

    /**
     * 是否需要登录（子类可覆盖）
     */
    protected $requireLogin = true;

    /**
     * 期望角色（null = 不限）
     */
    protected $requireRole = null;

    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();

        if (!$this->requireLogin) {
            return;
        }

        $token = $this->request->header('Token', '');
        // 兼容 GET 参数 token
        if (empty($token)) {
            $token = $this->request->param('token', '');
        }

        $info = Token::check($token, $this->requireRole);
        if (!$info) {
            if (empty($token)) {
                throw new HttpException(401, '请先登录');
            }
            throw new HttpException(401, '登录已失效，请重新登录');
        }

        $this->user = $info;
        // 注入到请求对象
        $this->request->user = $info;
    }

    /**
     * 响应成功
     *
     * 父类（Jump trait）签名：success($msg, $url, $data, $wait, $header)
     * 本类业务调用约定：    success($data, $msg)
     * 通过参数数量自动判断：2 参数 → 老风格；其他 → 新风格
     *
     * 实现：用 think\Response::create() + throw HttpResponseException
     * 这是 FastAdmin/ThinkPHP 标准做法，避免 controller return 数组触发 type error
     */
    protected function success($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        if (func_num_args() === 2) {
            $payload = Response::success($msg, $url);
        } elseif (func_num_args() === 1) {
            $payload = Response::success($msg);
        } else {
            $payload = Response::success($data, $msg);
        }
        throw new \think\exception\HttpResponseException(\think\Response::create($payload, 'json', 200));
    }

    /**
     * 响应失败
     *
     * 父类签名：error($msg, $data, $code, $type, $header)
     * 本类业务调用约定：error($msg, $code, $data) 或 error($msg, $code)
     */
    protected function error($msg = '', $data = null, $code = 0, $type = null, array $header = [])
    {
        $argc = func_num_args();
        if ($argc === 1) {
            // 只传 msg → 默认业务错误码 400(避免 code=0 的歧义)
            $payload = Response::error($msg, Response::CODE_PARAM_ERROR);
        } elseif ($argc === 2) {
            $payload = Response::error($msg, $data);
        } elseif ($argc === 3) {
            $payload = Response::error($msg, $code, $data);
        } else {
            $payload = Response::error($msg, $code, $data);
        }
        throw new \think\exception\HttpResponseException(\think\Response::create($payload, 'json', 200));
    }

    /**
     * 获取当前请求 IP
     */
    protected function clientIp()
    {
        return $this->request->ip();
    }
}
/* opcache test marker: 1791091018 */
