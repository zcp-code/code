<?php
/**
 * 微信小程序码(wxacode)生成
 *
 * 扫码直接进入小程序指定 path(无需服务端中转)。
 * access_token 缓存到 Redis,避免每次重新换取。
 *
 * 用法:Wxacode::generate('pages/shop/shop?id=1')
 */

namespace extend\gch;

use think\Env;
use think\Cache;

class Wxacode
{
    /**
     * 生成小程序码(无限量接口,scene 透传额外参数)
     * 文档:https://developers.weixin.qq.com/miniprogram/dev/api-backend/qrcode/wxaacode/getUnlimitedQRCode.html
     *
     * @param string $path  小程序页面路径,不带前导 /
     * @param string $scene 额外参数(可选,会作为 query.scene 传给 path)
     * @param int    $width 像素宽度,默认 430
     * @return array [ok, png_bytes | msg]
     */
    public static function generate($path, $scene = '', $width = 430)
    {
        $token = self::getAccessToken();
        if (!$token['ok']) {
            return [false, $token['msg']];
        }

        $url = 'https://api.weixin.qq.com/wxaapi/getwxacodeunlimit?access_token=' . $token['token'];

        $postData = json_encode([
            'path'  => $path,
            'scene' => $scene,
            'width' => $width,
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($resp === false) {
            return [false, '微信 API 请求失败:' . $err];
        }

        // 微信返回:成功 → 图片二进制;失败 → JSON {errcode, errmsg}
        if ($httpCode !== 200 || (isset($resp[0]) && $resp[0] === '{')) {
            $j = json_decode($resp, true);
            if (isset($j['errcode']) && $j['errcode'] !== 0) {
                return [false, '微信 errcode=' . $j['errcode'] . ' errmsg=' . ($j['errmsg'] ?? '')];
            }
        }

        // 检查 PNG 魔数(\x89PNG)
        if (strlen($resp) < 8 || ord($resp[0]) !== 0x89 || ord($resp[1]) !== 0x50) {
            return [false, '微信返回非图片数据'];
        }

        return [true, $resp];
    }

    /**
     * 获取 access_token(Redis 缓存 7000s,微信默认 7200s)
     */
    private static function getAccessToken()
    {
        $appId  = Env::get('wechat.min_appid');
        $secret = Env::get('wechat.min_secret');
        if (empty($appId) || empty($secret)) {
            return ['ok' => false, 'msg' => '.env 未配置 wechat.min_appid / min_secret'];
        }

        $cacheKey = 'wx_access_token_' . md5($appId);

        try {
            $cached = Cache::get($cacheKey);
            if (!empty($cached)) {
                return ['ok' => true, 'token' => $cached];
            }
        } catch (\Throwable $e) {
            // Redis 不可用时继续往下走直接请求
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/token'
             . '?grant_type=client_credential'
             . '&appid=' . urlencode($appId)
             . '&secret=' . urlencode($secret);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);

        if ($resp === false) {
            return ['ok' => false, 'msg' => '获取 access_token 网络失败'];
        }

        $j = json_decode($resp, true);
        if (empty($j['access_token'])) {
            return ['ok' => false, 'msg' => '获取 access_token 失败:' . ($j['errmsg'] ?? $resp)];
        }

        try {
            Cache::set($cacheKey, $j['access_token'], 7000);
        } catch (\Throwable $e) {}

        return ['ok' => true, 'token' => $j['access_token']];
    }
}
