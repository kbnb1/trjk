<?php
/**
 * 文件上传类
 * 支持图片、APK 等文件上传，自动重命名、目录创建、安全校验
 */
class Uploader
{
    /** @var array 上传错误信息映射 */
    protected static $errorMessages = [
        UPLOAD_ERR_INI_SIZE   => '上传文件超过 php.ini 配置限制',
        UPLOAD_ERR_FORM_SIZE  => '上传文件超过表单限制',
        UPLOAD_ERR_PARTIAL    => '文件仅部分被上传',
        UPLOAD_ERR_NO_FILE    => '没有文件被上传',
        UPLOAD_ERR_NO_TMP_DIR => '缺少临时目录',
        UPLOAD_ERR_CANT_WRITE => '写入磁盘失败',
        UPLOAD_ERR_EXTENSION  => 'PHP扩展阻止了上传',
    ];

    /**
     * 上传文件
     * @param array  $file     $_FILES 中的单元素
     * @param string $category 分类目录（如 image/apk）
     * @param array  $allowExt 允许的扩展名
     * @return array [success, path, url, message]
     */
    public static function upload($file, $category = 'image', $allowExt = null)
    {
        // 基础校验
        if (!isset($file['error']) || is_array($file['error'])) {
            return self::fail('无效的文件参数');
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $msg = isset(self::$errorMessages[$file['error']]) ? self::$errorMessages[$file['error']] : '未知上传错误';
            return self::fail($msg);
        }
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            return self::fail('文件大小超过限制');
        }

        // 默认允许的扩展名
        if ($allowExt === null) {
            $allowExt = IMAGE_ALLOW_EXT;
        }

        // 校验扩展名
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowExt, true)) {
            return self::fail('不允许的文件类型：' . $ext);
        }

        // 校验 MIME（防止伪装）
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $imageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/x-icon'];
        if (in_array($ext, IMAGE_ALLOW_EXT) && !in_array($mime, $imageMimes, true)) {
            return self::fail('图片MIME类型不合法');
        }

        // 构建存储目录：uploads/category/YYYY/MM/
        $dateDir = date('Y/m');
        $relativeDir = '/' . trim($category, '/') . '/' . $dateDir;
        $absDir = UPLOAD_PATH . $relativeDir;
        if (!is_dir($absDir) && !mkdir($absDir, 0755, true)) {
            return self::fail('创建上传目录失败');
        }

        // 生成唯一文件名
        $filename = self::generateFilename($ext);
        $absPath = $absDir . '/' . $filename;
        $relativePath = trim($relativeDir, '/') . '/' . $filename;

        // 移动临时文件
        if (!move_uploaded_file($file['tmp_name'], $absPath)) {
            return self::fail('保存文件失败');
        }

        // 拼接访问URL
        $url = UPLOAD_URL . '/' . $relativePath;

        return [
            'success' => true,
            'path'    => $relativePath,
            'url'     => $url,
            'message' => '上传成功',
            'size'    => $file['size'],
            'ext'     => $ext,
            'mime'    => $mime,
        ];
    }

    /**
     * 上传图片
     * @param array $file $_FILES 中的单元素
     * @return array
     */
    public static function uploadImage($file)
    {
        return self::upload($file, 'image', IMAGE_ALLOW_EXT);
    }

    /**
     * 上传APK
     * @param array $file $_FILES 中的单元素
     * @return array
     */
    public static function uploadApk($file)
    {
        return self::upload($file, 'apk', APK_ALLOW_EXT);
    }

    /**
     * 上传通用文件
     * @param array $file $_FILES 中的单元素
     * @return array
     */
    public static function uploadFile($file)
    {
        return self::upload($file, 'file', FILE_ALLOW_EXT);
    }

    /**
     * 生成唯一文件名
     * @param string $ext 扩展名
     * @return string
     */
    protected static function generateFilename($ext)
    {
        return date('YmdHis') . '_' . substr(md5(uniqid(mt_rand(), true)), 0, 8) . '.' . $ext;
    }

    /**
     * 返回失败结构
     * @param string $message
     * @return array
     */
    protected static function fail($message)
    {
        return [
            'success' => false,
            'path'    => '',
            'url'     => '',
            'message' => $message,
        ];
    }
}
