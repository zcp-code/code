<?php
namespace app\api\controller;

use extend\gch\Token;
use think\Env;

/**
 * 公共接口
 */
class Common extends ApiBase
{
    protected $requireLogin = false;

    /**
     * 系统配置
     */
    public function config()
    {
        return $this->success([
            'name'       => '仓货盘',
            'version'    => '1.0.0',
            'min_appid'  => Env::get('wechat.min_appid', ''),
            'image_host' => '', // 图片 CDN 域名
        ]);
    }

    /**
     * 静默续期 token(在 token 即将过期时调用,延长 7 天)
     * - 不需要新生成 token,直接 UPDATE expire_time
     * - 不修改任何业务数据
     * - 失败(401 等)不影响主流程
     *
     * 入参:无(从 header.Token 取当前 token)
     * 返回: { token, expire }  // token 字符串没变,主要是给前端同步本地 expire
     */
    public function refreshToken()
    {
        $token = $this->request->header('Token', '');
        if (empty($token)) {
            return $this->error('缺少 Token', 401);
        }

        $row = \think\Db::name('user_token')
            ->where('token', $token)
            ->where('expire_time', '>', time())
            ->find();
        if (!$row) {
            return $this->error('Token 已失效', 401);
        }

        // 续期 7 天
        $newExpire = time() + Token::TOKEN_EXPIRE;
        \think\Db::name('user_token')
            ->where('token', $token)
            ->update(['expire_time' => $newExpire]);

        return $this->success([
            'token'  => $token,
            'expire' => $newExpire,
            'role'   => $row['role'],
        ], '已续期');
    }

    /**
     * 上传图片（本地存储，public/uploads/）
     * 由 Nginx 直接提供静态资源（expires 30d）
     */
    public function upload()
    {
        $file = $this->request->file('file');
        if (!$file) return $this->error('未上传文件');

        $info = $file->validate(['size' => 5 * 1024 * 1024, 'ext' => 'jpg,jpeg,png,webp'])
            ->move(ROOT_PATH . 'public' . DS . 'uploads');
        if (!$info) return $this->error($file->getError());

        // 返回完整可访问 URL,前端 image 标签直接可用
        $relPath = '/uploads/' . str_replace('\\', '/', $info->getSaveName());
        $fullUrl = $this->request->domain() . $relPath;
        return $this->success(['url' => $fullUrl, 'path' => $relPath], '上传成功');
    }
}
