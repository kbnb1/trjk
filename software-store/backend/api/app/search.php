<?php

namespace App;

if ($_ROUTE_METHOD !== 'GET') {
    Response::error('请求方法不允许，仅支持 GET', Response::CODE_BAD_REQUEST);
}

$keyword = trim($_GET['keyword'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = max(1, min(100, (int)($_GET['per_page'] ?? 20)));
$offset = ($page - 1) * $perPage;

if (empty($keyword)) {
    Response::error('搜索关键词不能为空');
}

$user = getUserFromToken();

$useFulltext = false;
$keywordLen = mb_strlen($keyword);
if ($keywordLen >= 2) {
    $useFulltext = true;
}

$where = ['s.status = 1'];
$params = [];

if ($useFulltext) {
    $where[] = "MATCH(s.name, s.subtitle, s.description) AGAINST(? IN BOOLEAN MODE)";
    $params[] = $keyword;
} else {
    $where[] = '(s.name LIKE ? OR s.subtitle LIKE ?)';
    $likeKeyword = '%' . $keyword . '%';
    $params[] = $likeKeyword;
    $params[] = $likeKeyword;
}

$whereStr = implode(' AND ', $where);

$countSql = "SELECT COUNT(*) as total FROM software s WHERE {$whereStr}";
$countRow = Database::fetch($countSql, $params);
$total = (int)($countRow['total'] ?? 0);

$list = [];
if ($total > 0) {
    $list = Database::fetchAll(
        "SELECT s.*, c.name AS category_name
         FROM software s
         LEFT JOIN category c ON c.id = s.category_id
         WHERE {$whereStr}
         ORDER BY s.sort DESC, s.id DESC
         LIMIT {$perPage} OFFSET {$offset}",
        $params
    );
}

$isFavCheck = $user ? (int)$user['id'] : null;
$list = array_map(function ($row) use ($isFavCheck) {
    $row = formatSoftwareRow($row);
    $row['is_favorite'] = $isFavCheck ? checkIsFavorite($isFavCheck, (int)$row['id']) : false;
    return $row;
}, $list);

$suggestions = [];
if ($total === 0 && $keywordLen >= 2) {
    $suggestions = Database::fetchAll(
        "SELECT DISTINCT name FROM software WHERE status = 1 AND name LIKE ? LIMIT 5",
        ['%' . $keyword . '%']
    );
    $suggestions = array_column($suggestions, 'name');
}

Response::success([
    'keyword'     => $keyword,
    'list'        => $list,
    'total'       => $total,
    'page'        => $page,
    'per_page'    => $perPage,
    'suggestions' => $suggestions,
], '搜索完成');
