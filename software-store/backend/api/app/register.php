<?php

namespace App;

if ($_ROUTE_METHOD !== 'POST') {
    Response::error('请求方法不允许，仅支持 POST', Response::CODE_BAD_REQUEST);
}

$enablePhoneVerify = (bool)getUserConfigValue('register', 'phone_verify', false);
$enableEmailVerify = (bool)getUserConfigValue('register', 'email_verify', false);
$enableRegister = (bool)getUserConfigValue('register', 'enable_register', true);

if (!$enableRegister) {
    Response::error('当前系统未开放注册');
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
$phone = trim($input['phone'] ?? '');
$email = trim($input['email'] ?? '');
$phoneCode = trim($input['phone_code'] ?? '');
$emailCode = trim($input['email_code'] ?? '');

$validator = new Validator([
    'username' => $username,
    'password' => $password,
]);

$validator->field('username', '用户名')->string(64)->min(3);
$validator->field('password', '密码')->string(128)->min(6);

if (!empty($phone)) {
    $validator->field('phone', '手机号')->phone();
}
if (!empty($email)) {
    $validator->field('email', '邮箱')->email();
}

$checkErrors = $validator->check();
if (!empty($checkErrors)) {
    Response::error('参数验证失败: ' . implode('; ', array_merge(...array_values($checkErrors))));
}

$existing = Database::fetch(
    "SELECT id FROM user WHERE username = ?",
    [$username]
);
if ($existing) {
    Response::error('用户名已被占用');
}

if (!empty($phone)) {
    $existingPhone = Database::fetch(
        "SELECT id FROM user WHERE phone = ?",
        [$phone]
    );
    if ($existingPhone) {
        Response::error('该手机号已注册');
    }

    if ($enablePhoneVerify) {
        $codeRecord = Database::fetch(
            "SELECT * FROM verification_code
             WHERE target = ? AND scene = 'register' AND code = ? AND status = 0
             AND expire_at >= NOW()
             ORDER BY id DESC LIMIT 1",
            [$phone, $phoneCode]
        );
        if (!$codeRecord) {
            Response::error('手机验证码无效或已过期');
        }
        Database::update('verification_code', ['status' => 1], 'id = ?', [(int)$codeRecord['id']]);
    }
}

if (!empty($email)) {
    $existingEmail = Database::fetch(
        "SELECT id FROM user WHERE email = ?",
        [$email]
    );
    if ($existingEmail) {
        Response::error('该邮箱已注册');
    }

    if ($enableEmailVerify) {
        $codeRecord = Database::fetch(
            "SELECT * FROM verification_code
             WHERE target = ? AND scene = 'register' AND code = ? AND status = 0
             AND expire_at >= NOW()
             ORDER BY id DESC LIMIT 1",
            [$email, $emailCode]
        );
        if (!$codeRecord) {
            Response::error('邮箱验证码无效或已过期');
        }
        Database::update('verification_code', ['status' => 1], 'id = ?', [(int)$codeRecord['id']]);
    }
}

$hashedPassword = Auth::hashPassword($password);

$userId = Database::insert('user', [
    'username' => $username,
    'password' => $hashedPassword,
    'nickname' => '用户' . substr(md5($username), 0, 6),
    'avatar'   => '',
    'email'    => $email,
    'phone'    => $phone,
    'status'   => 1,
]);

$token = Auth::generateToken($userId, 'user');

Response::success([
    'token' => $token,
    'user'  => [
        'id'       => $userId,
        'username' => $username,
        'nickname' => '用户' . substr(md5($username), 0, 6),
        'avatar'   => '',
        'email'    => $email,
        'phone'    => $phone,
    ],
], '注册成功');
