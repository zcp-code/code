<?php
/**
 * 微信小程序辅助
 * 用于游客登录的 code2Session
 */

namespace extend\gch;

use think\Log;

class Wechat
{
    /**
     * code 换 session
     *
     * @param string $code  微信 code
     * @param string $appid 小程序 AppID
     * @param string $secret 小程序 AppSecret
     * @return array|false 成功返回 ['openid', 'unionid', 'session_key']
     */
    public static function code2Session($code, $appid, $secret)
    {
        $url = 'https://api.weixin.qq.com/sns/jscode2session'
             . '?appid=' . urlencode($appid)
             . '&secret=' . urlencode($secret)
             . '&js_code=' . urlencode($code)
             . '&grant_type=authorization_code';

        $resp = self::httpGet($url);
        if (!$resp) {
            Log::error('[Wechat] code2Session HTTP failed');
            return false;
        }

        $data = json_decode($resp, true);
        if (isset($data['errcode']) && $data['errcode'] != 0) {
            Log::error('[Wechat] code2Session error: ' . json_encode($data));
            return false;
        }

        if (!isset($data['openid'])) {
            return false;
        }

        return [
            'openid'      => $data['openid'],
            'unionid'     => $data['unionid'] ?? '',
            'session_key' => $data['session_key'] ?? '',
        ];
    }

    /**
     * GET 请求
     */
    private static function httpGet($url, $timeout = 10)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('[Wechat] curl error: ' . $err);
            return false;
        }
        return $resp;
    }
}
