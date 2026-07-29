<?php
/**
 * 公共工具函数
 */

/**
 * 获取 GET 参数
 * @param string $key     参数名
 * @param mixed  $default 默认值
 * @return mixed
 */
function get_param($key, $default = null)
{
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

/**
 * 获取 POST 参数
 * @param string $key     参数名
 * @param mixed  $default 默认值
 * @return mixed
 */
function post_param($key, $default = null)
{
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

/**
 * 获取请求参数（POST 优先，其次 GET）
 * @param string $key     参数名
 * @param mixed  $default 默认值
 * @return mixed
 */
function input($key, $default = null)
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }
    if (isset($_GET[$key])) {
        return $_GET[$key];
    }
    return $default;
}

/**
 * 获取 JSON 请求体参数
 * @return array
 */
function json_input()
{
    static $data = null;
    if ($data === null) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $data = [];
        }
    }
    return $data;
}

/**
 * 从 JSON 请求体或 POST/GET 获取参数
 * @param string $key
 * @param mixed  $default
 * @return mixed
 */
function param($key, $default = null)
{
    $json = json_input();
    if (isset($json[$key])) {
        return $json[$key];
    }
    return input($key, $default);
}

/**
 * 获取所有请求参数（合并 JSON/POST/GET）
 * @return array
 */
function all_params()
{
    return array_merge($_GET, $_POST, json_input());
}

/**
 * 获取分页参数
 * @return array [page, size, offset]
 */
function pagination()
{
    $page = max(1, (int) param('page', 1));
    $size = (int) param('size', PAGE_SIZE);
    $size = min(max(1, $size), PAGE_SIZE_MAX);
    $offset = ($page - 1) * $size;
    return [$page, $size, $offset];
}

/**
 * 字符串安全过滤（去除首尾空格、去标签）
 * @param string|null $value
 * @return string
 */
function clean($value)
{
    if ($value === null) {
        return '';
    }
    return trim(strip_tags((string) $value));
}

/**
 * 转义 HTML 输出
 * @param string $value
 * @return string
 */
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * 格式化日期时间
 * @param string|int $datetime 时间
 * @param string     $format   格式
 * @return string
 */
function format_date($datetime, $format = 'Y-m-d H:i:s')
{
    if (empty($datetime)) {
        return '';
    }
    $ts = is_numeric($datetime) ? (int) $datetime : strtotime($datetime);
    return $ts ? date($format, $ts) : '';
}

/**
 * 友好的时间显示（xx前）
 * @param string|int $datetime
 * @return string
 */
function time_ago($datetime)
{
    if (empty($datetime)) {
        return '';
    }
    $ts = is_numeric($datetime) ? (int) $datetime : strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60) {
        return $diff . '秒前';
    }
    if ($diff < 3600) {
        return floor($diff / 60) . '分钟前';
    }
    if ($diff < 86400) {
        return floor($diff / 3600) . '小时前';
    }
    if ($diff < 2592000) {
        return floor($diff / 86400) . '天前';
    }
    return date('Y-m-d', $ts);
}

/**
 * 格式化数字（万）
 * @param int $num
 * @return string
 */
function format_number($num)
{
    $num = (int) $num;
    if ($num >= 100000000) {
        return round($num / 100000000, 1) . '亿';
    }
    if ($num >= 10000) {
        return round($num / 10000, 1) . '万';
    }
    return (string) $num;
}

/**
 * 生成随机字符串
 * @param int $length 长度
 * @return string
 */
function random_str($length = 6)
{
    $chars = '0123456789';
    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $str;
}

/**
 * 生成随机验证码
 * @return string
 */
function generate_code()
{
    return random_str(CODE_LENGTH);
}

/**
 * 邮箱验证
 * @param string $email
 * @return bool
 */
function is_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * 手机号验证
 * @param string $phone
 * @return bool
 */
function is_phone($phone)
{
    return (bool) preg_match('/^1[3-9]\d{9}$/', $phone);
}

/**
 * 数组转 JSON（保持中文）
 * @param mixed $data
 * @return string
 */
function to_json($data)
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * JSON 解码（兼容字符串或已是数组）
 * @param string|null $json
 * @param mixed $default
 * @return mixed
 */
function from_json($json, $default = [])
{
    if (empty($json)) {
        return $default;
    }
    if (is_array($json)) {
        return $json;
    }
    $data = json_decode($json, true);
    return $data === null ? $default : $data;
}

/**
 * 获取客户端 IP
 * @return string
 */
function client_ip()
{
    $ips = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($ips as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

/**
 * 获取系统配置值（带缓存）
 * @param string $key     配置键
 * @param mixed  $default 默认值
 * @return mixed
 */
function get_config($key, $default = null)
{
    static $cache = null;
    if ($cache === null) {
        try {
            $db = Database::getInstance();
            $rows = $db->fetchAll('SELECT config_key, config_value FROM config');
            $cache = [];
            foreach ($rows as $row) {
                $cache[$row['config_key']] = $row['config_value'];
            }
        } catch (Exception $e) {
            $cache = [];
        }
    }
    return isset($cache[$key]) ? $cache[$key] : $default;
}

/**
 * 设置系统配置值
 * @param string $key   配置键
 * @param string $value 配置值
 * @return bool
 */
function set_config($key, $value)
{
    $db = Database::getInstance();
    $exists = $db->fetch('SELECT id FROM config WHERE config_key = ?', [$key]);
    if ($exists) {
        $db->update('config', ['config_value' => $value, 'description' => ''], 'config_key = ?', [$key]);
    } else {
        $db->insert('config', [
            'config_key'   => $key,
            'config_value' => $value,
            'description'  => '',
        ]);
    }
    return true;
}

/**
 * 获取完整 URL（拼接站点域名）
 * @param string $path 相对路径
 * @return string
 */
function asset_url($path)
{
    if (empty($path)) {
        return '';
    }
    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }
    return $path;
}

/**
 * 记录接口访问日志（简单版）
 * @param string $action 操作名
 * @param array  $params 参数
 */
function api_log($action, $params = [])
{
    if (!API_DEBUG) {
        return;
    }
    $log = sprintf('[%s] %s %s params=%s', date('Y-m-d H:i:s'), client_ip(), $action, to_json($params));
    @file_put_contents(__DIR__ . '/../runtime/api.log', $log . PHP_EOL, FILE_APPEND);
}

/**
 * 生成密码重置 token
 * @param int $userId
 * @return string
 */
function generate_reset_token($userId)
{
    return md5($userId . time() . mt_rand());
}

/**
 * 兼容低版本 PHP 的 array_column
 */
if (!function_exists('array_column')) {
    function array_column(array $input, $columnKey, $indexKey = null)
    {
        $result = [];
        foreach ($input as $row) {
            if ($indexKey !== null && isset($row[$indexKey])) {
                $result[$row[$indexKey]] = $columnKey === null ? $row : $row[$columnKey];
            } else {
                $result[] = $columnKey === null ? $row : $row[$columnKey];
            }
        }
        return $result;
    }
}
