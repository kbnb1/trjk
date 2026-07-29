<?php
/**
 * 统一 JSON 响应类
 * 统一返回格式：{code, message, data, time}
 */
class Response
{
    /** 成功状态码 */
    const CODE_SUCCESS = 200;
    /** 失败状态码 */
    const CODE_ERROR   = 400;
    /** 未授权 */
    const CODE_UNAUTHORIZED = 401;
    /** 禁止访问 */
    const CODE_FORBIDDEN = 403;
    /** 资源不存在 */
    const CODE_NOT_FOUND = 404;
    /** 服务器错误 */
    const CODE_SERVER_ERROR = 500;

    /**
     * 输出 JSON 并终止脚本
     * @param int        $code    状态码
     * @param string     $message 提示信息
     * @param mixed|null $data    返回数据
     * @param int        $httpStatus HTTP 状态码
     */
    public static function json($code = self::CODE_SUCCESS, $message = 'success', $data = null, $httpStatus = 200)
    {
        http_response_code($httpStatus);
        header('Content-Type: application/json; charset=utf-8');
        // 允许跨域（如需）
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, token');

        $result = [
            'code'    => (int) $code,
            'message' => $message,
            'data'    => $data,
            'time'    => time(),
        ];

        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * 成功响应
     * @param mixed|null $data    返回数据
     * @param string     $message 提示信息
     */
    public static function success($data = null, $message = '操作成功')
    {
        self::json(self::CODE_SUCCESS, $message, $data, 200);
    }

    /**
     * 失败响应
     * @param string $message 提示信息
     * @param mixed|null $data 返回数据
     * @param int $code 错误码
     */
    public static function error($message = '操作失败', $data = null, $code = self::CODE_ERROR)
    {
        self::json($code, $message, $data, 200);
    }

    /**
     * 未授权响应
     * @param string $message 提示信息
     */
    public static function unauthorized($message = '未登录或登录已过期')
    {
        self::json(self::CODE_UNAUTHORIZED, $message, null, 401);
    }

    /**
     * 禁止访问响应
     * @param string $message 提示信息
     */
    public static function forbidden($message = '无权限访问')
    {
        self::json(self::CODE_FORBIDDEN, $message, null, 403);
    }

    /**
     * 资源不存在响应
     * @param string $message 提示信息
     */
    public static function notFound($message = '资源不存在')
    {
        self::json(self::CODE_NOT_FOUND, $message, null, 404);
    }

    /**
     * 服务器错误响应
     * @param string $message 提示信息
     */
    public static function serverError($message = '服务器内部错误')
    {
        self::json(self::CODE_SERVER_ERROR, $message, null, 500);
    }

    /**
     * 分页数据响应
     * @param array $list  当前页数据列表
     * @param int   $total 总记录数
     * @param int   $page  当前页码
     * @param int   $size  每页条数
     */
    public static function paginate($list, $total, $page, $size)
    {
        self::success([
            'list'  => $list,
            'total' => (int) $total,
            'page'  => (int) $page,
            'size'  => (int) $size,
            'pages' => $size > 0 ? (int) ceil($total / $size) : 0,
        ]);
    }

    /**
     * 处理 OPTIONS 预检请求
     */
    public static function handleOptions()
    {
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, token');
            header('HTTP/1.1 204 No Content');
            exit;
        }
    }
}
