<?php

/**
 * 公共工具函数库
 *
 * 提供项目中常用的辅助函数。
 */

if (!function_exists('clean')) {
    /**
     * 清理字符串，防止 XSS 和 SQL 注入
     *
     * @param string $input 原始输入
     * @param int    $flags htmlspecialchars flags
     * @return string
     */
    function clean(string $input, int $flags = ENT_QUOTES): string
    {
        $input = trim($input);
        $input = strip_tags($input);
        return htmlspecialchars($input, $flags, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    /**
     * 重定向到指定 URL
     *
     * @param string $url   目标 URL
     * @param int    $code  HTTP 状态码
     */
    function redirect(string $url, int $code = 302): void
    {
        if (!headers_sent()) {
            header("Location: {$url}", true, $code);
            exit;
        }
        echo '<script>window.location.href="' . $url . '";</script>';
        exit;
    }
}

if (!function_exists('isAjax')) {
    /**
     * 判断是否为 AJAX 请求
     *
     * @return bool
     */
    function isAjax(): bool
    {
        $header = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return strtolower($header) === 'xmlhttprequest'
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
}

if (!function_exists('timeAgo')) {
    /**
     * 计算相对时间（中文友好格式）
     *
     * @param string|int $timestamp 时间戳或日期字符串
     * @return string
     */
    function timeAgo($timestamp): string
    {
        if (is_string($timestamp)) {
            $timestamp = strtotime($timestamp);
        }

        $now = time();
        $diff = $now - $timestamp;

        if ($diff < 0) {
            return date('Y-m-d H:i', $timestamp);
        }

        $units = [
            ['秒', 1],
            ['分钟', 60],
            ['小时', 3600],
            ['天', 86400],
            ['周', 604800],
            ['月', 2592000],
            ['年', 31536000],
        ];

        foreach ($units as [$label, $step]) {
            if ($diff < $step * 60 || $label === '年') {
                $value = (int) floor($diff / $step);
                return $value . $label . '前';
            }
        }

        return '刚刚';
    }
}

if (!function_exists('formatSize')) {
    /**
     * 格式化文件大小为人类可读格式
     *
     * @param int $bytes 字节数
     * @param int $precision 小数位数
     * @return string
     */
    function formatSize(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

if (!function_exists('getClientIp')) {
    /**
     * 获取客户端 IP 地址
     *
     * @return string
     */
    function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = trim(explode(',', $_SERVER[$header])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

if (!function_exists('output_file')) {
    /**
     * 输出文件内容（用于下载或预览）
     *
     * @param string $path      文件路径
     * @param string $filename  下载文件名
     * @param bool   $download  是否强制下载
     * @param string $mimeType  MIME 类型（留空自动检测）
     */
    function output_file(string $path, string $filename = '', bool $download = true, string $mimeType = ''): void
    {
        if (!file_exists($path) || !is_file($path)) {
            http_response_code(404);
            echo '文件不存在';
            return;
        }

        if ($mimeType === '') {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($path);
        }

        $basename = $filename ?: basename($path);
        $contentDisposition = $download
            ? 'attachment; filename="' . $basename . '"'
            : 'inline; filename="' . $basename . '"';

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: ' . $contentDisposition);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: public, max-age=0, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');

        readfile($path);
        exit;
    }
}

if (!function_exists('config')) {
    /**
     * 全局配置访问辅助函数
     *
     * @param string $key     配置键，支持点号分隔
     * @param mixed  $default 默认值
     * @return mixed
     */
    function config(string $key, $default = null)
    {
        static $config = null;

        if ($config === null) {
            $configFile = __DIR__ . '/config.php';
            if (file_exists($configFile)) {
                $config = require $configFile;
            } else {
                $config = [];
            }
        }

        $keys = explode('.', $key);
        $value = $config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }
}

if (!function_exists('env')) {
    /**
     * 获取环境变量
     *
     * @param string $key     变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}

if (!function_exists('load_helpers')) {
    /**
     * 自动加载目录下的所有 PHP 文件
     *
     * @param string $directory 目录路径
     */
    function load_helpers(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = glob(rtrim($directory, '/') . '/*.php');
        foreach ($files as $file) {
            require_once $file;
        }
    }
}