<?php

namespace App;

$segments = $_ROUTE_SEGMENTS;
$method = $_ROUTE_METHOD;

$subResource = $segments[1] ?? null;

if ($subResource === 'info' && $method === 'GET') {
    $user = requireAuth();

    Response::success([
        'id'         => (int)$user['id'],
        'username'   => $user['username'],
        'nickname'   => $user['nickname'],
        'avatar'     => $user['avatar'],
        'email'      => $user['email'],
        'phone'      => $user['phone'],
        'gender'     => (int)$user['gender'],
        'birthday'   => $user['birthday'],
        'signature'  => $user['signature'],
        'balance'    => $user['balance'],
        'created_at' => $user['created_at'],
    ], '获取成功');
}

if ($subResource === 'update' && $method === 'POST') {
    $user = requireAuth();
    $input = getJsonInput();

    $data = [];

    if (isset($input['nickname'])) {
        $nickname = trim($input['nickname']);
        $validator = new Validator(['nickname' => $nickname]);
        $validator->field('nickname', '昵称')->string(64);
        $errors = $validator->check();
        if (!empty($errors)) {
            Response::error('参数验证失败: ' . implode('; ', array_merge(...array_values($errors))));
        }
        $data['nickname'] = $nickname;
    }

    if (isset($input['email'])) {
        $email = trim($input['email']);
        $validator = new Validator(['email' => $email]);
        $validator->field('email', '邮箱')->email();
        $errors = $validator->check();
        if (!empty($errors)) {
            Response::error('邮箱格式不正确');
        }
        $data['email'] = $email;
    }

    if (isset($input['phone'])) {
        $phone = trim($input['phone']);
        $validator = new Validator(['phone' => $phone]);
        $validator->field('phone', '手机号')->phone();
        $errors = $validator->check();
        if (!empty($errors)) {
            Response::error('手机号格式不正确');
        }
        $data['phone'] = $phone;
    }

    if (isset($input['gender'])) {
        $gender = (int)$input['gender'];
        if (!in_array($gender, [0, 1, 2], true)) {
            Response::error('性别参数无效');
        }
        $data['gender'] = $gender;
    }

    if (isset($input['birthday'])) {
        $birthday = $input['birthday'];
        $data['birthday'] = $birthday;
    }

    if (isset($input['signature'])) {
        $signature = trim($input['signature']);
        $validator = new Validator(['signature' => $signature]);
        $validator->field('signature', '个性签名')->string(512);
        $errors = $validator->check();
        if (!empty($errors)) {
            Response::error('个性签名长度不能超过 512 个字符');
        }
        $data['signature'] = $signature;
    }

    if (empty($data)) {
        Response::error('没有需要更新的字段');
    }

    $data['updated_at'] = date('Y-m-d H:i:s');

    Database::update('user', $data, 'id = ?', [(int)$user['id']]);

    $updatedUser = Database::fetch(
        "SELECT id, username, nickname, avatar, email, phone, gender, birthday, signature, balance, created_at, updated_at
         FROM user WHERE id = ?",
        [(int)$user['id']]
    );

    Response::success($updatedUser, '更新成功');
}

if ($subResource === 'avatar' && $method === 'POST') {
    $user = requireAuth();

    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        Response::error('请选择要上传的头像');
    }

    $result = Uploader::uploadAvatar($_FILES['avatar'], (int)$user['id']);

    if (!$result['success']) {
        Response::error($result['message'] ?? '上传失败');
    }

    $oldAvatar = $user['avatar'] ?? '';
    if (!empty($oldAvatar) && $oldAvatar !== $result['path']) {
        Uploader::delete($oldAvatar);
    }

    Database::update('user', [
        'avatar'      => $result['path'],
        'updated_at'  => date('Y-m-d H:i:s'),
    ], 'id = ?', [(int)$user['id']]);

    Response::success([
        'avatar' => $result['path'],
    ], '头像上传成功');
}

Response::error('请求的用户接口不存在', Response::CODE_NOT_FOUND);
