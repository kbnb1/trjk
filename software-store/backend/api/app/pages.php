<?php

namespace App;

if ($_ROUTE_METHOD !== 'GET') {
    Response::error('请求方法不允许', Response::CODE_BAD_REQUEST);
}

$slug = trim($_GET['slug'] ?? '');

if (!empty($slug)) {
    $page = Database::fetch(
        "SELECT id, title, content, slug, status, created_at, updated_at
         FROM page WHERE slug = ? AND status = 1",
        [$slug]
    );
    if (!$page) {
        Response::notFound('页面不存在');
    }
    Response::success($page, '获取成功');
}

$list = Database::fetchAll(
    "SELECT id, title, slug, status
     FROM page
     WHERE status = 1
     ORDER BY id DESC
     LIMIT 20"
);

Response::success([
    'list' => $list,
], '获取成功');
