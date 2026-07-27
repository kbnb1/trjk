<?php

namespace App;

$segments = $_ROUTE_SEGMENTS;
$method = $_ROUTE_METHOD;

$softwareId = $segments[1] ?? null;

if ($softwareId !== null && $method === 'GET') {
    $softwareId = (int)$softwareId;
    if ($softwareId <= 0) {
        Response::error('无效的软件 ID', Response::CODE_BAD_REQUEST);
    }

    $user = getUserFromToken();

    $software = Database::fetch(
        "SELECT s.*, c.name AS category_name, c.id AS category_id_val
         FROM software s
         LEFT JOIN category c ON c.id = s.category_id
         WHERE s.id = ? AND s.status = 1",
        [$softwareId]
    );

    if (!$software) {
        Response::notFound('软件不存在或已下架');
    }

    Database::query(
        "UPDATE software SET view_count = view_count + 1 WHERE id = ?",
        [$softwareId]
    );

    $software = formatSoftwareRow($software);
    $software['is_favorite'] = $user ? checkIsFavorite((int)$user['id'], $softwareId) : false;

    $related = Database::fetchAll(
        "SELECT s.*, c.name AS category_name
         FROM software s
         LEFT JOIN category c ON c.id = s.category_id
         WHERE s.status = 1 AND s.category_id = ? AND s.id != ?
         ORDER BY s.sort DESC, s.id DESC
         LIMIT 8",
        [(int)$software['category_id'], $softwareId]
    );

    $related = array_map(function ($row) use ($user) {
        $row = formatSoftwareRow($row);
        $row['is_favorite'] = $user ? checkIsFavorite((int)$user['id'], (int)$row['id']) : false;
        return $row;
    }, $related);

    Response::success([
        'software' => $software,
        'related'  => $related,
    ], '获取成功');
}

if ($method === 'GET') {
    $user = getUserFromToken();

    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 20)));
    $offset = ($page - 1) * $perPage;

    $filters = [
        'category_id' => $_GET['category_id'] ?? null,
        'keyword'     => $_GET['keyword'] ?? null,
        'sort'        => $_GET['sort'] ?? 's.sort DESC, s.id DESC',
    ];

    [$countSql, $dataSql, $params] = getSoftwareListSql($filters);

    $countRow = Database::fetch($countSql, $params);
    $total = (int)($countRow['total'] ?? 0);

    $list = Database::fetchAll(
        $dataSql . " LIMIT {$perPage} OFFSET {$offset}",
        $params
    );

    $isFavCheck = $user ? (int)$user['id'] : null;
    $list = array_map(function ($row) use ($isFavCheck) {
        $row = formatSoftwareRow($row);
        $row['is_favorite'] = $isFavCheck ? checkIsFavorite($isFavCheck, (int)$row['id']) : false;
        return $row;
    }, $list);

    Response::success([
        'list'  => $list,
        'total' => $total,
        'page'  => $page,
        'per_page' => $perPage,
    ], '获取成功');
}

Response::error('请求方法不允许', Response::CODE_BAD_REQUEST);
