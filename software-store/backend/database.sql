-- ============================================================
-- 软件库 App 后台管理系统数据库脚本
-- 适用 MySQL 5.7+
-- 字符集：utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS `software_store` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `software_store`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. 管理员表 admin
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码(bcrypt加密)',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '姓名',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `role` varchar(20) NOT NULL DEFAULT 'admin' COMMENT '角色：super/admin',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1启用 0禁用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `last_login` datetime DEFAULT NULL COMMENT '最后登录时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员表';

-- ------------------------------------------------------------
-- 2. 用户表 user
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码(bcrypt加密)',
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '邮箱',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1启用 0禁用',
  `register_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  `last_login` datetime DEFAULT NULL COMMENT '最后登录时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_phone` (`phone`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- ------------------------------------------------------------
-- 3. 分类表 category
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序(越大越靠前)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1启用 0禁用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分类表';

-- ------------------------------------------------------------
-- 4. 软件表 software
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `software`;
CREATE TABLE `software` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(100) NOT NULL COMMENT '软件名称',
  `category_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `description` text COMMENT '软件描述',
  `version` varchar(50) NOT NULL DEFAULT '' COMMENT '版本号',
  `size` varchar(50) NOT NULL DEFAULT '' COMMENT '软件大小',
  `download_url` varchar(500) NOT NULL DEFAULT '' COMMENT '下载地址',
  `screenshots` text COMMENT '截图(JSON数组)',
  `download_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '下载次数',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序(越大越靠前)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1上架 0下架',
  `is_hot` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否热门：1是 0否',
  `is_recommend` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否推荐：1是 0否',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_hot` (`is_hot`),
  KEY `idx_recommend` (`is_recommend`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='软件表';

-- ------------------------------------------------------------
-- 5. 轮播图表 banner
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `banner`;
CREATE TABLE `banner` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `title` varchar(100) NOT NULL COMMENT '标题',
  `image` varchar(255) NOT NULL COMMENT '图片地址',
  `link` varchar(500) NOT NULL DEFAULT '' COMMENT '跳转链接',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序(越大越靠前)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1启用 0禁用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='轮播图表';

-- ------------------------------------------------------------
-- 6. 公告表 notice
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `notice`;
CREATE TABLE `notice` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `title` varchar(100) NOT NULL COMMENT '标题',
  `content` text COMMENT '内容',
  `type` varchar(20) NOT NULL DEFAULT 'static' COMMENT '类型：scroll滚动 static静态',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序(越大越靠前)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1启用 0禁用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公告表';

-- ------------------------------------------------------------
-- 7. 工具栏表 toolbar
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `toolbar`;
CREATE TABLE `toolbar` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(50) NOT NULL COMMENT '名称',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `link` varchar(500) NOT NULL DEFAULT '' COMMENT '跳转链接',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序(越大越靠前)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1启用 0禁用',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示：1是 0否',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='工具栏表';

-- ------------------------------------------------------------
-- 8. 广告表 advertisement
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `advertisement`;
CREATE TABLE `advertisement` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(100) NOT NULL COMMENT '广告名称',
  `image` varchar(255) NOT NULL COMMENT '广告图片',
  `link` varchar(500) NOT NULL DEFAULT '' COMMENT '跳转链接',
  `position` varchar(20) NOT NULL DEFAULT 'home' COMMENT '位置：splash启动页 home首页 detail详情页',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序(越大越靠前)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1启用 0禁用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_position` (`position`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='广告表';

-- ------------------------------------------------------------
-- 9. 收藏表 favorite
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `favorite`;
CREATE TABLE `favorite` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `software_id` int(11) unsigned NOT NULL COMMENT '软件ID',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_software` (`user_id`, `software_id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='收藏表';

-- ------------------------------------------------------------
-- 10. 下载记录表 download_record
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `download_record`;
CREATE TABLE `download_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `software_id` int(11) unsigned NOT NULL COMMENT '软件ID',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_software` (`software_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='下载记录表';

-- ------------------------------------------------------------
-- 11. 反馈表 feedback
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `content` text NOT NULL COMMENT '反馈内容',
  `contact` varchar(100) NOT NULL DEFAULT '' COMMENT '联系方式',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0待处理 1已处理',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='反馈表';

-- ------------------------------------------------------------
-- 12. 线报配置表 report_config
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `report_config`;
CREATE TABLE `report_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(100) NOT NULL COMMENT '线报名称',
  `source_url` varchar(500) NOT NULL COMMENT '数据源地址',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1启用 0禁用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='线报配置表';

-- ------------------------------------------------------------
-- 13. 系统配置表 config
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `config_key` varchar(100) NOT NULL COMMENT '配置键',
  `config_value` text COMMENT '配置值',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '配置描述',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统配置表';

-- ------------------------------------------------------------
-- 14. 验证码表 verification_code
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `verification_code`;
CREATE TABLE `verification_code` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `account` varchar(100) NOT NULL COMMENT '账号(手机号或邮箱)',
  `code` varchar(10) NOT NULL COMMENT '验证码',
  `type` varchar(20) NOT NULL DEFAULT 'register' COMMENT '类型：register注册 login登录',
  `expire_time` datetime NOT NULL COMMENT '过期时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0未使用 1已使用',
  PRIMARY KEY (`id`),
  KEY `idx_account` (`account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='验证码表';

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 初始种子数据
-- ============================================================

-- 管理员（账号：admin / 密码：admin123）
INSERT INTO `admin` (`username`, `password`, `name`, `avatar`, `role`, `status`, `create_time`) VALUES
('admin', '$2y$12$c9oOMqfA23tfSBlKUPswIeenodHF6y/sCdn3ak/Z3pS0YH0pqr97K', '超级管理员', '', 'super', 1, '2024-01-01 10:00:00');

-- 示例用户（密码均为 123456）
INSERT INTO `user` (`username`, `password`, `phone`, `email`, `avatar`, `nickname`, `status`, `register_time`) VALUES
('user001', '$2y$12$c9oOMqfA23tfSBlKUPswIeenodHF6y/sCdn3ak/Z3pS0YH0pqr97K', '13800138001', 'user001@example.com', '', '测试用户1', 1, '2024-02-01 09:00:00'),
('user002', '$2y$12$c9oOMqfA23tfSBlKUPswIeenodHF6y/sCdn3ak/Z3pS0YH0pqr97K', '13800138002', 'user002@example.com', '', '测试用户2', 1, '2024-02-05 14:30:00'),
('user003', '$2y$12$c9oOMqfA23tfSBlKUPswIeenodHF6y/sCdn3ak/Z3pS0YH0pqr97K', '13800138003', 'user003@example.com', '', '测试用户3', 0, '2024-03-01 10:00:00');

-- 示例分类
INSERT INTO `category` (`name`, `icon`, `sort`, `status`, `create_time`) VALUES
('系统工具', 'fas fa-cog', 100, 1, '2024-01-01 10:00:00'),
('影音播放', 'fas fa-film', 90, 1, '2024-01-01 10:00:00'),
('社交通讯', 'fas fa-comments', 80, 1, '2024-01-01 10:00:00'),
('游戏娱乐', 'fas fa-gamepad', 70, 1, '2024-01-01 10:00:00'),
('学习教育', 'fas fa-book', 60, 1, '2024-01-01 10:00:00'),
('生活服务', 'fas fa-shopping-bag', 50, 1, '2024-01-01 10:00:00'),
('办公软件', 'fas fa-briefcase', 40, 1, '2024-01-01 10:00:00'),
('网络安全', 'fas fa-shield-alt', 30, 1, '2024-01-01 10:00:00');

-- 示例软件
INSERT INTO `software` (`name`, `category_id`, `icon`, `description`, `version`, `size`, `download_url`, `screenshots`, `download_count`, `sort`, `status`, `is_hot`, `is_recommend`, `create_time`) VALUES
('应用宝', 1, '', '应用宝是腾讯应用中心倾力打造的手机应用商店，致力于为用户提供丰富、安全、优质的应用下载服务。', '8.5.2', '32.5MB', 'https://example.com/downloads/appmaster.apk', '["https://example.com/screenshot/1.jpg","https://example.com/screenshot/2.jpg"]', 125680, 100, 1, 1, 1, '2024-02-01 10:00:00'),
('酷狗音乐', 2, '', '酷狗音乐是中国领先的数字音乐交互服务提供商，海量正版高品质音乐，陪你听好歌。', '11.2.6', '68.8MB', 'https://example.com/downloads/kugou.apk', '["https://example.com/screenshot/3.jpg"]', 985320, 95, 1, 1, 1, '2024-02-02 11:00:00'),
('腾讯QQ', 3, '', '腾讯QQ是腾讯公司开发的一款基于Internet的即时通信软件，支持在线聊天、视频电话、点对点断点续传文件。', '8.9.78', '95.2MB', 'https://example.com/downloads/qq.apk', '["https://example.com/screenshot/4.jpg"]', 5689200, 90, 1, 1, 1, '2024-02-03 12:00:00'),
('王者荣耀', 4, '', '王者荣耀是腾讯游戏天美工作室群开发的MOBA类手机游戏，5V5王者峡谷公平对战。', '3.72.1.22', '3.5GB', 'https://example.com/downloads/wzry.apk', '["https://example.com/screenshot/5.jpg"]', 8956321, 88, 1, 1, 0, '2024-02-04 13:00:00'),
('有道词典', 5, '', '有道词典是网易有道公司出品的语言翻译软件，支持多语种翻译，单词记忆，学习助手。', '9.2.5', '45.6MB', 'https://example.com/downloads/youdao.apk', '[]', 456320, 85, 1, 0, 1, '2024-02-05 14:00:00'),
('美团', 6, '', '美团是一款生活服务类应用，提供外卖、酒店、电影票、休闲娱乐等本地生活服务。', '12.5.201', '88.2MB', 'https://example.com/downloads/meituan.apk', '[]', 3256800, 82, 1, 1, 0, '2024-02-06 15:00:00'),
('WPS Office', 7, '', 'WPS Office是一套办公软件套装，可以实现办公软件最常用的文字、表格、演示等多种功能。', '14.2.0', '52.8MB', 'https://example.com/downloads/wps.apk', '[]', 2896500, 78, 1, 0, 1, '2024-02-07 16:00:00'),
('360安全卫士', 8, '', '360安全卫士是一款功能强、效果好、受用户欢迎的安全杀毒软件。', '10.0.0', '38.6MB', 'https://example.com/downloads/360.apk', '[]', 6789300, 75, 1, 1, 0, '2024-02-08 17:00:00'),
('网易云音乐', 2, '', '网易云音乐是一款专注于发现与分享的音乐产品，依托专业音乐人、DJ、好友推荐及社交功能。', '8.8.20', '78.5MB', 'https://example.com/downloads/netease_music.apk', '[]', 4563200, 72, 1, 0, 1, '2024-02-09 10:00:00'),
('微信', 3, '', '微信是腾讯公司推出的一个为智能终端提供即时通讯服务的免费应用程序。', '8.0.43', '256.8MB', 'https://example.com/downloads/wechat.apk', '[]', 12345678, 70, 1, 1, 1, '2024-02-10 11:00:00');

-- 示例轮播图
INSERT INTO `banner` (`title`, `image`, `link`, `sort`, `status`, `create_time`) VALUES
('每日推荐精品应用', 'https://example.com/banner/1.jpg', '/software/detail?id=1', 100, 1, '2024-02-01 10:00:00'),
('限时热门游戏', 'https://example.com/banner/2.jpg', '/software/detail?id=4', 90, 1, '2024-02-02 10:00:00'),
('办公效率神器', 'https://example.com/banner/3.jpg', '/software/detail?id=7', 80, 1, '2024-02-03 10:00:00');

-- 示例公告
INSERT INTO `notice` (`title`, `content`, `type`, `sort`, `status`, `create_time`) VALUES
('欢迎使用软件库App', '欢迎使用软件库App，这里汇聚海量优质应用，安全下载，畅享精彩。', 'static', 100, 1, '2024-02-01 10:00:00'),
('新版上线通知', '软件库App已更新至2.0版本，新增智能推荐、下载加速等功能，欢迎体验。', 'static', 90, 1, '2024-02-15 10:00:00'),
('温馨提示：请认准官方渠道下载', '为保障您的设备安全，请通过本应用官方渠道下载软件。', 'scroll', 80, 1, '2024-02-20 10:00:00');

-- 示例工具栏
INSERT INTO `toolbar` (`name`, `icon`, `link`, `sort`, `status`, `is_show`, `create_time`) VALUES
('首页', 'fas fa-home', '/home', 100, 1, 1, '2024-02-01 10:00:00'),
('分类', 'fas fa-th-large', '/category', 90, 1, 1, '2024-02-01 10:00:00'),
('下载', 'fas fa-download', '/download', 80, 1, 1, '2024-02-01 10:00:00'),
('我的', 'fas fa-user', '/profile', 70, 1, 1, '2024-02-01 10:00:00');

-- 示例广告
INSERT INTO `advertisement` (`name`, `image`, `link`, `position`, `sort`, `status`, `create_time`) VALUES
('启动页广告', 'https://example.com/ad/splash.jpg', '/software/detail?id=10', 'splash', 100, 1, '2024-02-01 10:00:00'),
('首页横幅广告', 'https://example.com/ad/home.jpg', '/software/detail?id=2', 'home', 90, 1, '2024-02-01 10:00:00'),
('详情页广告', 'https://example.com/ad/detail.jpg', '/software/detail?id=3', 'detail', 80, 1, '2024-02-01 10:00:00');

-- 示例收藏
INSERT INTO `favorite` (`user_id`, `software_id`, `create_time`) VALUES
(1, 1, '2024-03-01 10:00:00'),
(1, 3, '2024-03-02 10:00:00'),
(2, 2, '2024-03-03 10:00:00');

-- 示例下载记录
INSERT INTO `download_record` (`user_id`, `software_id`, `create_time`) VALUES
(1, 1, '2024-03-01 10:00:00'),
(1, 2, '2024-03-01 11:00:00'),
(1, 3, '2024-03-02 10:00:00'),
(2, 1, '2024-03-03 10:00:00'),
(2, 4, '2024-03-03 11:00:00');

-- 示例反馈
INSERT INTO `feedback` (`user_id`, `content`, `contact`, `status`, `create_time`) VALUES
(1, '建议增加软件评分功能，方便用户参考。', '13800138001', 1, '2024-03-05 10:00:00'),
(2, '下载速度有时候比较慢，希望优化。', 'user002@example.com', 0, '2024-03-06 14:00:00');

-- 示例线报配置
INSERT INTO `report_config` (`name`, `source_url`, `status`, `create_time`) VALUES
('官方软件更新源', 'https://example.com/api/report/official', 1, '2024-02-01 10:00:00'),
('第三方软件源', 'https://example.com/api/report/third', 0, '2024-02-02 10:00:00');

-- 系统配置
INSERT INTO `config` (`config_key`, `config_value`, `description`) VALUES
('site_name', '软件库', '站点名称'),
('site_description', '海量优质应用，安全下载', '站点描述'),
('site_logo', '', '站点Logo'),
('app_version', '2.0.0', 'App当前版本'),
('phone_verify', '1', '是否开启手机验证：1是 0否'),
('email_verify', '0', '是否开启邮箱验证：1是 0否'),
('register_switch', '1', '是否允许注册：1是 0否'),
('upload_path', 'uploads', '上传目录'),
('upload_max_size', '104857600', '最大上传大小(字节)'),
('icp', '京ICP备12345678号', '备案信息'),
('contact_email', 'support@example.com', '联系邮箱'),
('contact_phone', '400-888-8888', '联系电话');
