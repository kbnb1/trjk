<?php

namespace App;

require_once __DIR__ . '/../../include/Database.php';
require_once __DIR__ . '/../../include/Response.php';
require_once __DIR__ . '/../../include/Validator.php';
require_once __DIR__ . '/../../include/Auth.php';
require_once __DIR__ . '/../../include/Uploader.php';
require_once __DIR__ . '/../../include/functions.php';

$config = require __DIR__ . '/../../include/config.php';

Database::setConfig($config['db']);
Auth::setConfig($config['auth']);
Uploader::setConfig($config['upload']);

function getAdminFromToken(): ?array
{
    $token = Auth::getTokenFromHeader();
    if (!$token) {
        return null;
    }

    $payload = Auth::verifyToken($token);
    if (!$payload || ($payload['type'] ?? '') !== 'admin') {
        return null;
    }

    $adminId = $payload['sub'] ?? 0;
    $admin = Database::fetch(
        "SELECT id, username, nickname, avatar, role, status FROM admin WHERE id = ? AND status = 1",
        [$adminId]
    );

    return $admin ?: null;
}

function requireAdmin(): array
{
    $admin = getAdminFromToken();
    if (!$admin) {
        Response::unauthorized('登录已过期,请重新登录');
    }
    return $admin;
}

function getPagination(): array
{
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = max(1, (int)($_GET['per_page'] ?? 15));
    $perPage = min($perPage, 100);

    return [
        'page'     => $page,
        'per_page' => $perPage,
        'offset'   => ($page - 1) * $perPage,
    ];
}

function formatDate($timestamp): string
{
    if (!$timestamp) {
        return '';
    }
    if (is_numeric($timestamp)) {
        return date('Y-m-d H:i:s', $timestamp);
    }
    return date('Y-m-d H:i:s', strtotime($timestamp));
}

function parseJsonInput(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return $_POST;
    }
    $data = json_decode($raw, true);
    if (is_array($data)) {
        return $data;
    }
    return $_POST;
}

function parsePathInfo(): array
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $basePath = '/api/admin';

    $path = parse_url($uri, PHP_URL_PATH) ?: '/';
    $path = preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $path);
    $path = trim($path, '/');

    $segments = $path === '' ? [] : explode('/', $path);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    return [
        'segments' => $segments,
        'method'   => $method,
        'query'    => $_GET,
    ];
}

function setCorsHeaders(): void
{
    $config = require __DIR__ . '/../../include/config.php';
    $api = $config['api'] ?? [];

    $origins = $api['cors_origins'] ?? ['*'];
    $methods = $api['cors_methods'] ?? ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
    $headers = $api['cors_headers'] ?? ['Content-Type', 'Authorization'];

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, $origins, true) || in_array('*', $origins, true)) {
        header("Access-Control-Allow-Origin: {$origin}");
    }

    header('Access-Control-Allow-Methods: ' . implode(', ', $methods));
    header('Access-Control-Allow-Headers: ' . implode(', ', $headers));
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}