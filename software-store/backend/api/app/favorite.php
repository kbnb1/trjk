<?php

namespace App;

$segments = $_ROUTE_SEGMENTS;
$method = $_ROUTE_METHOD;

$resource = $segments[0] ?? '';

if ($resource === 'favorites' && $method === 'GET') {
    $user = requireAuth();

    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 20)));
    $offset = ($page - 1) * $perPage;

    $countSql = "SELECT COUNT(*) as total FROM favorite WHERE user_id = ?";
    $countRow = Database::fetch($countSql, [(int)$user['id']]);
    $total = (int)($countRow['total'] ?? 0);

    $list = Database::fetchAll(
        "SELECT f.id AS favorite_id, f.created_at AS favorited_at,
                s.*, c.name AS category_name
         FROM favorite f
         LEFT JOIN software s ON s.id = f.software_id
         LEFT JOIN category c ON c.id = s.category_id
         WHERE f.user_id = ? AND s.status = 1
         ORDER BY f.created_at DESC
         LIMIT {$perPage} OFFSET {$offset}",
        [(int)$user['id']]
    );

    $list = array_map(function ($row) {
        $row = formatSoftwareRow($row);
        $row['is_favorite'] = true;
        unset($row['favorite_id']);
        unset($row['favorited_at']);
        return $row;
    }, $list);

    Response::success([
        'list'     => $list,
        'total'    => $total,
        'page'     => $page,
        'per_page' => $perPage,
    ], '获取成功');
}

if ($resource === 'favorite' && $method === 'POST') {
    $user = requireAuth();
    $input = getJsonInput();

    $softwareId = (int)($input['software_id'] ?? 0);
    if ($softwareId <= 0) {
        Response::error('软件 ID 不能为空');
    }

    $software = Database::fetch(
        "SELECT id, name FROM software WHERE id = ? AND status = 1",
        [$softwareId]
    );
    if (!$software) {
        Response::error('软件不存在或已下架');
    }

    $existing = Database::fetch(
        "SELECT id FROM favorite WHERE user_id = ? AND software_id = ?",
        [(int)$user['id'], $softwareId]
    );

    if ($existing) {
        Database::delete('favorite', 'id = ?', [(int)$existing['id']]);
        Response::success(['is_favorite' => false], '已取消收藏');
    }

    Database::insert('favorite', [
        'user_id'     => (int)$user['id'],
        'software_id' => $softwareId,
    ]);

    Response::success(['is_favorite' => true], '收藏成功');
}

Response::error('请求方法不允许', Response::CODE_BAD_REQUEST);
