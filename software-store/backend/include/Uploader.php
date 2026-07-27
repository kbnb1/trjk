<?php

namespace App;

/**
 * 文件上传处理类
 *
 * 提供通用文件上传、图片上传、APK 上传等方法，
 * 自动验证文件类型、大小并生成唯一文件名。
 *
 * @package App
 */
class Uploader
{
    /** @var array 上传配置 */
    private static array $config = [];

    /**
     * 设置上传配置
     *
     * @param array $config 上传配置
     */
    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }

    /**
     * 通用文件上传
     *
     * @param array  $file $_FILES 数组中的单个文件
     * @param string $dir  子目录名
     * @param array  $allowedExtensions 允许的扩展名
     * @param int    $maxSize 最大文件大小（字节）
     * @return array  ['success' => bool, 'path' => string, 'message' => string]
     */
    public static function upload(array $file, string $dir = '', array $allowedExtensions = [], int $maxSize = 0): array
    {
        $cfg = self::$config;

        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'path' => '', 'message' => self::getUploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE)];
        }

        if ($maxSize <= 0) {
            $maxSize = $cfg['max_file_size'] ?? 104857600;
        }

        if ($file['size'] > $maxSize) {
            return ['success' => false, 'path' => '', 'message' => '文件大小超过限制'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!empty($allowedExtensions) && !in_array($extension, $allowedExtensions)) {
            return ['success' => false, 'path' => '', 'message' => '不允许的文件类型'];
        }

        $baseDir = $cfg['image_dir'] ?? __DIR__ . '/../uploads';
        $uploadDir = rtrim($baseDir, '/') . ($dir ? '/' . $dir : '');

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return ['success' => false, 'path' => '', 'message' => '创建上传目录失败'];
            }
        }

        $filename = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'path' => '', 'message' => '文件保存失败'];
        }

        $relativePath = '/uploads' . ($dir ? '/' . $dir : '') . '/' . $filename;

        return ['success' => true, 'path' => $relativePath, 'message' => '上传成功', 'filename' => $filename];
    }

    /**
     * 上传图片
     *
     * @param array $file $_FILES 数组中的图片文件
     * @param string $subDir 子目录
     * @return array
     */
    public static function uploadImage(array $file, string $subDir = ''): array
    {
        $cfg = self::$config;
        $allowed = $cfg['allowed_images'] ?? ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = $cfg['max_image_size'] ?? 10485760;

        return self::upload($file, 'images' . ($subDir ? '/' . $subDir : ''), $allowed, $maxSize);
    }

    /**
     * 上传 APK 安装包
     *
     * @param array $file $_FILES 数组中的 APK 文件
     * @param string $subDir 子目录
     * @return array
     */
    public static function uploadApk(array $file, string $subDir = ''): array
    {
        $cfg = self::$config;
        $allowed = $cfg['allowed_apks'] ?? ['apk'];
        $maxSize = $cfg['max_apk_size'] ?? 104857600;

        return self::upload($file, 'apks' . ($subDir ? '/' . $subDir : ''), $allowed, $maxSize);
    }

    /**
     * 上传头像
     *
     * @param array $file $_FILES 数组中的头像文件
     * @param int    $userId 用户 ID
     * @return array
     */
    public static function uploadAvatar(array $file, int $userId = 0): array
    {
        $cfg = self::$config;
        $allowed = $cfg['allowed_images'] ?? ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = $cfg['max_image_size'] ?? 2097152;

        return self::upload($file, 'avatars' . ($userId ? '/' . $userId : ''), $allowed, $maxSize);
    }

    /**
     * 删除已上传的文件
     *
     * @param string $relativePath 相对路径
     * @return bool
     */
    public static function delete(string $relativePath): bool
    {
        $cfg = self::$config;
        $baseDir = dirname($cfg['image_dir'] ?? (__DIR__ . '/../uploads'));

        $fullPath = $baseDir . $relativePath;

        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    /**
     * 计算文件哈希值（用于去重检查）
     *
     * @param string $filePath 文件路径
     * @return string
     */
    public static function hashFile(string $filePath): string
    {
        return md5_file($filePath);
    }

    /**
     * 获取上传错误信息
     *
     * @param int $errorCode 错误码
     * @return string
     */
    private static function getUploadErrorMessage(int $errorCode): string
    {
        $errors = [
            UPLOAD_ERR_OK           => '上传成功',
            UPLOAD_ERR_INI_SIZE     => '文件超过 php.ini 中 upload_max_filesize 限制',
            UPLOAD_ERR_FORM_SIZE    => '文件超过表单 MAX_FILE_SIZE 限制',
            UPLOAD_ERR_PARTIAL      => '文件只有部分被上传',
            UPLOAD_ERR_NO_FILE      => '没有文件被上传',
            UPLOAD_ERR_NO_TMP_DIR   => '找不到临时文件夹',
            UPLOAD_ERR_CANT_WRITE   => '文件写入失败',
            UPLOAD_ERR_EXTENSION    => 'PHP 扩展停止了文件上传',
        ];

        return $errors[$errorCode] ?? '未知上传错误';
    }
}