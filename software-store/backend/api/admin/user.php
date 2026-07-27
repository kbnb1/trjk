<?php

namespace App;

$route = $GLOBALS['_route'];
$method = $route['method'];
$id = $route['id'] ? (int)$route['id'] : null;
$action = $route['action'];

if ($method === 'GET' && $id === null) {
    $pagination = getPagination();
    $search = trim($_GET['search'] ?? '');
    $status = ($_GET['status'] ?? '') !== '' ? (int)$_GET['status'] : null;

    $where = ['1=1'];
    $params = [];

    if ($search !== '') {
        $where[] = '(username LIKE ? OR nickname LIKE ? OR email LIKE ? OR phone LIKE ?)';
        $like = "%{$search}%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    if ($status !== null) {
        $where[] = 'status = ?';
        $params[] = $status;
    }

    $whereSql = implode(' AND ', $where);

    $total = (int)Database::fetch(
        "SELECT COUNT(*) as cnt FROM user WHERE {$whereSql}",
        $params
    )['cnt'];

    $list = Database::fetchAll(
        "SELECT id, username, nickname, avatar, email, phone, gender, balance, status,
                last_login_at, last_login_ip, created_at
         FROM user
         WHERE {$whereSql}
         ORDER BY id DESC
         LIMIT ? OFFSET ?",
        array_merge($params, [$pagination['per_page'], $pagination['offset']])
    );

    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['gender'] = (int)$item['gender'];
        $item['balance'] = (float)$item['balance'];
        $item['status'] = (int)$item['status'];
        $item['last_login_at'] = formatDate($item['last_login_at']);
        $item['created_at'] = formatDate($item['created_at']);
    }
    unset($item);

    Response::success([
        'list'  => $list,
        'total' => $total,
        'page'  => $pagination['page'],
        'per_page' => $pagination['per_page'],
    ]);
}

if ($method === 'GET' && $id !== null) {
    $user = Database::fetch(
        "SELECT * FROM user WHERE id = ?",
        [$id]
    );

    if (!$user) {
        Response::notFound('用户不存在');
    }

    $user['id'] = (int)$user['id'];
    $user['gender'] = (int)$user['gender'];
    $user['balance'] = (float)$user['balance'];
    $user['status'] = (int)$user['status'];
    $user['last_login_at'] = formatDate($user['last_login_at']);
    $user['created_at'] = formatDate($user['created_at']);
    $user['updated_at'] = formatDate($user['updated_at']);

    $downloadCount = (int)Database::fetch(
        "SELECT COUNT(*) as cnt FROM download_record WHERE user_id = ?",
        [$id]
    )['cnt'];
    $user['download_count'] = $downloadCount;

    $favoriteCount = (int)Database::fetch(
        "SELECT COUNT(*) as cnt FROM favorite WHERE user_id = ?",
        [$id]
    )['cnt'];
    $user['favorite_count'] = $favoriteCount;

    Response::success($user);
}

if ($method === 'POST' && $id !== null && $action === 'toggle') {
    $user = Database::fetch("SELECT id, status FROM user WHERE id = ?", [$id]);
    if (!$user) {
        Response::notFound('用户不存在');
    }

    $newStatus = (int)$user['status'] === 1 ? 0 : 1;
    Database::update('user', ['status' => $newStatus], 'id = ?', [$id]);

    Response::success(['status' => $newStatus], $newStatus === 1 ? '已启用' : '已禁用');
}

if ($method === 'DELETE' && $id !== null) {
    $user = Database::fetch("SELECT id FROM user WHERE id = ?", [$id]);
    if (!$user) {
        Response::notFound('用户不存在');
    }

    Database::delete('user', 'id = ?', [$id]);

    Response::success(null, '删除成功');
}

Response::error('请求方式不允许', Response::CODE_BAD_REQUEST);