<?php

namespace App;

require_once __DIR__ . '/common.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$config = require __DIR__ . '/../../include/config.php';

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH);

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = dirname($scriptName);
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
    $basePath = '';
}

$routePath = str_replace($basePath . '/index.php', '', $path);
$routePath = str_replace($basePath, '', $routePath);
$routePath = '/' . ltrim($routePath, '/');

$segments = array_values(array_filter(explode('/', $routePath)));

$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'home'        => 'home.php',
    'software'    => 'software.php',
    'category'    => 'category.php',
    'toolbar'     => 'toolbar.php',
    'pages'       => 'pages.php',
    'splash'      => 'splash.php',
    'config'      => 'config.php',
    'search'      => 'search.php',
    'login'       => 'login.php',
    'register'    => 'register.php',
    'user'        => 'user.php',
    'favorite'    => 'favorite.php',
    'favorites'   => 'favorite.php',
    'download'    => 'download.php',
    'downloads'   => 'download.php',
    'feedback'    => 'feedback.php',
];

if (empty($segments)) {
    Response::error('API 路由不存在', Response::CODE_NOT_FOUND);
}

$primaryResource = $segments[0];

if (!isset($routes[$primaryResource])) {
    Response::error('API 路由不存在: ' . $primaryResource, Response::CODE_NOT_FOUND);
}

$handlerFile = __DIR__ . '/' . $routes[$primaryResource];

if (!file_exists($handlerFile)) {
    Response::error('处理器文件不存在: ' . $routes[$primaryResource], Response::CODE_INTERNAL_ERROR);
}

$_ROUTE_SEGMENTS = $segments;
$_ROUTE_METHOD = $method;

try {
    require $handlerFile;
} catch (\Throwable $e) {
    if ($config['site']['debug'] ?? false) {
        Response::error(
            '服务器错误: ' . $e->getMessage(),
            Response::CODE_INTERNAL_ERROR
        );
    }
    Response::error('服务器内部错误', Response::CODE_INTERNAL_ERROR);
}
