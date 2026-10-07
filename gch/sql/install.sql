-- =====================================================================
-- 仓货盘小程序 - 数据库初始化脚本
-- 数据库：gch_dev（开发环境），gch（生产环境）
-- 表前缀：fy_
-- MySQL：>= 5.7 ，utf8mb4
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- 一、FastAdmin 系统表（前缀从 fa_ 改为 fy_）
-- =====================================================================

-- 管理员表
DROP TABLE IF EXISTS `fy_admin`;
CREATE TABLE `fy_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(30) NOT NULL DEFAULT '' COMMENT '密码盐',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '邮箱',
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `loginfailure` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '失败次数',
  `logintime` int(10) unsigned DEFAULT NULL COMMENT '登录时间',
  `loginip` varchar(50) DEFAULT NULL COMMENT '登录IP',
  `token` varchar(59) DEFAULT '' COMMENT 'Session标识',
  `status` varchar(30) NOT NULL DEFAULT 'normal' COMMENT '状态',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='管理员表';

-- 角色表
DROP TABLE IF EXISTS `fy_auth_group`;
CREATE TABLE `fy_auth_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父组别',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '组名',
  `rules` text NOT NULL COMMENT '权限规则ID',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `status` varchar(30) NOT NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COMMENT='角色组';

-- 角色成员表
DROP TABLE IF EXISTS `fy_auth_group_access`;
CREATE TABLE `fy_auth_group_access` (
  `uid` int(10) unsigned NOT NULL COMMENT '成员ID',
  `group_id` int(10) unsigned NOT NULL COMMENT '角色ID',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色成员';

-- 权限规则表
DROP TABLE IF EXISTS `fy_auth_rule`;
CREATE TABLE `fy_auth_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('menu_dir','menu','button') NOT NULL DEFAULT 'menu' COMMENT '类型',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标',
  `condition` varchar(255) NOT NULL DEFAULT '' COMMENT '条件',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `ismenu` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否菜单',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` varchar(30) NOT NULL DEFAULT '' COMMENT '状态',
  -- 仓货盘 GCH 项目补充：FastAdmin Auth.php 第 482/483 行访问该字段，必须存在
  `menutype` enum('addtabs','blank','dialog','ajax') DEFAULT NULL COMMENT '菜单类型',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`) USING BTREE,
  KEY `pid` (`pid`),
  KEY `weigh` (`weigh`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COMMENT='菜单和权限规则';

-- 管理员日志
DROP TABLE IF EXISTS `fy_admin_log`;
CREATE TABLE `fy_admin_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '管理员名字',
  `url` varchar(1500) NOT NULL DEFAULT '' COMMENT '操作页面',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '日志标题',
  `content` text NOT NULL COMMENT '内容',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `useragent` varchar(255) NOT NULL DEFAULT '' COMMENT 'User-Agent',
  `createtime` int(10) DEFAULT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `name` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员日志';

-- 附件表
DROP TABLE IF EXISTS `fy_attachment`;
CREATE TABLE `fy_attachment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(50) DEFAULT NULL COMMENT '类别',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '物理路径',
  `imagewidth` varchar(30) NOT NULL DEFAULT '' COMMENT '宽度',
  `imageheight` varchar(30) NOT NULL DEFAULT '' COMMENT '高度',
  `imagetype` varchar(30) NOT NULL DEFAULT '' COMMENT '图片类型',
  `imageframes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图片帧数',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `mimetype` varchar(100) NOT NULL DEFAULT '' COMMENT 'mime类型',
  `extparam` varchar(255) NOT NULL DEFAULT '' COMMENT '透传数据',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `uploadtime` int(10) DEFAULT NULL COMMENT '上传时间',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '原始文件名',
  `storage` varchar(100) NOT NULL DEFAULT 'local' COMMENT '存储位置',
  `sha1` varchar(40) NOT NULL DEFAULT '' COMMENT 'SHA1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='附件表';

-- 配置表
DROP TABLE IF EXISTS `fy_config`;
CREATE TABLE `fy_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '变量名',
  `group` varchar(30) NOT NULL DEFAULT '' COMMENT '分组',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '变量标题',
  `tip` varchar(100) NOT NULL DEFAULT '' COMMENT '变量描述',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '类型:string,number,radio,checkbox,switch,array,text',
  `value` text COMMENT '变量值',
  `content` text COMMENT '变量字典数据',
  `rule` varchar(100) DEFAULT '' COMMENT '验证规则',
  `extend` varchar(255) DEFAULT '' COMMENT '扩展属性',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COMMENT='配置表';

-- 短信验证码表（保留为通用工具，**本项目业务不使用**）
DROP TABLE IF EXISTS `fy_sms`;
CREATE TABLE `fy_sms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `event` varchar(30) NOT NULL DEFAULT '' COMMENT '事件',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
  `code` varchar(10) NOT NULL DEFAULT '' COMMENT '验证码',
  `times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '验证次数',
  `ip` varchar(30) NOT NULL DEFAULT '' COMMENT 'IP',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='短信验证码';

-- 地区表
DROP TABLE IF EXISTS `fy_area`;
CREATE TABLE `fy_area` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(10) DEFAULT NULL COMMENT '父ID',
  `shortname` varchar(100) DEFAULT NULL COMMENT '简称',
  `name` varchar(100) DEFAULT NULL COMMENT '名称',
  `mergename` varchar(255) DEFAULT NULL COMMENT '全称',
  `level` tinyint(1) DEFAULT NULL COMMENT '层级:1=省,2=市,3=区/县',
  `pinyin` varchar(100) DEFAULT NULL COMMENT '拼音',
  `code` varchar(100) DEFAULT NULL COMMENT '区号',
  `zip` varchar(100) DEFAULT NULL COMMENT '邮编',
  `first` varchar(50) DEFAULT NULL COMMENT '首字母',
  `lng` varchar(50) DEFAULT NULL COMMENT '经度',
  `lat` varchar(50) DEFAULT NULL COMMENT '纬度',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='地区表';

-- 分类表（FastAdmin 通用分类，**与业务货品分类 fy_category 不同**）
DROP TABLE IF EXISTS `fy_category`;
CREATE TABLE `fy_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '类型',
  `name` varchar(50) NOT NULL DEFAULT '',
  `nickname` varchar(50) NOT NULL DEFAULT '',
  `flag` set('hot','index','recommend') NOT NULL DEFAULT '',
  `image` varchar(100) NOT NULL DEFAULT '' COMMENT '图片',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `diyname` varchar(50) NOT NULL DEFAULT '' COMMENT '自定义名称',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` varchar(30) NOT NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分类表';

-- =====================================================================
-- 二、业务表
-- =====================================================================

-- 游客（微信登录用户）
DROP TABLE IF EXISTS `fy_visitor`;
CREATE TABLE `fy_visitor` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(64) NOT NULL COMMENT '微信openid(唯一)',
  `unionid` varchar(64) DEFAULT NULL COMMENT '微信unionid',
  `nickname` varchar(64) DEFAULT NULL COMMENT '微信昵称',
  `avatar` varchar(255) DEFAULT NULL COMMENT '微信头像',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态:0拉黑1正常',
  `last_login_time` int(10) unsigned DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(64) DEFAULT NULL,
  `createtime` int(10) unsigned DEFAULT NULL,
  `updatetime` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_openid` (`openid`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='游客(微信登录用户)';

-- 采购商（账号密码登录）
DROP TABLE IF EXISTS `fy_buyer`;
CREATE TABLE `fy_buyer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account` varchar(64) NOT NULL COMMENT '账号(管理员预创建,唯一)',
  `password` varchar(128) NOT NULL COMMENT '密码哈希(bcrypt)',
  `salt` varchar(32) NOT NULL DEFAULT '' COMMENT '密码盐',
  `real_name` varchar(64) DEFAULT NULL COMMENT '姓名',
  `mobile` varchar(20) DEFAULT NULL COMMENT '手机号(联系用,非唯一)',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态:0停用1启用',
  `last_login_time` int(10) unsigned DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(64) DEFAULT NULL,
  `loginfailure` tinyint(1) NOT NULL DEFAULT '0' COMMENT '登录失败次数',
  `createtime` int(10) unsigned DEFAULT NULL,
  `updatetime` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_account` (`account`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='采购商';

-- 批发商（账号密码登录）
DROP TABLE IF EXISTS `fy_wholesaler`;
CREATE TABLE `fy_wholesaler` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account` varchar(64) NOT NULL COMMENT '账号(管理员预创建,唯一)',
  `password` varchar(128) NOT NULL COMMENT '密码哈希(bcrypt)',
  `salt` varchar(32) NOT NULL DEFAULT '' COMMENT '密码盐',
  `shop_id` int(10) unsigned DEFAULT NULL COMMENT '所属店铺',
  `real_name` varchar(64) DEFAULT NULL COMMENT '负责人',
  `mobile` varchar(20) DEFAULT NULL COMMENT '手机号',
  `contact_phone` varchar(20) DEFAULT NULL COMMENT '联系电话(店铺)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态:0停用1启用',
  `last_login_time` int(10) unsigned DEFAULT NULL,
  `last_login_ip` varchar(64) DEFAULT NULL,
  `loginfailure` tinyint(1) NOT NULL DEFAULT '0' COMMENT '登录失败次数',
  `createtime` int(10) unsigned DEFAULT NULL,
  `updatetime` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_account` (`account`),
  KEY `idx_mobile` (`mobile`),
  KEY `idx_shop` (`shop_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='批发商';

-- 货品分类（业务分类）
DROP TABLE IF EXISTS `fy_product_category`;
CREATE TABLE `fy_product_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL COMMENT '分类名',
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序(升序)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0停用1启用',
  `createtime` int(10) unsigned DEFAULT NULL,
  `updatetime` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  KEY `idx_status_sort` (`status`,`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='货品分类';

-- 店铺
DROP TABLE IF EXISTS `fy_shop`;
CREATE TABLE `fy_shop` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL COMMENT '店铺名',
  `logo` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL COMMENT '位置',
  `longitude` decimal(10,6) DEFAULT NULL,
  `latitude` decimal(10,6) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL COMMENT '联系电话',
  `business_hours` varchar(64) DEFAULT NULL COMMENT '营业时间',
  `intro` text COMMENT '简介',
  `qrcode_url` varchar(255) DEFAULT NULL COMMENT '店铺二维码URL(小程序二维码)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0停用1启用',
  `createtime` int(10) unsigned DEFAULT NULL,
  `updatetime` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='店铺';

-- 货盘
DROP TABLE IF EXISTS `fy_goods`;
CREATE TABLE `fy_goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `goods_no` varchar(32) NOT NULL COMMENT '货盘编号',
  `wholesaler_id` int(10) unsigned NOT NULL COMMENT '发布批发商',
  `shop_id` int(10) unsigned NOT NULL COMMENT '所属店铺',
  `category_id` int(10) unsigned NOT NULL COMMENT '分类ID',
  `name` varchar(128) NOT NULL COMMENT '品名',
  `price` decimal(10,2) NOT NULL COMMENT '单价',
  `unit` varchar(16) NOT NULL COMMENT '单位(斤/箱/个)',
  `total_stock` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总库存',
  `reserved_quantity` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已预订数量(含待确认+已确认)',
  `description` text COMMENT '描述',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0下架1在售2售罄',
  `publish_time` int(10) unsigned DEFAULT NULL COMMENT '发布时间',
  `createtime` int(10) unsigned DEFAULT NULL,
  `updatetime` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_goods_no` (`goods_no`),
  KEY `idx_wholesaler` (`wholesaler_id`),
  KEY `idx_shop` (`shop_id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_status_publish` (`status`,`publish_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='货盘';

-- 货盘图片
DROP TABLE IF EXISTS `fy_goods_image`;
CREATE TABLE `fy_goods_image` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` int(10) unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  `sort` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_goods` (`goods_id`,`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='货盘图片';

-- 预订（订单）
DROP TABLE IF EXISTS `fy_reservation`;
CREATE TABLE `fy_reservation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_no` varchar(32) NOT NULL COMMENT '预订编号',
  `goods_id` int(10) unsigned NOT NULL,
  `buyer_id` int(10) unsigned NOT NULL COMMENT '采购商',
  `wholesaler_id` int(10) unsigned NOT NULL COMMENT '批发商',
  `shop_id` int(10) unsigned NOT NULL COMMENT '店铺',
  `goods_name` varchar(128) NOT NULL COMMENT '冗余:货品名',
  `price` decimal(10,2) NOT NULL COMMENT '冗余:下单单价',
  `unit` varchar(16) NOT NULL COMMENT '冗余:单位',
  `quantity` int(10) unsigned NOT NULL COMMENT '预订数量',
  `status` varchar(16) NOT NULL DEFAULT 'pending' COMMENT 'pending/confirmed/cancelled',
  `confirm_time` int(10) unsigned DEFAULT NULL,
  `cancel_time` int(10) unsigned DEFAULT NULL,
  `cancel_reason` varchar(255) DEFAULT NULL COMMENT '取消原因',
  `cancel_role` varchar(16) DEFAULT NULL COMMENT 'buyer/wholesaler/admin/system',
  `reserve_time` int(10) unsigned DEFAULT NULL COMMENT '预订时间',
  `createtime` int(10) unsigned DEFAULT NULL,
  `updatetime` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_reservation_no` (`reservation_no`),
  KEY `idx_buyer` (`buyer_id`,`status`),
  KEY `idx_wholesaler` (`wholesaler_id`,`status`),
  KEY `idx_goods` (`goods_id`),
  KEY `idx_status_time` (`status`,`createtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='预订(订单)';

-- 收藏货品
DROP TABLE IF EXISTS `fy_favorite_goods`;
CREATE TABLE `fy_favorite_goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `buyer_id` int(10) unsigned NOT NULL,
  `goods_id` int(10) unsigned NOT NULL,
  `createtime` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_buyer_goods` (`buyer_id`,`goods_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='收藏货品';

-- 收藏店铺
DROP TABLE IF EXISTS `fy_favorite_shop`;
CREATE TABLE `fy_favorite_shop` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `buyer_id` int(10) unsigned NOT NULL,
  `shop_id` int(10) unsigned NOT NULL,
  `createtime` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_buyer_shop` (`buyer_id`,`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='收藏店铺';

-- 拨打电话日志
DROP TABLE IF EXISTS `fy_call_log`;
CREATE TABLE `fy_call_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `caller_role` varchar(16) DEFAULT NULL COMMENT 'visitor/buyer/wholesaler/guest',
  `caller_id` int(10) unsigned DEFAULT NULL COMMENT '拨打方ID,NULL=未登录',
  `wholesaler_id` int(10) unsigned DEFAULT NULL,
  `shop_id` int(10) unsigned DEFAULT NULL,
  `phone` varchar(20) NOT NULL COMMENT '被拨号码',
  `ip` varchar(64) DEFAULT NULL,
  `createtime` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_caller` (`caller_role`,`caller_id`),
  KEY `idx_wholesaler` (`wholesaler_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='拨打电话日志';

-- Token 表（采购商/批发商/游客的登录 Token）
DROP TABLE IF EXISTS `fy_user_token`;
CREATE TABLE `fy_user_token` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role` varchar(16) NOT NULL COMMENT 'visitor/buyer/wholesaler',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `token` varchar(64) NOT NULL COMMENT 'Token值',
  `expire_time` int(10) unsigned NOT NULL COMMENT '过期时间',
  `ip` varchar(64) DEFAULT NULL,
  `createtime` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token` (`token`),
  KEY `idx_role_user` (`role`,`user_id`),
  KEY `idx_expire` (`expire_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户Token表';

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- 三、初始数据
-- =====================================================================

-- 默认超级管理员（账号：admin  密码：123456）
-- FastAdmin 默认密码算法：md5(md5(password) + salt)
-- 哈希值：md5(md5('123456') + 'rUXqWa') = 212ce36f967715de79fddec77ed55a41
INSERT INTO `fy_admin` VALUES
(1, 'admin', 'Admin', '212ce36f967715de79fddec77ed55a41', 'rUXqWa', '/assets/img/avatar.png', 'admin@admin.com', '13800000000', 0, UNIX_TIMESTAMP(), '127.0.0.1', '', 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 初始角色：超级管理员组
INSERT INTO `fy_auth_group` VALUES
(1, 0, '超级管理员组', '*', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'normal'),
(2, 1, '采购商管理员', '1,2,3,4,5,6', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'normal');

-- 默认管理员归属超级管理组
INSERT INTO `fy_auth_group_access` VALUES (1, 1);

-- 初始菜单（参考 FastAdmin 标准菜单 + 本项目业务菜单）
INSERT INTO `fy_auth_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`) VALUES
-- 仪表盘
(1, 'menu_dir', 0, 'dashboard', '仪表盘', 'fa fa-tachometer', '', '仪表盘', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 150, 'normal'),
(2, 'menu', 1, 'dashboard/index', '查看', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 150, 'normal'),
-- 仓货盘业务菜单（顶级）
(10, 'menu_dir', 0, 'gch', '仓货盘管理', 'fa fa-cubes', '', '本项目业务', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 100, 'normal'),
-- 控制台
(11, 'menu', 10, 'gch/dashboard', '控制台', 'fa fa-tachometer', '', '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 99, 'normal'),
(12, 'menu', 11, 'gch/dashboard/index', '查看', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 99, 'normal'),
-- 货品分类
(20, 'menu', 10, 'gch/product_category', '货品分类', 'fa fa-list', '', '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 90, 'normal'),
(21, 'menu', 20, 'gch/product_category/index', '查看', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 90, 'normal'),
(22, 'menu', 20, 'gch/product_category/add', '添加', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 89, 'normal'),
(23, 'menu', 20, 'gch/product_category/edit', '编辑', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 88, 'normal'),
(24, 'menu', 20, 'gch/product_category/del', '删除', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 87, 'normal'),
-- 店铺管理
(30, 'menu', 10, 'gch/shop', '店铺管理', 'fa fa-shopping-bag', '', '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 85, 'normal'),
(31, 'menu', 30, 'gch/shop/index', '查看', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 85, 'normal'),
(32, 'menu', 30, 'gch/shop/add', '添加', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 84, 'normal'),
(33, 'menu', 30, 'gch/shop/edit', '编辑', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 83, 'normal'),
(34, 'menu', 30, 'gch/shop/del', '删除', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 82, 'normal'),
-- 货盘管理
(40, 'menu', 10, 'gch/goods', '货盘管理', 'fa fa-th-large', '', '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 80, 'normal'),
(41, 'menu', 40, 'gch/goods/index', '查看', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 80, 'normal'),
(42, 'menu', 40, 'gch/goods/edit', '编辑', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 79, 'normal'),
(43, 'menu', 40, 'gch/goods/status', '强制下架', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 78, 'normal'),
-- 预订管理
(50, 'menu', 10, 'gch/reservation', '预订管理', 'fa fa-calendar', '', '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 75, 'normal'),
(51, 'menu', 50, 'gch/reservation/index', '查看', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 75, 'normal'),
(52, 'menu', 50, 'gch/reservation/export', '导出', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 74, 'normal'),
-- 用户管理 - 采购商
(60, 'menu', 10, 'gch/buyer', '采购商管理', 'fa fa-users', '', '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 70, 'normal'),
(61, 'menu', 60, 'gch/buyer/index', '查看', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 70, 'normal'),
(62, 'menu', 60, 'gch/buyer/add', '添加', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 69, 'normal'),
(63, 'menu', 60, 'gch/buyer/edit', '编辑', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 68, 'normal'),
(64, 'menu', 60, 'gch/buyer/status', '启用/停用', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 67, 'normal'),
(65, 'menu', 60, 'gch/buyer/resetPwd', '重置密码', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 66, 'normal'),
-- 用户管理 - 批发商
(70, 'menu', 10, 'gch/wholesaler', '批发商管理', 'fa fa-user-md', '', '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 65, 'normal'),
(71, 'menu', 70, 'gch/wholesaler/index', '查看', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 65, 'normal'),
(72, 'menu', 70, 'gch/wholesaler/add', '添加', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 64, 'normal'),
(73, 'menu', 70, 'gch/wholesaler/edit', '编辑', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 63, 'normal'),
(74, 'menu', 70, 'gch/wholesaler/status', '启用/停用', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 62, 'normal'),
(75, 'menu', 70, 'gch/wholesaler/resetPwd', '重置密码', 'fa fa-circle-o', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 61, 'normal');

-- =====================================================================
-- 四、初始货品分类（4 个）
-- =====================================================================
INSERT INTO `fy_product_category` (`name`, `icon`, `sort`, `status`, `createtime`, `updatetime`) VALUES
('水果', '', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('蔬菜', '', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('海鲜', '', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('其他', '', 4, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- =====================================================================
-- 五、系统配置
-- =====================================================================
INSERT INTO `fy_config` (`name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`, `createtime`, `updatetime`) VALUES
('name', 'basic', '站点名称', '站点名称', 'string', '仓货盘小程序', '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('version', 'basic', '版本号', '系统版本号', 'string', '1.0.0', '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('timezone', 'basic', '时区', '系统时区', 'string', 'Asia/Shanghai', '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('min_appid', 'wechat', '小程序 AppID', '微信小程序 AppID', 'string', '', '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('min_secret', 'wechat', '小程序 AppSecret', '微信小程序 AppSecret', 'string', '', '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('call_log_keep_days', 'business', '通话记录保留天数', '拨号日志保留天数', 'number', '90', '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('token_expire', 'business', 'Token 有效期（秒）', '默认 7 天', 'number', '604800', '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- =====================================================================
-- 初始化完成
-- =====================================================================
SELECT '✅ 仓货盘小程序数据库初始化完成' AS message;
SELECT '默认管理员账号: admin  密码: 123456' AS info;
SELECT '⚠️ 首次登录后请立即修改密码！' AS warning;
