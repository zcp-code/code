<?php
/**
 * 统一响应封装
 * 所有 API 返回统一格式：{code, msg, data, ...}
 */

namespace extend\gch;

class Response
{
    const CODE_SUCCESS       = 200;
    const CODE_PARAM_ERROR   = 400;
    const CODE_UNAUTHORIZED  = 401;
    const CODE_FORBIDDEN     = 403;
    const CODE_NOT_FOUND     = 404;
    const CODE_SERVER_ERROR  = 500;

    /**
     * 成功响应
     */
    public static function success($data = null, $msg = '成功')
    {
        return self::make(self::CODE_SUCCESS, $msg, $data);
    }

    /**
     * 失败响应
     */
    public static function error($msg = '失败', $code = self::CODE_PARAM_ERROR, $data = null)
    {
        return self::make($code, $msg, $data);
    }

    /**
     * 未登录
     */
    public static function unauthorized($msg = '请先登录')
    {
        return self::make(self::CODE_UNAUTHORIZED, $msg, null);
    }

    /**
     * 权限不足
     */
    public static function forbidden($msg = '权限不足')
    {
        return self::make(self::CODE_FORBIDDEN, $msg, null);
    }

    /**
     * 服务异常
     */
    public static function serverError($msg = '服务异常')
    {
        return self::make(self::CODE_SERVER_ERROR, $msg, null);
    }

    /**
     * 直接输出 JSON 响应
     */
    public static function send($code, $msg, $data = null)
    {
        $payload = self::make($code, $msg, $data);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 构造响应数组
     */
    private static function make($code, $msg, $data = null)
    {
        $resp = ['code' => $code, 'msg' => $msg];
        if ($data !== null) {
            $resp['data'] = $data;
        }
        return $resp;
    }
}
