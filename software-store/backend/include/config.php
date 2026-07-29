<?php
/**
 * 系统核心配置文件
 * 包含数据库连接、站点设置、API设置、上传配置、验证开关等
 */

// 错误报告设置（生产环境建议关闭 display_errors）
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 时区设置
date_default_timezone_set('Asia/Shanghai');

// 字符编码
header('Content-Type: text/html; charset=utf-8');

// ------------------------------------------------------------
// 数据库连接配置
// ------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'software_store');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);

// ------------------------------------------------------------
// 站点设置
// ------------------------------------------------------------
define('SITE_NAME', '软件库');
define('SITE_DESCRIPTION', '海量优质应用，安全下载');
define('SITE_URL', 'http://localhost/software-store/backend');
define('SITE_LOGO', '');

// ------------------------------------------------------------
// API 设置
// ------------------------------------------------------------
define('API_PREFIX', '/api');                    // API 路由前缀
define('TOKEN_EXPIRE', 7 * 24 * 3600);           // Token 有效期（秒）：7天
define('TOKEN_SECRET', 'software_store_2024_secret_key'); // Token 加密密钥
define('API_DEBUG', true);                       // 是否开启调试模式

// ------------------------------------------------------------
// 上传配置
// ------------------------------------------------------------
define('UPLOAD_PATH', __DIR__ . '/../uploads');   // 上传根目录（绝对路径）
define('UPLOAD_URL', '/uploads');                 // 上传访问URL前缀
define('UPLOAD_MAX_SIZE', 100 * 1024 * 1024);     // 最大上传大小（字节）：100MB
define('IMAGE_ALLOW_EXT', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'ico', 'svg']); // 允许的图片扩展名
define('APK_ALLOW_EXT', ['apk']);                 // 允许的APK扩展名
define('FILE_ALLOW_EXT', ['apk', 'zip', 'rar', 'ipa']); // 允许的文件扩展名

// ------------------------------------------------------------
// 验证开关
// ------------------------------------------------------------
define('PHONE_VERIFY', true);    // 是否开启手机验证：true 是 false 否
define('EMAIL_VERIFY', false);   // 是否开启邮箱验证：true 是 false 否

// ------------------------------------------------------------
// 验证码配置
// ------------------------------------------------------------
define('CODE_LENGTH', 6);                    // 验证码长度
define('CODE_EXPIRE', 5 * 60);              // 验证码有效期（秒）：5分钟
define('CODE_SEND_INTERVAL', 60);           // 发送间隔（秒）：60秒

// ------------------------------------------------------------
// 分页配置
// ------------------------------------------------------------
define('PAGE_SIZE', 20);          // 默认每页条数
define('PAGE_SIZE_MAX', 100);     // 每页最大条数

// ------------------------------------------------------------
// 系统版本
// ------------------------------------------------------------
define('SYSTEM_VERSION', '2.0.0');

// ------------------------------------------------------------
// 自动加载类文件
// ------------------------------------------------------------
spl_autoload_register(function ($className) {
    $file = __DIR__ . '/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
