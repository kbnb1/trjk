<?php

namespace App;

/**
 * 认证类
 *
 * 提供密码哈希、JWT 风格令牌生成与验证、用户身份获取等功能。
 *
 * @package App
 */
class Auth
{
    /** @var array 认证配置 */
    private static array $config = [];

    /**
     * 设置认证配置
     *
     * @param array $config 认证配置
     */
    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }

    /**
     * 哈希密码
     *
     * @param string $password 原始密码
     * @return string 哈希后的密码
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * 验证密码
     *
     * @param string $password 原始密码
     * @param string $hash     存储的哈希值
     * @return bool
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * 生成令牌
     *
     * @param int    $userId 用户 ID
     * @param string $type   令牌类型 (user/admin)
     * @param array  $extra  附加数据
     * @return string
     */
    public static function generateToken(int $userId, string $type = 'user', array $extra = []): string
    {
        $cfg = self::$config;
        $header = [
            'alg' => $cfg['algorithm'] ?? 'HS256',
            'typ' => 'JWT',
        ];

        $ttl = $type === 'admin' ? ($cfg['admin_ttl'] ?? 3600) : ($cfg['user_ttl'] ?? 7200);

        $payload = array_merge($extra, [
            'sub'  => $userId,
            'type' => $type,
            'iat'  => time(),
            'exp'  => time() + $ttl,
            'jti'  => bin2hex(random_bytes(16)),
        ]);

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "{$headerEncoded}.{$payloadEncoded}", $cfg['secret_key'], true);
        $signatureEncoded = self::base64UrlEncode($signature);

        return "{$headerEncoded}.{$payloadEncoded}.{$signatureEncoded}";
    }

    /**
     * 验证令牌
     *
     * @param string $token JWT 令牌
     * @return array|false 载荷数据或 false
     */
    public static function verifyToken(string $token)
    {
        $cfg = self::$config;

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        $expectedSignature = hash_hmac('sha256', "{$headerEncoded}.{$payloadEncoded}", $cfg['secret_key'], true);
        $actualSignature = self::base64UrlDecode($signatureEncoded);

        if (!hash_equals($expectedSignature, $actualSignature)) {
            return false;
        }

        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        if (!$payload) {
            return false;
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    /**
     * 从请求头获取令牌
     *
     * @return string|null
     */
    public static function getTokenFromHeader(): ?string
    {
        $cfg = self::$config;
        $headerName = $cfg['token_header'] ?? 'Authorization';
        $prefix = $cfg['token_prefix'] ?? 'Bearer';

        $header = $_SERVER["HTTP_{$headerName}"] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (empty($header)) {
            return null;
        }

        if (stripos($header, $prefix) === 0) {
            return trim(substr($header, strlen($prefix)));
        }

        return $header;
    }

    /**
     * 获取当前用户
     *
     * @return array|null 用户数据
     */
    public static function getCurrentUser(): ?array
    {
        $token = self::getTokenFromHeader();
        if (!$token) {
            return null;
        }

        $payload = self::verifyToken($token);
        if (!$payload || ($payload['type'] ?? '') !== 'user') {
            return null;
        }

        $userId = $payload['sub'] ?? 0;

        return [
            'id'   => $userId,
            'type' => 'user',
            'data' => $payload,
        ];
    }

    /**
     * 获取当前管理员
     *
     * @return array|null 管理员数据
     */
    public static function getCurrentAdmin(): ?array
    {
        $token = self::getTokenFromHeader();
        if (!$token) {
            return null;
        }

        $payload = self::verifyToken($token);
        if (!$payload || ($payload['type'] ?? '') !== 'admin') {
            return null;
        }

        $adminId = $payload['sub'] ?? 0;

        return [
            'id'   => $adminId,
            'type' => 'admin',
            'data' => $payload,
        ];
    }

    /**
     * Base64 URL 编码
     *
     * @param string $data 原始数据
     * @return string
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL 解码
     *
     * @param string $data 编码数据
     * @return string
     */
    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}