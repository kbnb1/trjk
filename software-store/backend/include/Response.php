<?php

namespace App;

/**
 * API 响应辅助类
 *
 * 提供统一的 JSON 响应格式：{code, message, data}
 *
 * @package App
 */
class Response
{
    /** @var int 成功状态码 */
    public const CODE_OK = 200;

    /** @var int 参数错误 */
    public const CODE_BAD_REQUEST = 400;

    /** @var int 未授权 */
    public const CODE_UNAUTHORIZED = 401;

    /** @var int 禁止访问 */
    public const CODE_FORBIDDEN = 403;

    /** @var int 资源不存在 */
    public const CODE_NOT_FOUND = 404;

    /** @var int 服务器内部错误 */
    public const CODE_INTERNAL_ERROR = 500;

    /**
     * 输出 JSON 响应并终止脚本
     *
     * @param mixed  $data    响应数据
     * @param int    $code    状态码
     * @param string $message 消息文本
     */
    public static function json($data, int $code = self::CODE_OK, string $message = ''): void
    {
        http_response_code($code);

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
        }

        $response = [
            'code'    => $code,
            'message' => $message ?: self::codeToMessage($code),
            'data'    => $data,
            'time'    => time(),
        ];

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 成功响应
     *
     * @param mixed  $data    响应数据
     * @param string $message 可选消息
     */
    public static function success($data = null, string $message = '操作成功'): void
    {
        self::json($data, self::CODE_OK, $message);
    }

    /**
     * 错误响应
     *
     * @param string $message 错误消息
     * @param int    $code    错误码
     * @param mixed  $data    附加数据
     */
    public static function error(string $message = '操作失败', int $code = self::CODE_BAD_REQUEST, $data = null): void
    {
        self::json($data, $code, $message);
    }

    /**
     * 未授权响应
     *
     * @param string $message 错误消息
     */
    public static function unauthorized(string $message = '未登录或登录已过期'): void
    {
        self::error($message, self::CODE_UNAUTHORIZED);
    }

    /**
     * 禁止访问响应
     *
     * @param string $message 错误消息
     */
    public static function forbidden(string $message = '没有访问权限'): void
    {
        self::error($message, self::CODE_FORBIDDEN);
    }

    /**
     * 资源不存在响应
     *
     * @param string $message 错误消息
     */
    public static function notFound(string $message = '资源不存在'): void
    {
        self::error($message, self::CODE_NOT_FOUND);
    }

    /**
     * 根据状态码获取默认消息
     *
     * @param int $code 状态码
     * @return string
     */
    private static function codeToMessage(int $code): string
    {
        $messages = [
            self::CODE_OK             => '操作成功',
            self::CODE_BAD_REQUEST    => '请求参数错误',
            self::CODE_UNAUTHORIZED   => '未授权',
            self::CODE_FORBIDDEN      => '禁止访问',
            self::CODE_NOT_FOUND      => '资源不存在',
            self::CODE_INTERNAL_ERROR => '服务器内部错误',
        ];

        return $messages[$code] ?? '未知状态';
    }
}