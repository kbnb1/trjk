<?php

namespace App;

if ($_ROUTE_METHOD !== 'GET') {
    Response::error('请求方法不允许', Response::CODE_BAD_REQUEST);
}

$parentId = (int)($_GET['parent_id'] ?? 0);
$keyword = trim($_GET['keyword'] ?? '');

$sql = "SELECT id, parent_id, name, icon, image, keywords, sort, status, software_count
        FROM category
        WHERE status = 1";
$params = [];

if ($parentId >= 0) {
    $sql .= " AND parent_id = ?";
    $params[] = $parentId;
}

if (!empty($keyword)) {
    $sql .= " AND (name LIKE ? OR keywords LIKE ?)";
    $likeKeyword = '%' . $keyword . '%';
    $params[] = $likeKeyword;
    $params[] = $likeKeyword;
}

$sql .= " ORDER BY sort DESC, id ASC";

$list = Database::fetchAll($sql, $params);

Response::success([
    'list' => $list,
], '获取成功');
