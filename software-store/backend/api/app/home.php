<?php

namespace App;

if ($_ROUTE_METHOD !== 'GET') {
    Response::error('请求方法不允许', Response::CODE_BAD_REQUEST);
}

$user = getUserFromToken();

$banners = Database::fetchAll(
    "SELECT id, title, image, link_type, link_value, position, sort
     FROM banner
     WHERE status = 1 AND position = 'home'
     ORDER BY sort DESC, id DESC"
);

$notice = Database::fetch(
    "SELECT id, title, content, type, is_top, sort
     FROM notice
     WHERE status = 1 AND type = 1
     ORDER BY is_top DESC, sort DESC, id DESC
     LIMIT 1"
);

$categories = Database::fetchAll(
    "SELECT id, parent_id, name, icon, image, keywords, sort, software_count
     FROM category
     WHERE status = 1 AND parent_id = 0
     ORDER BY sort DESC, id ASC"
);

$tools = Database::fetchAll(
    "SELECT id, name, icon, link_type, link_value, sort
     FROM toolbar
     WHERE status = 1
     ORDER BY sort DESC, id ASC
     LIMIT 8"
);

$softwareRecommend = Database::fetchAll(
    "SELECT s.id, s.name, s.subtitle, s.icon, s.cover, s.version,
            s.size, s.download_count, s.rating, s.is_free, s.is_recommend,
            s.category_id, c.name AS category_name
     FROM software s
     LEFT JOIN category c ON c.id = s.category_id
     WHERE s.status = 1 AND s.is_recommend = 1
     ORDER BY s.sort DESC, s.id DESC
     LIMIT 6"
);

$softwareHot = Database::fetchAll(
    "SELECT s.id, s.name, s.subtitle, s.icon, s.cover, s.version,
            s.size, s.download_count, s.rating, s.is_free, s.is_hot,
            s.category_id, c.name AS category_name
     FROM software s
     LEFT JOIN category c ON c.id = s.category_id
     WHERE s.status = 1 AND s.is_hot = 1
     ORDER BY s.download_count DESC, s.id DESC
     LIMIT 6"
);

$softwareNew = Database::fetchAll(
    "SELECT s.id, s.name, s.subtitle, s.icon, s.cover, s.version,
            s.size, s.download_count, s.rating, s.is_free, s.is_new,
            s.category_id, c.name AS category_name
     FROM software s
     LEFT JOIN category c ON c.id = s.category_id
     WHERE s.status = 1 AND s.is_new = 1
     ORDER BY s.created_at DESC, s.id DESC
     LIMIT 6"
);

$transformSoftware = function (array $row) use ($user) {
    $row = formatSoftwareRow($row);
    $row['is_favorite'] = $user ? checkIsFavorite((int)$user['id'], (int)$row['id']) : false;
    return $row;
};

Response::success([
    'banners'  => $banners,
    'notice'   => $notice,
    'categories' => $categories,
    'tools'    => $tools,
    'recommend' => array_map($transformSoftware, $softwareRecommend),
    'hot'      => array_map($transformSoftware, $softwareHot),
    'new'      => array_map($transformSoftware, $softwareNew),
], '获取成功');
