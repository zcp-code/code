<?php
// 用 PHP GD 生成 tabBar 占位 PNG
// 4 个 tab × 2 状态(正常/选中) = 8 个 PNG

$tabs = [
  'home'      => ['🏠', '首页'],
  'shops'     => ['🏪', '店铺'],
  'favorites' => ['★', '收藏'],
  'profile'   => ['👤', '我的']
];

$size = 81;
$normal_color   = [0x99, 0x99, 0x99]; // 灰色
$selected_color = [0xff, 0x6b, 0x35]; // 橙色

foreach ($tabs as $name => [$emoji, $label]) {
  foreach (['normal' => $normal_color, 'a' => $selected_color] as $suffix => $rgb) {
    $im = imagecreatetruecolor($size, $size);
    // 透明背景
    imagesavealpha($im, true);
    $bg = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $bg);

    // 圆背景(选中态)
    if ($suffix === 'a') {
      $fill = imagecolorallocate($im, ...$rgb);
      imagefilledellipse($im, 40, 40, 70, 70, $fill);
    }

    // 文字色
    $fg = imagecolorallocate($im, ...$rgb);

    // 用 imagettftext 绘制 emoji(用系统字体)
    $font = 'C:/Windows/Fonts/seguiemj.ttf';
    if (!file_exists($font)) {
      $font = 'C:/Windows/Fonts/arial.ttf';
    }
    imagettftext($im, 32, 0, 25, 55, $fg, $font, $emoji);

    $file = sprintf('%s_%s.png', $name, $suffix);
    imagepng($im, __DIR__ . '/' . $file);
    imagedestroy($im);
    echo "生成: $file\n";
  }
}
echo "8 个 tabBar 图标已生成在 static/tabbar/\n";
