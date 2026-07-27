<?php

namespace App;

if ($_ROUTE_METHOD !== 'POST') {
    Response::error('请求方法不允许，仅支持 POST', Response::CODE_BAD_REQUEST);
}

$input = getJsonInput();

$errors = [];
if (empty($input['username'])) {
    $errors['username'] = '用户名不能为空';
}
if (empty($input['password'])) {
    $errors['password'] = '密码不能为空';
}

if (!empty($errors)) {
    Response::error('参数验证失败: ' . implode('; ', $errors));
}

$username = trim($input['username']);
$password = $input['password'];

$validator = new Validator([
    'username' => $username,
    'password' => $password,
]);

$validator->field('username', '用户名')->string(64);
$validator->field('password', '密码')->string(128)->min(6);

$checkErrors = $validator->check();
if (!empty($checkErrors)) {
    Response::error('参数验证失败: ' . implode('; ', array_merge(...array_values($checkErrors))));
}

$user = Database::fetch(
    "SELECT * FROM user WHERE username = ? AND status = 1",
    [$username]
);

if (!$user) {
    Response::error('用户名或密码错误');
}

if (!Auth::verifyPassword($password, $user['password'])) {
    $attemptsKey = 'login_attempts_' . $user['id'];
    $attempts = (int)($_SESSION[$attemptsKey] ?? 0);
    $maxAttempts = (int)getUserConfigValue('security', 'max_login_attempts', 5);

    $_SESSION[$attemptsKey] = $attempts + 1;

    if ($attempts + 1 >= $maxAttempts) {
        Response::error('尝试次数过多，请稍后再试');
    }

    Response::error('用户名或密码错误，剩余尝试 ' . ($maxAttempts - $attempts - 1) . ' 次');
}

unset($_SESSION['login_attempts_' . $user['id']]);

$token = Auth::generateToken((int)$user['id'], 'user');

Database::update(
    'user',
    [
        'last_login_at' => date('Y-m-d H:i:s'),
        'last_login_ip' => getClientIp(),
    ],
    'id = ?',
    [(int)$user['id']]
);

$userData = [
    'id'       => (int)$user['id'],
    'username' => $user['username'],
    'nickname' => $user['nickname'],
    'avatar'   => $user['avatar'],
    'email'    => $user['email'],
    'phone'    => $user['phone'],
];

Response::success([
    'token' => $token,
    'user'  => $userData,
], '登录成功');
