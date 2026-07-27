-- ============================================================
-- 软件商店数据库 Schema
-- 引擎: InnoDB | 字符集: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS `software_store`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `software_store`;

-- -----------------------------------------------------------
-- 1. admin - 管理员表
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(64)  NOT NULL COMMENT '登录名',
  `password`      VARCHAR(255) NOT NULL COMMENT 'PHP password_hash',
  `nickname`      VARCHAR(64)  NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar`        VARCHAR(512) NOT NULL DEFAULT '' COMMENT '头像 URL',
  `role`          TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '角色:1=超级管理员,2=普通管理员',
  `status`        TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `last_login_at` DATETIME     DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` VARCHAR(64)  NOT NULL DEFAULT '' COMMENT '最后登录 IP',
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员';

-- -----------------------------------------------------------
-- 2. user - 用户表
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(64)  NOT NULL COMMENT '登录名',
  `password`      VARCHAR(255) NOT NULL COMMENT 'PHP password_hash',
  `nickname`      VARCHAR(64)  NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar`        VARCHAR(512) NOT NULL DEFAULT '' COMMENT '头像 URL',
  `email`         VARCHAR(128) NOT NULL DEFAULT '' COMMENT '邮箱',
  `phone`         VARCHAR(32)  NOT NULL DEFAULT '' COMMENT '手机号',
  `gender`        TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '性别:0=未知,1=男,2=女',
  `birthday`      DATE DEFAULT NULL COMMENT '生日',
  `signature`     VARCHAR(512) NOT NULL DEFAULT '' COMMENT '个性签名',
  `balance`       DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT '余额',
  `status`        TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `last_login_at` DATETIME     DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` VARCHAR(64)  NOT NULL DEFAULT '' COMMENT '最后登录 IP',
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_phone` (`phone`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户';

-- -----------------------------------------------------------
-- 3. category - 分类表
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id`   INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级 ID,0=顶级',
  `name`        VARCHAR(64)  NOT NULL COMMENT '分类名称',
  `icon`        VARCHAR(512) NOT NULL DEFAULT '' COMMENT '图标 URL',
  `image`       VARCHAR(512) NOT NULL DEFAULT '' COMMENT '分类图片',
  `keywords`    VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'SEO 关键词',
  `sort`        INT          NOT NULL DEFAULT 0 COMMENT '排序,越大越靠前',
  `status`      TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `software_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '软件数量(冗余)',
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_sort` (`sort`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='软件分类';

-- -----------------------------------------------------------
-- 4. software - 软件表
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `software`;
CREATE TABLE `software` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id`     INT UNSIGNED NOT NULL COMMENT '分类 ID',
  `name`            VARCHAR(128) NOT NULL COMMENT '软件名称',
  `subtitle`        VARCHAR(255) NOT NULL DEFAULT '' COMMENT '副标题/一句话介绍',
  `description`     TEXT COMMENT '详细介绍(富文本)',
  `icon`            VARCHAR(512) NOT NULL DEFAULT '' COMMENT '图标 URL',
  `cover`           VARCHAR(512) NOT NULL DEFAULT '' COMMENT '封面图 URL',
  `images`          JSON         DEFAULT NULL COMMENT '截图数组 JSON',
  `version`         VARCHAR(32)  NOT NULL DEFAULT '1.0.0' COMMENT '当前版本',
  `size`            BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '大小(字节)',
  `download_url`    VARCHAR(1024) NOT NULL DEFAULT '' COMMENT '下载链接',
  `download_count`  INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '下载量',
  `view_count`      INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '浏览量',
  `like_count`      INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '点赞数',
  `comment_count`   INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '评论数',
  `rating`          DECIMAL(3,2) NOT NULL DEFAULT 0.00 COMMENT '平均评分',
  `rating_count`    INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '评分人数',
  `price`           DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT '价格,0=免费',
  `is_free`         TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否免费:0=否,1=是',
  `is_recommend`    TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否推荐:0=否,1=是',
  `is_hot`          TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否热门:0=否,1=是',
  `is_new`          TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否最新:0=否,1=是',
  `platform`        VARCHAR(32)  NOT NULL DEFAULT '' COMMENT '支持平台:windows/macos/linux/ios/android',
  `language`        VARCHAR(32)  NOT NULL DEFAULT '' COMMENT '语言',
  `developer`       VARCHAR(128) NOT NULL DEFAULT '' COMMENT '开发者',
  `website`         VARCHAR(512) NOT NULL DEFAULT '' COMMENT '官网',
  `tags`            VARCHAR(512) NOT NULL DEFAULT '' COMMENT '标签,逗号分隔',
  `sort`            INT          NOT NULL DEFAULT 0 COMMENT '排序',
  `status`          TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=下架,1=上架',
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_download_count` (`download_count`),
  KEY `idx_view_count` (`view_count`),
  KEY `idx_is_recommend` (`is_recommend`),
  KEY `idx_is_hot` (`is_hot`),
  KEY `idx_is_new` (`is_new`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_name` (`name`),
  FULLTEXT KEY `ft_name_desc` (`name`, `subtitle`, `description`),
  CONSTRAINT `fk_software_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='软件';

-- -----------------------------------------------------------
-- 5. banner - 轮播图表
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `banner`;
CREATE TABLE `banner` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(128) NOT NULL DEFAULT '' COMMENT '标题',
  `image`      VARCHAR(512) NOT NULL COMMENT '图片 URL',
  `link_type`  TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '跳转类型:1=软件详情,2=分类,3=外链',
  `link_value` VARCHAR(512) NOT NULL DEFAULT '' COMMENT '跳转值(软件ID/分类ID/URL)',
  `position`   VARCHAR(32)  NOT NULL DEFAULT 'home' COMMENT '位置:home=首页',
  `sort`       INT          NOT NULL DEFAULT 0 COMMENT '排序',
  `status`     TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `start_at`   DATETIME     DEFAULT NULL COMMENT '生效开始时间',
  `end_at`     DATETIME     DEFAULT NULL COMMENT '生效结束时间',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_position_status` (`position`, `status`),
  KEY `idx_sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='轮播图';

-- -----------------------------------------------------------
-- 6. notice - 公告表
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `notice`;
CREATE TABLE `notice` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(255) NOT NULL COMMENT '公告标题',
  `content`    TEXT         NOT NULL COMMENT '公告内容',
  `type`       TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '类型:1=系统公告,2=活动,3=更新日志',
  `is_top`     TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否置顶:0=否,1=是',
  `sort`       INT          NOT NULL DEFAULT 0 COMMENT '排序',
  `status`     TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `start_at`   DATETIME     DEFAULT NULL COMMENT '生效开始时间',
  `end_at`     DATETIME     DEFAULT NULL COMMENT '生效结束时间',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_is_top` (`is_top`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='公告';

-- -----------------------------------------------------------
-- 7. toolbar - 工具栏表(快捷入口/导航)
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `toolbar`;
CREATE TABLE `toolbar` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(64)  NOT NULL COMMENT '名称',
  `icon`       VARCHAR(512) NOT NULL DEFAULT '' COMMENT '图标 URL',
  `link_type`  TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '跳转类型:1=软件详情,2=分类,3=外链,4=页面',
  `link_value` VARCHAR(512) NOT NULL DEFAULT '' COMMENT '跳转值',
  `sort`       INT          NOT NULL DEFAULT 0 COMMENT '排序',
  `status`     TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工具栏快捷入口';

-- -----------------------------------------------------------
-- 8. advertisement - 广告表
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `advertisement`;
CREATE TABLE `advertisement` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(128) NOT NULL DEFAULT '' COMMENT '标题',
  `image`      VARCHAR(512) NOT NULL COMMENT '图片 URL',
  `link`       VARCHAR(512) NOT NULL DEFAULT '' COMMENT '跳转链接',
  `position`   VARCHAR(32)  NOT NULL DEFAULT 'home' COMMENT '位置:home=首页,detail=详情页',
  `sort`       INT          NOT NULL DEFAULT 0 COMMENT '排序',
  `status`     TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `start_at`   DATETIME     DEFAULT NULL COMMENT '生效开始时间',
  `end_at`     DATETIME     DEFAULT NULL COMMENT '生效结束时间',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_position_status` (`position`, `status`),
  KEY `idx_sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='广告';

-- -----------------------------------------------------------
-- 9. favorite - 收藏表
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `favorite`;
CREATE TABLE `favorite` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       INT UNSIGNED NOT NULL COMMENT '用户 ID',
  `software_id`   INT UNSIGNED NOT NULL COMMENT '软件 ID',
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_software` (`user_id`, `software_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_software_id` (`software_id`),
  CONSTRAINT `fk_favorite_user`     FOREIGN KEY (`user_id`)     REFERENCES `user`    (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_favorite_software` FOREIGN KEY (`software_id`) REFERENCES `software` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='收藏';

-- -----------------------------------------------------------
-- 10. download_record - 下载记录表
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `download_record`;
CREATE TABLE `download_record` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       INT UNSIGNED NOT NULL COMMENT '用户 ID',
  `software_id`   INT UNSIGNED NOT NULL COMMENT '软件 ID',
  `software_name` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '软件名称(冗余)',
  `version`       VARCHAR(32)  NOT NULL DEFAULT '' COMMENT '下载时版本',
  `ip`            VARCHAR(64)  NOT NULL DEFAULT '' COMMENT '下载 IP',
  `user_agent`    VARCHAR(512) NOT NULL DEFAULT '' COMMENT 'UA',
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_software_id` (`software_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_download_user`     FOREIGN KEY (`user_id`)     REFERENCES `user`    (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_download_software` FOREIGN KEY (`software_id`) REFERENCES `software` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='下载记录';

-- -----------------------------------------------------------
-- 11. feedback - 反馈表
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL COMMENT '用户 ID',
  `type`       TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '类型:1=功能建议,2=Bug 反馈,3=内容纠错,4=其他',
  `title`      VARCHAR(255) NOT NULL COMMENT '标题',
  `content`    TEXT         NOT NULL COMMENT '详细内容',
  `images`     JSON         DEFAULT NULL COMMENT '图片数组 JSON',
  `contact`    VARCHAR(128) NOT NULL DEFAULT '' COMMENT '联系方式',
  `status`     TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态:0=待处理,1=处理中,2=已解决,3=已忽略',
  `reply`      TEXT         COMMENT '管理员回复',
  `replied_by` INT UNSIGNED DEFAULT NULL COMMENT '回复管理员 ID',
  `replied_at` DATETIME     DEFAULT NULL COMMENT '回复时间',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_feedback_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户反馈';

-- -----------------------------------------------------------
-- 12. report_config - 举报配置表
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `report_config`;
CREATE TABLE `report_config` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `target_type` TINYINT UNSIGNED NOT NULL COMMENT '举报对象类型:1=软件,2=评论,3=用户',
  `reason`     VARCHAR(128) NOT NULL COMMENT '举报原因',
  `description` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '详细描述(可选)',
  `sort`       INT          NOT NULL DEFAULT 0 COMMENT '排序',
  `status`     TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_target_type` (`target_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='举报原因配置';

-- -----------------------------------------------------------
-- 13. config - 系统配置表
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_name` VARCHAR(64)  NOT NULL DEFAULT 'default' COMMENT '配置分组',
  `key_name`   VARCHAR(128) NOT NULL COMMENT '配置键',
  `value`      TEXT         COMMENT '配置值',
  `type`       VARCHAR(32)  NOT NULL DEFAULT 'string' COMMENT '值类型:string/number/json/bool',
  `description` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '配置说明',
  `sort`       INT          NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_group_key` (`group_name`, `key_name`),
  KEY `idx_group` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置';

-- -----------------------------------------------------------
-- 14. verification_code - 验证码表
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `verification_code`;
CREATE TABLE `verification_code` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `target`     VARCHAR(128) NOT NULL COMMENT '目标(邮箱/手机号)',
  `scene`      VARCHAR(32)  NOT NULL COMMENT '场景:register/login/reset_password/bind',
  `code`       VARCHAR(16)  NOT NULL COMMENT '验证码',
  `type`       TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '类型:1=邮箱,2=短信',
  `status`     TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态:0=未使用,1=已使用,2=已过期',
  `ip`         VARCHAR(64)  NOT NULL DEFAULT '' COMMENT '请求 IP',
  `expire_at`  DATETIME     NOT NULL COMMENT '过期时间',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_target_scene` (`target`, `scene`),
  KEY `idx_code` (`code`),
  KEY `idx_status` (`status`),
  KEY `idx_expire_at` (`expire_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='验证码';


-- ============================================================
-- 种子数据
-- ============================================================

-- 默认管理员 (admin / admin123)
INSERT INTO `admin` (`username`, `password`, `nickname`, `avatar`, `role`, `status`) VALUES
  ('admin', '$2y$10$CwTycUXWue0Thq9StjUM0uJ8g8JNXu9SCcJwMGNYp3fQ5WcVSZqGq', '超级管理员', '', 1, 1);

-- 默认分类
INSERT INTO `category` (`parent_id`, `name`, `icon`, `image`, `keywords`, `sort`, `status`, `software_count`) VALUES
  (0, '系统工具',   '', '', '系统,工具,优化',     100, 1, 0),
  (0, '办公软件',   '', '', '办公,文档,效率',     90,  1, 0),
  (0, '开发编程',   '', '', '开发,编程,IDE',      80,  1, 0),
  (0, '图形设计',   '', '', '设计,图像,创意',     70,  1, 0),
  (0, '影音娱乐',   '', '', '影音,视频,音乐',     60,  1, 0),
  (0, '安全杀毒',   '', '', '安全,杀毒,防护',     50,  1, 0),
  (0, '网络通讯',   '', '', '网络,通讯,下载',     40,  1, 0),
  (0, '教育学习',   '', '', '教育,学习,知识',     30,  1, 0),
  (0, '游戏娱乐',   '', '', '游戏,娱乐,休闲',     20,  1, 0),
  (0, '实用软件',   '', '', '实用,生活,便捷',     10,  1, 0);

-- 子分类 (系统工具下)
INSERT INTO `category` (`parent_id`, `name`, `icon`, `image`, `keywords`, `sort`, `status`, `software_count`) VALUES
  (1, '系统清理',   '', '', '清理,垃圾,优化',  100, 1, 0),
  (1, '系统备份',   '', '', '备份,还原,恢复',   90, 1, 0),
  (1, '系统设置',   '', '', '设置,配置,调整',   80, 1, 0),
  (1, '硬件检测',   '', '', '硬件,检测,温度',   70, 1, 0);

-- 默认轮播图
INSERT INTO `banner` (`title`, `image`, `link_type`, `link_value`, `position`, `sort`, `status`, `start_at`, `end_at`) VALUES
  ('欢迎使用软件商店', 'https://cdn.example.com/banner/welcome.jpg', 3, 'https://example.com', 'home', 100, 1, NOW(), DATE_ADD(NOW(), INTERVAL 365 DAY)),
  ('热门推荐',         'https://cdn.example.com/banner/hot.jpg',     1, '1',                  'home', 90,  1, NOW(), DATE_ADD(NOW(), INTERVAL 365 DAY)),
  ('最新上架',         'https://cdn.example.com/banner/new.jpg',     3, 'https://example.com', 'home', 80,  1, NOW(), DATE_ADD(NOW(), INTERVAL 365 DAY));

-- 默认工具栏
INSERT INTO `toolbar` (`name`, `icon`, `link_type`, `link_value`, `sort`, `status`) VALUES
  ('软件分类', '', 2, '0', 100, 1),
  ('最新上架', '', 4, 'new', 90, 1),
  ('热门推荐', '', 4, 'hot', 80, 1),
  ('免费专区', '', 4, 'free', 70, 1),
  ('排行榜',   '', 4, 'rank', 60, 1),
  ('我的收藏', '', 4, 'favorite', 50, 1),
  ('下载管理', '', 4, 'download', 40, 1),
  ('帮助反馈', '', 4, 'feedback', 30, 1);

-- 默认广告
INSERT INTO `advertisement` (`title`, `image`, `link`, `position`, `sort`, `status`, `start_at`, `end_at`) VALUES
  ('限时优惠', 'https://cdn.example.com/ad/sale.jpg', 'https://example.com/sale', 'home', 100, 1, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
  ('新品推荐', 'https://cdn.example.com/ad/new.jpg', 'https://example.com/new', 'home', 90,  1, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY));

-- 默认公告
INSERT INTO `notice` (`title`, `content`, `type`, `is_top`, `sort`, `status`, `start_at`, `end_at`) VALUES
  ('欢迎使用软件商店', '感谢您使用软件商店,我们致力于为您提供优质的软件下载体验。', 1, 1, 100, 1, NOW(), DATE_ADD(NOW(), INTERVAL 365 DAY)),
  ('平台规则说明',     '请仔细阅读平台使用规则,文明上网,合法下载软件。',           1, 0, 90,  1, NOW(), DATE_ADD(NOW(), INTERVAL 365 DAY));

-- 默认举报原因配置
INSERT INTO `report_config` (`target_type`, `reason`, `description`, `sort`, `status`) VALUES
  (1, '软件内容违规',   '软件包含违法违规内容',             100, 1),
  (1, '软件有病毒',     '软件被检测出包含病毒或恶意代码',   90,  1),
  (1, '软件无法使用',   '软件下载后无法正常安装或运行',     80,  1),
  (1, '版权侵权',       '软件侵犯了他人的版权',             70,  1),
  (2, '评论内容违规',   '评论包含侮辱、诽谤或违法内容',     60,  1),
  (2, '广告营销',       '评论中包含垃圾广告或营销信息',     50,  1),
  (3, '用户行为违规',   '用户存在违规行为',                 40,  1),
  (3, '诈骗行为',       '用户存在欺诈行为',                 30,  1);

-- 默认系统配置
INSERT INTO `config` (`group_name`, `key_name`, `value`, `type`, `description`, `sort`) VALUES
  ('site',      'site_name',       '软件商店',                                         'string', '站点名称',           100),
  ('site',      'site_logo',       '/assets/images/logo.png',                          'string', '站点 Logo',          90),
  ('site',      'site_icp',        '',                                                  'string', 'ICP 备案号',         80),
  ('site',      'site_copyright',  '© 2024 软件商店 版权所有',                         'string', '版权信息',           70),
  ('site',      'site_description','提供优质的软件下载服务',                            'string', '站点描述',           60),
  ('site',      'site_keywords',   '软件下载,免费软件,绿色软件',                        'string', '站点关键词',         50),
  ('upload',    'max_upload_size', '2048',                                              'number', '单文件上传大小(MB)', 100),
  ('upload',    'allowed_types',   'jpg,jpeg,png,gif,zip,rar,7z,exe,dmg,apk,ipa',      'string', '允许的文件类型',     90),
  ('upload',    'upload_path',     '/uploads',                                          'string', '上传保存路径',       80),
  ('download',  'enable_download', '1',                                                'bool',   '是否启用下载',       100),
  ('download',  'download_limit',  '100',                                              'number', '每用户每日下载上限', 90),
  ('download',  'need_login',      '1',                                                'bool',   '下载是否需要登录',   80),
  ('register',  'enable_register', '1',                                               'bool',   '是否开启注册',       100),
  ('register',  'email_verify',    '1',                                                'bool',   '注册是否需要邮箱验证', 90),
  ('register',  'allow_third_party','1',                                               'bool',   '是否允许第三方登录', 80),
  ('seo',       'seo_title',       '软件商店 - 免费软件下载',                           'string', 'SEO 标题',           100),
  ('seo',       'seo_keywords',    '软件下载,免费软件',                                 'string', 'SEO 关键词',         90),
  ('seo',       'seo_description', '提供各类免费软件下载,安全可靠',                     'string', 'SEO 描述',           80),
  ('email',     'smtp_host',       'smtp.example.com',                                  'string', 'SMTP 主机',          100),
  ('email',     'smtp_port',       '465',                                              'number', 'SMTP 端口',          90),
  ('email',     'smtp_username',   '',                                                  'string', 'SMTP 用户名',        80),
  ('email',     'smtp_password',   '',                                                  'string', 'SMTP 密码',          70),
  ('email',     'smtp_secure',     'ssl',                                               'string', 'SMTP 加密方式',      60),
  ('email',     'from_email',      'noreply@example.com',                               'string', '发件人邮箱',         50),
  ('sms',       'sms_provider',    '',                                                  'string', '短信服务商',         100),
  ('sms',       'sms_app_id',      '',                                                  'string', '短信 AppID',         90),
  ('sms',       'sms_app_secret',  '',                                                  'string', '短信 AppSecret',     80),
  ('sms',       'sms_sign',        '',                                                  'string', '短信签名',           70),
  ('security',  'captcha_enable',  '1',                                                'bool',   '是否启用图形验证码', 100),
  ('security',  'max_login_attempts','5',                                               'number', '最大登录尝试次数',   90),
  ('security',  'lockout_duration', '30',                                               'number', '锁定时长(分钟)',     80);