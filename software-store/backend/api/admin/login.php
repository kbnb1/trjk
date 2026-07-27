<?php

namespace App;

$route = $GLOBALS['_route'];
$method = $route['method'];
$action = $route['action'];

if ($method === 'POST' && $action === null) {
    $input = parseJsonInput();

    $validator = new Validator($input);
    $errors = $validator
        ->field('username', '用户名')->required()->string(64)
        ->field('password', '密码')->required()->string(128)->min(6)
        ->check();

    if (!empty($errors)) {
        Response::error('参数验证失败: ' . implode('; ', array_merge(...array_values($errors))));
    }

    $username = $input['username'];
    $password = $input['password'];

    $admin = Database::fetch(
        "SELECT * FROM admin WHERE username = ?",
        [$username]
    );

    if (!$admin) {
        Response::error('用户名或密码错误', Response::CODE_UNAUTHORIZED);
    }

    if ((int)$admin['status'] !== 1) {
        Response::forbidden('账号已被禁用');
    }

    if (!Auth::verifyPassword($password, $admin['password'])) {
        Response::error('用户名或密码错误', Response::CODE_UNAUTHORIZED);
    }

    $token = Auth::generateToken((int)$admin['id'], 'admin', [
        'username' => $admin['username'],
        'role'     => $admin['role'],
    ]);

    Database::update('admin', [
        'last_login_at' => date('Y-m-d H:i:s'),
        'last_login_ip' => getClientIp(),
    ], 'id = ?', [(int)$admin['id']]);

    Response::success([
        'token' => $token,
        'admin' => [
            'id'       => (int)$admin['id'],
            'username' => $admin['username'],
            'nickname' => $admin['nickname'],
            'avatar'   => $admin['avatar'],
            'role'     => (int)$admin['role'],
        ],
    ], '登录成功');
}

if ($method === 'POST' && $action === 'logout') {
    Response::success(null, '已退出登录');
}

if ($method === 'GET' && $action === 'info') {
    $admin = requireAdmin();

    Response::success([
        'id'       => (int)$admin['id'],
        'username' => $admin['username'],
        'nickname' => $admin['nickname'],
        'avatar'   => $admin['avatar'],
        'role'     => (int)$admin['role'],
        'status'   => (int)$admin['status'],
    ]);
}

Response::error('请求方式不允许', Response::CODE_BAD_REQUEST);