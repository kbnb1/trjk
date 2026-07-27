<?php

namespace App;

$route = $GLOBALS['_route'];
$method = $route['method'];
$id = $route['id'] ? (int)$route['id'] : null;
$action = $route['action'];

if ($method === 'GET') {
    $status = ($_GET['status'] ?? '') !== '' ? (int)$_GET['status'] : null;

    $where = ['1=1'];
    $params = [];

    if ($status !== null) {
        $where[] = 'status = ?';
        $params[] = $status;
    }

    $list = Database::fetchAll(
        "SELECT * FROM toolbar WHERE " . implode(' AND ', $where) . " ORDER BY sort DESC, id ASC",
        $params
    );

    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['link_type'] = (int)$item['link_type'];
        $item['sort'] = (int)$item['sort'];
        $item['status'] = (int)$item['status'];
        $item['created_at'] = formatDate($item['created_at']);
    }
    unset($item);

    Response::success($list);
}

if ($method === 'POST' && $id === null && $action === null) {
    $input = parseJsonInput();

    $validator = new Validator($input);
    $errors = $validator
        ->field('name', '名称')->required()->string(64)
        ->field('link_type', '跳转类型')->required()->in(['1', '2', '3', '4'])
        ->field('link_value', '跳转值')->string(512)
        ->field('sort', '排序')->integer()
        ->field('status', '状态')->in(['0', '1'])
        ->check();

    if (!empty($errors)) {
        Response::error('参数验证失败: ' . implode('; ', array_merge(...array_values($errors))));
    }

    $data = [
        'name'       => $input['name'],
        'icon'       => $input['icon'] ?? '',
        'link_type'  => (int)$input['link_type'],
        'link_value' => $input['link_value'] ?? '',
        'sort'       => (int)($input['sort'] ?? 0),
        'status'     => (int)($input['status'] ?? 1),
    ];

    $newId = Database::insert('toolbar', $data);

    Response::success(['id' => $newId], '创建成功');
}

if ($method === 'POST' && $id === null && $action === 'sort') {
    $input = parseJsonInput();

    if (!isset($input['items']) || !is_array($input['items'])) {
        Response::error('参数错误: items 数组必填');
    }

    foreach ($input['items'] as $item) {
        if (!isset($item['id']) || !isset($item['sort'])) {
            continue;
        }
        Database::update('toolbar', ['sort' => (int)$item['sort']], 'id = ?', [(int)$item['id']]);
    }

    Response::success(null, '排序更新成功');
}

if ($method === 'PUT' && $id !== null) {
    $toolbar = Database::fetch("SELECT id FROM toolbar WHERE id = ?", [$id]);
    if (!$toolbar) {
        Response::notFound('工具栏项不存在');
    }

    $input = parseJsonInput();
    $data = [];

    $allowedFields = ['name', 'icon', 'link_type', 'link_value', 'sort', 'status'];
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            if (in_array($field, ['link_type', 'sort', 'status'], true)) {
                $data[$field] = (int)$input[$field];
            } else {
                $data[$field] = $input[$field];
            }
        }
    }

    if (empty($data)) {
        Response::error('没有需要更新的字段');
    }

    Database::update('toolbar', $data, 'id = ?', [$id]);

    Response::success(null, '更新成功');
}

if ($method === 'DELETE' && $id !== null) {
    $toolbar = Database::fetch("SELECT id FROM toolbar WHERE id = ?", [$id]);
    if (!$toolbar) {
        Response::notFound('工具栏项不存在');
    }

    Database::delete('toolbar', 'id = ?', [$id]);

    Response::success(null, '删除成功');
}

if ($method === 'POST' && $id !== null && $action === 'toggle') {
    $toolbar = Database::fetch("SELECT id, status FROM toolbar WHERE id = ?", [$id]);
    if (!$toolbar) {
        Response::notFound('工具栏项不存在');
    }

    $newStatus = (int)$toolbar['status'] === 1 ? 0 : 1;
    Database::update('toolbar', ['status' => $newStatus], 'id = ?', [$id]);

    Response::success(['status' => $newStatus], $newStatus === 1 ? '已显示' : '已隐藏');
}

Response::error('请求方式不允许', Response::CODE_BAD_REQUEST);