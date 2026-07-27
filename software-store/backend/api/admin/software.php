<?php

namespace App;

$route = $GLOBALS['_route'];
$method = $route['method'];
$id = $route['id'] ? (int)$route['id'] : null;
$action = $route['action'];

if ($method === 'GET' && $id === null) {
    $pagination = getPagination();
    $search = trim($_GET['search'] ?? '');
    $categoryId = (int)($_GET['category_id'] ?? 0);
    $status = ($_GET['status'] ?? '') !== '' ? (int)$_GET['status'] : null;
    $isRecommend = ($_GET['is_recommend'] ?? '') !== '' ? (int)$_GET['is_recommend'] : null;

    $where = ['1=1'];
    $params = [];

    if ($search !== '') {
        $where[] = '(name LIKE ? OR subtitle LIKE ?)';
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    if ($categoryId > 0) {
        $where[] = 'category_id = ?';
        $params[] = $categoryId;
    }
    if ($status !== null) {
        $where[] = 'status = ?';
        $params[] = $status;
    }
    if ($isRecommend !== null) {
        $where[] = 'is_recommend = ?';
        $params[] = $isRecommend;
    }

    $whereSql = implode(' AND ', $where);

    $total = (int)Database::fetch(
        "SELECT COUNT(*) as cnt FROM software WHERE {$whereSql}",
        $params
    )['cnt'];

    $list = Database::fetchAll(
        "SELECT s.*, c.name as category_name
         FROM software s
         LEFT JOIN category c ON c.id = s.category_id
         WHERE {$whereSql}
         ORDER BY s.sort DESC, s.id DESC
         LIMIT ? OFFSET ?",
        array_merge($params, [$pagination['per_page'], $pagination['offset']])
    );

    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['category_id'] = (int)$item['category_id'];
        $item['download_count'] = (int)$item['download_count'];
        $item['view_count'] = (int)$item['view_count'];
        $item['like_count'] = (int)$item['like_count'];
        $item['comment_count'] = (int)$item['comment_count'];
        $item['rating'] = (float)$item['rating'];
        $item['rating_count'] = (int)$item['rating_count'];
        $item['price'] = (float)$item['price'];
        $item['is_free'] = (int)$item['is_free'];
        $item['is_recommend'] = (int)$item['is_recommend'];
        $item['is_hot'] = (int)$item['is_hot'];
        $item['is_new'] = (int)$item['is_new'];
        $item['sort'] = (int)$item['sort'];
        $item['status'] = (int)$item['status'];
        $item['size'] = (int)$item['size'];
        $item['created_at'] = formatDate($item['created_at']);
        $item['updated_at'] = formatDate($item['updated_at']);
        $item['images'] = $item['images'] ? json_decode($item['images'], true) : [];
    }
    unset($item);

    Response::success([
        'list'  => $list,
        'total' => $total,
        'page'  => $pagination['page'],
        'per_page' => $pagination['per_page'],
    ]);
}

if ($method === 'GET' && $id !== null) {
    $software = Database::fetch(
        "SELECT * FROM software WHERE id = ?",
        [$id]
    );

    if (!$software) {
        Response::notFound('软件不存在');
    }

    $software['id'] = (int)$software['id'];
    $software['category_id'] = (int)$software['category_id'];
    $software['download_count'] = (int)$software['download_count'];
    $software['view_count'] = (int)$software['view_count'];
    $software['like_count'] = (int)$software['like_count'];
    $software['comment_count'] = (int)$software['comment_count'];
    $software['rating'] = (float)$software['rating'];
    $software['rating_count'] = (int)$software['rating_count'];
    $software['price'] = (float)$software['price'];
    $software['is_free'] = (int)$software['is_free'];
    $software['is_recommend'] = (int)$software['is_recommend'];
    $software['is_hot'] = (int)$software['is_hot'];
    $software['is_new'] = (int)$software['is_new'];
    $software['sort'] = (int)$software['sort'];
    $software['status'] = (int)$software['status'];
    $software['size'] = (int)$software['size'];
    $software['images'] = $software['images'] ? json_decode($software['images'], true) : [];
    $software['created_at'] = formatDate($software['created_at']);
    $software['updated_at'] = formatDate($software['updated_at']);

    Response::success($software);
}

