<?php

namespace App;

if ($_ROUTE_METHOD !== 'GET') {
    Response::error('请求方法不允许', Response::CODE_BAD_REQUEST);
}

$splash = Database::fetch(
    "SELECT id, image, duration, link_type, link_value, status
     FROM splash
     WHERE status = 1
     ORDER BY sort DESC, id DESC
     LIMIT 1"
);

if (!$splash) {
    $config = require __DIR__ . '/../../include/config.php';
    Response::success([
        'image'       => '',
        'duration'    => 3,
        'link_type'   => 0,
        'link_value'  => '',
        'site_name'   => $config['site']['name'] ?? '软件商店',
        'site_logo'   => '',
    ], '获取成功');
}

Response::success($splash, '获取成功');
