<?php

namespace App;

$route = $GLOBALS['_route'];
$method = $route['method'];
$id = $route['id'] ? (int)$route['id'] : null;
$action = $route['action'];

if ($method === 'GET' && $id === null) {
    $position = trim($_GET['position'] ?? '');
    $status = ($_GET['status'] ?? '') !== '' ? (int)$_GET['status'] : null;

    $where = ['1=1'];
    $params = [];

    if ($position !== '') {
        $where[] = 'position = ?';
        $params[] = $position;
    }
    if ($status !== null) {
        $where[] = 'status = ?';
        $params[] = $status;
    }

    $list = Database::fetchAll(
        "SELECT * FROM advertisement WHERE " . implode(' AND ', $where) . " ORDER BY sort DESC, id DESC",
        $params
    );

    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['sort'] = (int)$item['sort'];
        $item['status'] = (int)$item['status'];
        $item['start_at'] = formatDate($item['start_at']);
        $item['end_at'] = formatDate($item['end_at']);
        $item['created_at'] = formatDate($item['created_at']);
    }
    unset($item);

    Response::success($list);
}

if ($method === 'GET' && $id !== null) {
    $ad = Database::fetch("SELECT * FROM advertisement WHERE id = ?", [$id]);
    if (!$ad) {
        Response::notFound('广告不存在');
    }

    $ad['id'] = (int)$ad['id'];
    $ad['sort'] = (int)$ad['sort'];
    $ad['status'] = (int)$ad['status'];
    $ad['start_at'] = formatDate($ad['start_at']);
    $ad['end_at'] = formatDate($ad['end_at']);
    $ad['created_at'] = formatDate($ad['created_at']);

    Response::success($ad);
}

if ($method === 'POST' && $id === null) {
    $input = parseJsonInput();

    $validator = new Validator($input);
    $errors = $validator
        ->field('title', '标题')->string(128)
        ->field('link', '链接')->string(512)
        ->field('position', '位置')->string(32)
        ->field('sort', '排序')->integer()
        ->field('status', '状态')->in(['0', '1'])
        ->check();

    if (!empty($errors)) {
        Response::error('参数验证失败: ' . implode('; ', array_merge(...array_values($errors))));
    }

    $data = [
        'title'    => $input['title'] ?? '',
        'image'    => $input['image'] ?? '',
        'link'     => $input['link'] ?? '',
        'position' => $input['position'] ?? 'home',
        'sort'     => (int)($input['sort'] ?? 0),
        'status'   => (int)($input['status'] ?? 1),
        'start_at' => $input['start_at'] ?? date('Y-m-d H:i:s'),
        'end_at'   => $input['end_at'] ?? date('Y-m-d H:i:s', strtotime('+30 days')),
    ];

    if (isset($input['image_file']) && is_array($input['image_file'])) {
        $imgResult = Uploader::uploadImage($input['image_file'], 'ads');
        if (!$imgResult['success']) {
            Response::error('图片上传失败: ' . $imgResult['message']);
        }
        $data['image'] = $imgResult['path'];
    }

    if (empty($data['image'])) {
        Response::error('请提供广告图片');
    }

    $newId = Database::insert('advertisement', $data);

    Response::success(['id' => $newId], '创建成功');
}

if ($method === 'PUT' && $id !== null) {
    $ad = Database::fetch("SELECT id, image FROM advertisement WHERE id = ?", [$id]);
    if (!$ad) {
        Response::notFound('广告不存在');
    }

    $input = parseJsonInput();
    $data = [];

    $allowedFields = ['title', 'image', 'link', 'position', 'sort', 'status', 'start_at', 'end_at'];
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            if (in_array($field, ['sort', 'status'], true)) {
                $data[$field] = (int)$input[$field];
            } else {
                $data[$field] = $input[$field];
            }
        }
    }

    if (isset($input['image_file']) && is_array($input['image_file'])) {
        $imgResult = Uploader::uploadImage($input['image_file'], 'ads');
        if ($imgResult['success']) {
            if (!empty($ad['image'])) {
                Uploader::delete($ad['image']);
            }
            $data['image'] = $imgResult['path'];
        }
    }

    if (empty($data)) {
        Response::error('没有需要更新的字段');
    }

    Database::update('advertisement', $data, 'id = ?', [$id]);

    Response::success(null, '更新成功');
}

if ($method === 'DELETE' && $id !== null) {
    $ad = Database::fetch("SELECT id, image FROM advertisement WHERE id = ?", [$id]);
    if (!$ad) {
        Response::notFound('广告不存在');
    }

    Database::delete('advertisement', 'id = ?', [$id]);

    if (!empty($ad['image'])) {
        Uploader::delete($ad['image']);
    }

    Response::success(null, '删除成功');
}

if ($method === 'POST' && $id !== null && $action === 'toggle') {
    $ad = Database::fetch("SELECT id, status FROM advertisement WHERE id = ?", [$id]);
    if (!$ad) {
        Response::notFound('广告不存在');
    }

    $newStatus = (int)$ad['status'] === 1 ? 0 : 1;
    Database::update('advertisement', ['status' => $newStatus], 'id = ?', [$id]);

    Response::success(['status' => $newStatus], $newStatus === 1 ? '已启用' : '已禁用');
}

Response::error('请求方式不允许', Response::CODE_BAD_REQUEST);