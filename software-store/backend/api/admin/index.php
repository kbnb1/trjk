<?php

namespace App;

require_once __DIR__ . '/common.php';

setCorsHeaders();

$pathInfo = parsePathInfo();
$segments = $pathInfo['segments'];
$method = $pathInfo['method'];

$publicRoutes = [
    'login',
];

if (empty($segments)) {
    Response::json([
        'service' => 'Software Store Admin API',
        'version' => '1.0.0',
        'endpoints' => [
            'auth'       => 'login, logout, info',
            'software'   => 'CRUD + toggle',
            'category'   => 'CRUD + sort',
            'banner'     => 'CRUD + toggle',
            'notice'     => 'CRUD',
            'toolbar'    => 'CRUD + toggle + sort',
            'user'       => 'list, detail, toggle, delete',
            'ad'         => 'CRUD + toggle',
            'config'     => 'get, update',
            'stats'      => 'dashboard, trend',
        ],
    ]);
}

$resource = $segments[0] ?? '';
$resourceId = $segments[1] ?? null;
$action = $segments[2] ?? null;

$handlerMap = [
    'login'    => __DIR__ . '/login.php',
    'software' => __DIR__ . '/software.php',
    'category' => __DIR__ . '/category.php',
    'banner'   => __DIR__ . '/banner.php',
    'notice'   => __DIR__ . '/notice.php',
    'toolbar'  => __DIR__ . '/toolbar.php',
    'user'     => __DIR__ . '/user.php',
    'ad'       => __DIR__ . '/ad.php',
    'config'   => __DIR__ . '/config.php',
    'stats'    => __DIR__ . '/stats.php',
];

if (!isset($handlerMap[$resource])) {
    Response::notFound('未知的 API 资源');
}

$handlerFile = $handlerMap[$resource];
if (!file_exists($handlerFile)) {
    Response::error('处理器文件不存在', Response::CODE_INTERNAL_ERROR);
}

$isPublic = in_array($resource, $publicRoutes, true);
if (!$isPublic) {
    requireAdmin();
}

$GLOBALS['_route'] = [
    'resource' => $resource,
    'id'       => $resourceId,
    'action'   => $action,
    'method'   => $method,
    'segments' => $segments,
];

require $handlerFile;