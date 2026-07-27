<?php

namespace App;

if ($_ROUTE_METHOD !== 'GET') {
    Response::error('请求方法不允许', Response::CODE_BAD_REQUEST);
}

$list = Database::fetchAll(
    "SELECT id, name, icon, link_type, link_value, sort
     FROM toolbar
     WHERE status = 1
     ORDER BY sort DESC, id ASC"
);

Response::success([
    'list' => $list,
], '获取成功');
