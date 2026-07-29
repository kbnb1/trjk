<?php
/**
 * 认证类
 * 提供密码哈希/校验、Token 生成/校验功能
 * Token 采用 base64(json) 形式，包含用户ID、过期时间与签名
 */
class Auth
{
    /**
     * 密码哈希加密
     * @param string $password 明文密码
     * @return string 哈希后的密码
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * 验证密码
     * @param string $password 明文密码
     * @param string $hash     哈希密码
     * @return bool
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * 生成 Token
     * @param int    $userId   用户ID
     * @param string $type     类型（admin/user）
     * @param int    $expire   有效期（秒）
     * @return string
     */
    public static function generateToken($userId, $type = 'user', $expire = null)
    {
        if ($expire === null) {
            $expire = TOKEN_EXPIRE;
        }
        $payload = [
            'user_id' => (int) $userId,
            'type'    => $type,
            'iat'     => time(),          // 签发时间
            'exp'     => time() + $expire, // 过期时间
        ];
        $payloadBase64 = base64_encode(json_encode($payload));
        $signature = self::signature($payloadBase64);
        return $payloadBase64 . '.' . $signature;
    }

    /**
     * 校验 Token
     * @param string $token Token 字符串
     * @return array|false  返回 payload 或 false
     */
    public static function verifyToken($token)
    {
        if (empty($token) || strpos($token, '.') === false) {
            return false;
        }
        list($payloadBase64, $signature) = explode('.', $token, 2);

        // 验签
        if (!hash_equals(self::signature($payloadBase64), $signature)) {
            return false;
        }

        $payload = json_decode(base64_decode($payloadBase64), true);
        if (!is_array($payload)) {
            return false;
        }

        // 校验过期时间
        if (!isset($payload['exp']) || $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    /**
     * 生成签名
     * @param string $payloadBase64
     * @return string
     */
    protected static function signature($payloadBase64)
    {
        return hash_hmac('sha256', $payloadBase64, TOKEN_SECRET);
    }

    /**
     * 从请求头获取 Token
     * 支持 Authorization: Bearer xxx / token 头 / query 参数 token
     * @return string
     */
    public static function getTokenFromRequest()
    {
        // 1. Authorization 头
        $headers = self::getallheaders();
        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.+)/i', $auth, $matches)) {
                return trim($matches[1]);
            }
            return trim($auth);
        }
        // 2. token 头
        if (isset($headers['token'])) {
            return trim($headers['token']);
        }
        // 3. query 或 body 参数
        if (isset($_GET['token'])) {
            return trim($_GET['token']);
        }
        if (isset($_POST['token'])) {
            return trim($_POST['token']);
        }
        return '';
    }

    /**
     * 兼容获取所有请求头（Nginx/Apache）
     * @return array
     */
    public static function getallheaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }

    /**
     * 校验管理员 Token，失败直接返回未授权
     * @return array payload
     */
    public static function requireAdmin()
    {
        $token = self::getTokenFromRequest();
        $payload = self::verifyToken($token);
        if (!$payload || !isset($payload['type']) || $payload['type'] !== 'admin') {
            Response::unauthorized('管理员登录已过期，请重新登录');
        }
        return $payload;
    }

    /**
     * 校验用户 Token，失败直接返回未授权
     * @return array payload
     */
    public static function requireUser()
    {
        $token = self::getTokenFromRequest();
        $payload = self::verifyToken($token);
        if (!$payload || !isset($payload['type']) || $payload['type'] !== 'user') {
            Response::unauthorized('用户登录已过期，请重新登录');
        }
        return $payload;
    }

    /**
     * 获取当前登录用户ID（不强制校验）
     * @return int|null
     */
    public static function getUserId()
    {
        $token = self::getTokenFromRequest();
        $payload = self::verifyToken($token);
        if ($payload && isset($payload['user_id']) && $payload['type'] === 'user') {
            return (int) $payload['user_id'];
        }
        return null;
    }

    /**
     * 获取当前登录管理员ID（不强制校验）
     * @return int|null
     */
    public static function getAdminId()
    {
        $token = self::getTokenFromRequest();
        $payload = self::verifyToken($token);
        if ($payload && isset($payload['user_id']) && $payload['type'] === 'admin') {
            return (int) $payload['user_id'];
        }
        return null;
    }
}
