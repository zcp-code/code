<?php
/**
 * 二维码图片生成(GD)
 *
 * 当前实现:基于 GD 绘制"QR 风格"占位图(含店铺标识 + 模拟定位点)
 * ⚠️ 不是标准可扫的 QR,只是占位跑通流程。后续接入真 QR 库:
 *   composer require endroid/qr-code
 * 然后在本类 render() 里替换实现。
 */

namespace extend\gch;

class QrCode
{
    /**
     * 生成 base64 PNG(纯 PHP GD)
     *
     * @param string $data   QR 内容(目前用作显示文字,如 "shop:1")
     * @param string $shopName 店名(显示用)
     * @param int    $size   边长像素
     * @return string base64 PNG
     */
    public static function render($data, $shopName = '', $size = 360)
    {
        if (!extension_loaded('gd')) {
            throw new \Exception('GD 扩展未安装');
        }

        $img = imagecreatetruecolor($size, $size);
        // 背景白
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        $orange = imagecolorallocate($img, 255, 107, 53);
        imagefilledrectangle($img, 0, 0, $size, $size, $white);

        // 三个定位点(角) — 模拟 QR 的 finder pattern
        $finderSize = (int)($size * 0.22);
        $positions = [
            [10, 10],
            [$size - $finderSize - 10, 10],
            [10, $size - $finderSize - 10],
        ];
        foreach ($positions as [$x, $y]) {
            // 外框
            imagefilledrectangle($img, $x, $y, $x + $finderSize, $y + $finderSize, $black);
            // 内白框
            $innerPad = (int)($finderSize * 0.18);
            imagefilledrectangle(
                $img,
                $x + $innerPad, $y + $innerPad,
                $x + $finderSize - $innerPad, $y + $finderSize - $innerPad,
                $white
            );
            // 中心实心方块
            $centerPad = (int)($finderSize * 0.35);
            imagefilledrectangle(
                $img,
                $x + $centerPad, $y + $centerPad,
                $x + $finderSize - $centerPad, $y + $finderSize - $centerPad,
                $black
            );
        }

        // 中心区域:橙色边框 + 店名 + ID
        $cx = (int)($size / 2);
        $cy = (int)($size / 2);
        $boxW = (int)($size * 0.55);
        $boxH = (int)($size * 0.18);
        $boxX = $cx - (int)($boxW / 2);
        $boxY = $cy - (int)($boxH / 2);
        imagerectangle($img, $boxX, $boxY, $boxX + $boxW, $boxY + $boxH, $orange);

        $shopNameShort = mb_substr($shopName, 0, 6, 'UTF-8');
        imagestring($img, 5, $boxX + 10, $boxY + 8, $shopNameShort, $black);
        imagestring($img, 4, $boxX + 10, $boxY + 30, $data, $orange);

        // 顶部小字"扫码查看店铺"
        $tip = 'scan for shop';
        imagestring($img, 2, 12, $size - 18, $tip, $black);

        // 输出 base64
        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($png);
    }
}
