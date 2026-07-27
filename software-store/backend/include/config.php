<?php

/**
 * 软件商店后端配置文件
 *
 * 注意：以下配置值仅为开发环境占位符，生产环境必须替换为实际值。
 * 切勿将包含真实凭据的配置文件提交到版本控制系统。
 */

/* ============================================================
 * 数据库配置
 * ============================================================ */
return [

    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'database' => 'software_store',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'   => '',
    ],

    /* ============================================================
     * 站点配置
     * ============================================================ */
    'site' => [
        'name'        => '软件商店',
        'url'         => 'http://localhost/software-store',
        'timezone'    => 'Asia/Shanghai',
        'language'    => 'zh-CN',
        'copyright'   => '© 2026 软件商店. All rights reserved.',
        'icp'         => '',
        'debug'       => true,
    ],

    /* ============================================================
     * API 配置
     * ============================================================ */
    'api' => [
        'base_url'    => '/api',
        'version'     => 'v1',
        'rate_limit'  => 60,
        'rate_window' => 60,
        'cors_origins' => [
            'http://localhost',
            'http://127.0.0.1',
        ],
        'cors_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'cors_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
    ],

    /* ============================================================
     * 认证配置
     * ============================================================ */
    'auth' => [
        'secret_key'     => 'CHANGE_THIS_TO_A_SECURE_RANDOM_STRING_2026',
        'algorithm'      => 'HS256',
        'user_ttl'       => 7200,
        'admin_ttl'      => 3600,
        'refresh_ttl'    => 604800,
        'token_header'   => 'Authorization',
        'token_prefix'   => 'Bearer',
    ],

    /* ============================================================
     * 上传配置
     * ============================================================ */
    'upload' => [
        'max_file_size'   => 104857600,
        'max_image_size'  => 10485760,
        'max_apk_size'    => 104857600,
        'allowed_images'   => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'],
        'allowed_apks'     => ['apk'],
        'allowed_docs'     => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
        'image_dir'       => __DIR__ . '/../uploads/images',
        'apk_dir'         => __DIR__ . '/../uploads/apks',
        'temp_dir'        => __DIR__ . '/../uploads/temp',
        'avatar_dir'      => __DIR__ . '/../uploads/avatars',
    ],

    /* ============================================================
     * 缓存配置
     * ============================================================ */
    'cache' => [
        'driver'     => 'file',
        'ttl'        => 3600,
        'path'       => __DIR__ . '/../storage/cache',
    ],

    /* ============================================================
     * 日志配置
     * ============================================================ */
    'log' => [
        'path'       => __DIR__ . '/../storage/logs',
        'level'      => 'debug',
        'max_files'  => 30,
    ],
];