if ($method === 'POST' && $id === null) {
    $input = parseJsonInput();

    $validator = new Validator($input);
    $errors = $validator
        ->field('name', '软件名称')->required()->string(128)
        ->field('category_id', '分类ID')->required()->integer()->min(1)
        ->field('description', '软件描述')->string(50000)
        ->field('version', '版本号')->string(32)
        ->field('price', '价格')->string(32)
        ->field('is_free', '是否免费')->in(['0', '1'])
        ->field('is_recommend', '是否推荐')->in(['0', '1'])
        ->field('is_hot', '是否热门')->in(['0', '1'])
        ->field('is_new', '是否最新')->in(['0', '1'])
        ->field('status', '状态')->in(['0', '1'])
        ->field('sort', '排序')->integer()
        ->check();

    if (!empty($errors)) {
        Response::error('参数验证失败: ' . implode('; ', array_merge(...array_values($errors))));
    }

    $category = Database::fetch("SELECT id FROM category WHERE id = ?", [(int)$input['category_id']]);
    if (!$category) {
        Response::error('分类不存在');
    }

    $data = [
        'category_id'     => (int)$input['category_id'],
        'name'            => $input['name'],
        'subtitle'        => $input['subtitle'] ?? '',
        'description'     => $input['description'] ?? '',
        'icon'            => $input['icon'] ?? '',
        'cover'           => $input['cover'] ?? '',
        'version'         => $input['version'] ?? '1.0.0',
        'size'            => (int)($input['size'] ?? 0),
        'download_url'    => $input['download_url'] ?? '',
        'price'           => (float)($input['price'] ?? 0),
        'is_free'         => (int)($input['is_free'] ?? 1),
        'is_recommend'    => (int)($input['is_recommend'] ?? 0),
        'is_hot'          => (int)($input['is_hot'] ?? 0),
        'is_new'          => (int)($input['is_new'] ?? 0),
        'platform'        => $input['platform'] ?? '',
        'language'        => $input['language'] ?? '',
        'developer'       => $input['developer'] ?? '',
        'website'         => $input['website'] ?? '',
        'tags'            => $input['tags'] ?? '',
        'sort'            => (int)($input['sort'] ?? 0),
        'status'          => (int)($input['status'] ?? 1),
    ];

    if (isset($input['images']) && is_array($input['images'])) {
        $data['images'] = json_encode($input['images'], JSON_UNESCAPED_UNICODE);
    } else {
        $data['images'] = null;
    }

    if (isset($input['apk_file']) && is_array($input['apk_file'])) {
        $apkResult = Uploader::uploadApk($input['apk_file'], date('Y'));
        if (!$apkResult['success']) {
            Response::error('APK 上传失败: ' . $apkResult['message']);
        }
        $data['download_url'] = $apkResult['path'];
        $data['size'] = $input['apk_file']['size'] ?? 0;
    }

    if (isset($input['icon_file']) && is_array($input['icon_file'])) {
        $iconResult = Uploader::uploadImage($input['icon_file'], 'icons');
        if ($iconResult['success']) {
            $data['icon'] = $iconResult['path'];
        }
    }

    if (isset($input['cover_file']) && is_array($input['cover_file'])) {
        $coverResult = Uploader::uploadImage($input['cover_file'], 'covers');
        if ($coverResult['success']) {
            $data['cover'] = $coverResult['path'];
        }
    }

    $newId = Database::insert('software', $data);

    Database::update('category', ['software_count' => 'software_count + 1'], 'id = ?', [(int)$data['category_id']]);

    Response::success(['id' => $newId], '创建成功');
}

