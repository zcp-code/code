<?php

namespace app\common\library;

/**
 * 微信小程序用户管理类
 * Class WxUser
 * @package app\common\library\wechat
 */
class WxUser
{
    private $appId;
    private $appSecret;

    /**
     * 构造方法
     * WxUser constructor.
     * @param $appId
     * @param $appSecret
     */
    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    /**
     * 获取session_key
     * @param $code
     * @return array|mixed
     */
    public function sessionKey($code)
    {
        /**
         * code 换取 session_key
         * ​这是一个 HTTPS 接口，开发者服务器使用登录凭证 code 获取 session_key 和 openid。
         * 其中 session_key 是对用户数据进行加密签名的密钥。为了自身应用安全，session_key 不应该在网络上传输。
         */

        $url = 'https://api.weixin.qq.com/sns/jscode2session';

        $result = json_decode(curl_sessionkey($url, [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'grant_type' => 'authorization_code',
            'js_code' => $code
        ]), true);

        return isset($result['errcode']) ? [] : $result;
    }

    /**
     * 获取 access_token（带缓存）
     * @return string
     */
    public function getAccessToken()
    {
        $cacheKey = 'wx_access_token_' . $this->appId;
        $token = cache($cacheKey);

        if ($token) {
            return $token;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/token';
        $result = json_decode(curl_sessionkey($url, [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'grant_type' => 'client_credential'
        ]), true);

        if (isset($result['access_token'])) {
            cache($cacheKey, $result['access_token'], $result['expires_in'] - 300);
            return $result['access_token'];
        }

        return '';
    }

    /**
     * 通过 phoneCode 换取手机号（新版微信接口）
     * @param string $phoneCode 前端 getPhoneNumber 返回的 code
     * @return string 手机号；失败返回空字符串
     */
    public function getPhoneNumber($phoneCode)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return '';
        }

        $url = 'https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token=' . $accessToken;

        $postData = json_encode(['code' => $phoneCode]);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response, true);

        if (isset($result['errcode']) && $result['errcode'] === 0 && isset($result['phone_info']['purePhoneNumber'])) {
            return $result['phone_info']['purePhoneNumber'];
        }

        return '';
    }

}

