<?php

namespace App;

$route = $GLOBALS['_route'];
$method = $route['method'];
$id = $route['id'] ? (int)$route['id'] : null;
$action = $route['action'];

if ($method === 'GET' && $id === null) {
    $type = ($_GET['type'] ?? '') !== '' ? (int)$_GET['type'] : null;
    $status = ($_GET['status'] ?? '') !== '' ? (int)$_GET['status'] : null;

    $where = ['1=1'];
    $params = [];

    if ($type !== null) {
        $where[] = 'type = ?';
        $params[] = $type;
    }
    if ($status !== null) {
        $where[] = 'status = ?';
        $params[] = $status;
    }

    $list = Database::fetchAll(
        "SELECT * FROM notice WHERE " . implode(' AND ', $where) . " ORDER BY is_top DESC, sort DESC, id DESC",
        $params
    );

    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['type'] = (int)$item['type'];
        $item['is_top'] = (int)$item['is_top'];
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
    $notice = Database::fetch("SELECT * FROM notice WHERE id = ?", [$id]);
    if (!$notice) {
        Response::notFound('公告不存在');
    }

    $notice['id'] = (int)$notice['id'];
    $notice['type'] = (int)$notice['type'];
    $notice['is_top'] = (int)$notice['is_top'];
    $notice['sort'] = (int)$notice['sort'];
    $notice['status'] = (int)$notice['status'];
    $notice['start_at'] = formatDate($notice['start_at']);
    $notice['end_at'] = formatDate($notice['end_at']);
    $notice['created_at'] = formatDate($notice['created_at']);

    Response::success($notice);
}

if ($method === 'POST' && $id === null) {
    $input = parseJsonInput();

    $type = (int)($input['type'] ?? 0);
    if (!in_array($type, [1, 2, 3], true)) {
        Response::error('公告类型无效');
    }

    $validator = new Validator($input);
    $errors = $validator
        ->field('title', '标题')->required()->string(255)
        ->field('content', '内容')->required()->string(50000)
        ->field('is_top', '置顶')->in(['0', '1'])
        ->field('status', '状态')->in(['0', '1'])
        ->check();

    if (!empty($errors)) {
        Response::error('参数验证失败: ' . implode('; ', array_merge(...array_values($errors))));
    }

    $existing = Database::fetch(
        "SELECT id FROM notice WHERE type = ?",
        [$type]
    );

    $data = [
        'title'   => $input['title'],
        'content' => $input['content'],
        'type'    => $type,
        'is_top'  => (int)($input['is_top'] ?? 0),
        'sort'    => (int)($input['sort'] ?? 0),
        'status'  => (int)($input['status'] ?? 1),
        'start_at'=> $input['start_at'] ?? date('Y-m-d H:i:s'),
        'end_at'  => $input['end_at'] ?? date('Y-m-d H:i:s', strtotime('+365 days')),
    ];

    if ($existing && in_array($type, [2, 3], true)) {
        Database::update('notice', $data, 'id = ?', [$existing['id']]);
        Response::success(['id' => (int)$existing['id']], '更新成功');
    }

    $newId = Database::insert('notice', $data);
    Response::success(['id' => $newId], '创建成功');
}

if ($method === 'PUT' && $id !== null) {
    $notice = Database::fetch("SELECT id FROM notice WHERE id = ?", [$id]);
    if (!$notice) {
        Response::notFound('公告不存在');
    }

    $input = parseJsonInput();
    $data = [];

    $allowedFields = ['title', 'content', 'type', 'is_top', 'sort', 'status', 'start_at', 'end_at'];
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            if (in_array($field, ['type', 'is_top', 'sort', 'status'], true)) {
                $data[$field] = (int)$input[$field];
            } else {
                $data[$field] = $input[$field];
            }
        }
    }

    if (empty($data)) {
        Response::error('没有需要更新的字段');
    }

    Database::update('notice', $data, 'id = ?', [$id]);

    Response::success(null, '更新成功');
}

if ($method === 'DELETE' && $id !== null) {
    $notice = Database::fetch("SELECT id FROM notice WHERE id = ?", [$id]);
    if (!$notice) {
        Response::notFound('公告不存在');
    }

    Database::delete('notice', 'id = ?', [$id]);

    Response::success(null, '删除成功');
}

Response::error('请求方式不允许', Response::CODE_BAD_REQUEST);