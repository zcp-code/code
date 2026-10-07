<?php
namespace app\api\controller;

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