if ($method === 'PUT' && $id !== null) {
    $software = Database::fetch("SELECT id, category_id, icon, cover, download_url FROM software WHERE id = ?", [$id]);
    if (!$software) {
        Response::notFound('软件不存在');
    }

    $input = parseJsonInput();

    $data = [];
    $allowedFields = [
        'name', 'subtitle', 'description', 'icon', 'cover', 'version',
        'download_url', 'price', 'is_free', 'is_recommend', 'is_hot', 'is_new',
        'platform', 'language', 'developer', 'website', 'tags', 'sort', 'status',
        'size',
    ];

    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            if (in_array($field, ['is_free', 'is_recommend', 'is_hot', 'is_new', 'status'], true)) {
                $data[$field] = (int)$input[$field];
            } elseif ($field === 'price' || $field === 'rating') {
                $data[$field] = (float)$input[$field];
            } elseif ($field === 'size') {
                $data[$field] = (int)$input[$field];
            } else {
                $data[$field] = $input[$field];
            }
        }
    }

    if (isset($input['category_id'])) {
        $data['category_id'] = (int)$input['category_id'];
        $category = Database::fetch("SELECT id FROM category WHERE id = ?", [$data['category_id']]);
        if (!$category) {
            Response::error('分类不存在');
        }
        if ($data['category_id'] != $software['category_id']) {
            Database::update('category', ['software_count' => 'software_count - 1'], 'id = ?', [$software['category_id']]);
            Database::update('category', ['software_count' => 'software_count + 1'], 'id = ?', [$data['category_id']]);
        }
    }

    if (isset($input['images']) && is_array($input['images'])) {
        $data['images'] = json_encode($input['images'], JSON_UNESCAPED_UNICODE);
    }

    if (isset($input['apk_file']) && is_array($input['apk_file'])) {
        $apkResult = Uploader::uploadApk($input['apk_file'], date('Y'));
        if ($apkResult['success']) {
            if (!empty($software['download_url'])) {
                Uploader::delete($software['download_url']);
            }
            $data['download_url'] = $apkResult['path'];
            $data['size'] = $input['apk_file']['size'] ?? $software['size'];
        }
    }

    if (isset($input['icon_file']) && is_array($input['icon_file'])) {
        $iconResult = Uploader::uploadImage($input['icon_file'], 'icons');
        if ($iconResult['success']) {
            if (!empty($software['icon'])) {
                Uploader::delete($software['icon']);
            }
            $data['icon'] = $iconResult['path'];
        }
    }

    if (isset($input['cover_file']) && is_array($input['cover_file'])) {
        $coverResult = Uploader::uploadImage($input['cover_file'], 'covers');
        if ($coverResult['success']) {
            if (!empty($software['cover'])) {
                Uploader::delete($software['cover']);
            }
            $data['cover'] = $coverResult['path'];
        }
    }

    if (empty($data)) {
        Response::error('没有需要更新的字段');
    }

    Database::update('software', $data, 'id = ?', [$id]);

    Response::success(null, '更新成功');
}

if ($method === 'DELETE' && $id !== null) {
    $software = Database::fetch("SELECT id, icon, cover, download_url FROM software WHERE id = ?", [$id]);
    if (!$software) {
        Response::notFound('软件不存在');
    }

    Database::delete('software', 'id = ?', [$id]);

    if (!empty($software['icon'])) {
        Uploader::delete($software['icon']);
    }
    if (!empty($software['cover'])) {
        Uploader::delete($software['cover']);
    }
    if (!empty($software['download_url'])) {
        Uploader::delete($software['download_url']);
    }

    Response::success(null, '删除成功');
}

if ($method === 'POST' && $id !== null && $action === 'toggle') {
    $software = Database::fetch("SELECT id, status FROM software WHERE id = ?", [$id]);
    if (!$software) {
        Response::notFound('软件不存在');
    }

    $newStatus = (int)$software['status'] === 1 ? 0 : 1;
    Database::update('software', ['status' => $newStatus], 'id = ?', [$id]);

    Response::success(['status' => $newStatus], $newStatus === 1 ? '已上架' : '已下架');
}

Response::error('请求方式不允许', Response::CODE_BAD_REQUEST);