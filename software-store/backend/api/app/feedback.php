<?php

namespace App;

if ($_ROUTE_METHOD !== 'POST') {
    Response::error('请求方法不允许，仅支持 POST', Response::CODE_BAD_REQUEST);
}

$user = getUserFromToken();

$input = getJsonInput();

$content = trim($input['content'] ?? '');
$contact = trim($input['contact'] ?? '');
$images = $input['images'] ?? [];

$errors = [];
if (empty($content)) {
    $errors['content'] = '反馈内容不能为空';
}

if (!empty($errors)) {
    Response::error('参数验证失败: ' . implode('; ', $errors));
}

$type = (int)($input['type'] ?? 1);
if ($type < 1 || $type > 4) {
    $type = 1;
}

$title = mb_substr($content, 0, 50);

$validator = new Validator([
    'content' => $content,
    'contact' => $contact,
]);

$validator->field('content', '反馈内容')->string(5000);
if (!empty($contact)) {
    $validator->field('contact', '联系方式')->string(128);
}

$checkErrors = $validator->check();
if (!empty($checkErrors)) {
    Response::error('参数验证失败: ' . implode('; ', array_merge(...array_values($checkErrors))));
}

if (!empty($images) && is_array($images)) {
    $images = array_slice($images, 0, 9);
} else {
    $images = [];
}

$recordId = Database::insert('feedback', [
    'user_id'   => $user ? (int)$user['id'] : 0,
    'type'      => $type,
    'title'     => $title,
    'content'   => $content,
    'images'    => json_encode($images, JSON_UNESCAPED_UNICODE),
    'contact'   => $contact,
    'status'    => 0,
]);

Response::success([
    'id' => $recordId,
], '反馈提交成功，感谢您的建议！');
