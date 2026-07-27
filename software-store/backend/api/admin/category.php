<?php

namespace App;

$route = $GLOBALS['_route'];
$method = $route['method'];
$id = $route['id'] ? (int)$route['id'] : null;
$action = $route['action'];

if ($method === 'GET' && $id === null) {
    $list = Database::fetchAll(
        "SELECT c.*, p.name as parent_name
         FROM category c
         LEFT JOIN category p ON p.id = c.parent_id
         ORDER BY c.sort DESC, c.id ASC"
    );

    $tree = [];
    $byId = [];
    foreach ($list as $item) {
        $item['id'] = (int)$item['id'];
        $item['parent_id'] = (int)$item['parent_id'];
        $item['sort'] = (int)$item['sort'];
        $item['status'] = (int)$item['status'];
        $item['software_count'] = (int)$item['software_count'];
        $byId[$item['id']] = $item;
    }

    $rootIds = [];
    foreach ($byId as $item) {
        if ($item['parent_id'] == 0 || !isset($byId[$item['parent_id']])) {
            $rootIds[] = $item['id'];
        }
    }

    function buildTree($byId, $parentId) {
        $children = [];
        foreach ($byId as $id => $item) {
            if ($item['parent_id'] == $parentId) {
                $node = $item;
                $node['children'] = buildTree($byId, $id);
                $children[] = $node;
            }
        }
        return $children;
    }

    $tree = buildTree($byId, 0);

    Response::success([
        'tree' => $tree,
        'list' => array_values($byId),
    ]);
}

if ($method === 'POST' && $id === null) {
    $input = parseJsonInput();

    $validator = new Validator($input);
    $errors = $validator
        ->field('name', '分类名称')->required()->string(64)
        ->field('parent_id', '父级ID')->integer()
        ->check();

    if (!empty($errors)) {
        Response::error('参数验证失败: ' . implode('; ', array_merge(...array_values($errors))));
    }

    $parentId = (int)($input['parent_id'] ?? 0);
    if ($parentId > 0) {
        $parent = Database::fetch("SELECT id FROM category WHERE id = ?", [$parentId]);
        if (!$parent) {
            Response::error('父级分类不存在');
        }
    }

    $existing = Database::fetch(
        "SELECT id FROM category WHERE name = ? AND parent_id = ?",
        [$input['name'], $parentId]
    );
    if ($existing) {
        Response::error('同级下已存在该分类名称');
    }

    $data = [
        'parent_id' => $parentId,
        'name'      => $input['name'],
        'icon'      => $input['icon'] ?? '',
        'image'     => $input['image'] ?? '',
        'keywords'  => $input['keywords'] ?? '',
        'sort'      => (int)($input['sort'] ?? 0),
        'status'    => (int)($input['status'] ?? 1),
    ];

    $newId = Database::insert('category', $data);

    Response::success(['id' => $newId], '创建成功');
}

if ($method === 'PUT' && $id !== null) {
    $category = Database::fetch("SELECT id FROM category WHERE id = ?", [$id]);
    if (!$category) {
        Response::notFound('分类不存在');
    }

    $input = parseJsonInput();
    $data = [];

    $allowedFields = ['name', 'icon', 'image', 'keywords', 'sort', 'status'];
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            if ($field === 'sort' || $field === 'status') {
                $data[$field] = (int)$input[$field];
            } else {
                $data[$field] = $input[$field];
            }
        }
    }

    if (isset($input['parent_id'])) {
        $parentId = (int)$input['parent_id'];
        if ($parentId === $id) {
            Response::error('不能将自己设为父级');
        }
        if ($parentId > 0) {
            $parent = Database::fetch("SELECT id FROM category WHERE id = ?", [$parentId]);
            if (!$parent) {
                Response::error('父级分类不存在');
            }
            $children = Database::fetchAll("SELECT id FROM category WHERE parent_id = ?", [$id]);
            foreach ($children as $child) {
                if ((int)$child['id'] === $parentId) {
                    Response::error('不能将子分类设为父级');
                }
            }
        }
        $data['parent_id'] = $parentId;
    }

    if (empty($data)) {
        Response::error('没有需要更新的字段');
    }

    Database::update('category', $data, 'id = ?', [$id]);

    Response::success(null, '更新成功');
}

if ($method === 'DELETE' && $id !== null) {
    $category = Database::fetch("SELECT id, name FROM category WHERE id = ?", [$id]);
    if (!$category) {
        Response::notFound('分类不存在');
    }

    $childCount = (int)Database::fetch(
        "SELECT COUNT(*) as cnt FROM category WHERE parent_id = ?",
        [$id]
    )['cnt'];
    if ($childCount > 0) {
        Response::error('该分类下存在子分类,无法删除');
    }

    $softwareCount = (int)Database::fetch(
        "SELECT COUNT(*) as cnt FROM software WHERE category_id = ?",
        [$id]
    )['cnt'];
    if ($softwareCount > 0) {
        Response::error('该分类下存在软件,无法删除');
    }

    Database::delete('category', 'id = ?', [$id]);

    Response::success(null, '删除成功');
}

if ($method === 'POST' && $id !== null && $action === 'sort') {
    $input = parseJsonInput();
    if (!isset($input['sort']) || !is_numeric($input['sort'])) {
        Response::error('排序值不能为空');
    }

    $category = Database::fetch("SELECT id FROM category WHERE id = ?", [$id]);
    if (!$category) {
        Response::notFound('分类不存在');
    }

    Database::update('category', ['sort' => (int)$input['sort']], 'id = ?', [$id]);

    Response::success(null, '排序更新成功');
}

Response::error('请求方式不允许', Response::CODE_BAD_REQUEST);