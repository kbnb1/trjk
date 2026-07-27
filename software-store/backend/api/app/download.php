<?php

namespace App;

$segments = $_ROUTE_SEGMENTS;
$method = $_ROUTE_METHOD;
$resource = $segments[0] ?? '';

if ($resource === 'downloads' && $method === 'GET') {
    $user = requireAuth();

    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 20)));
    $offset = ($page - 1) * $perPage;

    $countSql = "SELECT COUNT(*) as total FROM download_record WHERE user_id = ?";
    $countRow = Database::fetch($countSql, [(int)$user['id']]);
    $total = (int)($countRow['total'] ?? 0);

    $list = Database::fetchAll(
        "SELECT dr.id, dr.software_id, dr.software_name, dr.version,
                dr.ip, dr.user_agent, dr.created_at,
                s.icon, s.cover, s.name AS software_real_name
         FROM download_record dr
         LEFT JOIN software s ON s.id = dr.software_id
         WHERE dr.user_id = ?
         ORDER BY dr.created_at DESC
         LIMIT {$perPage} OFFSET {$offset}",
        [(int)$user['id']]
    );

    Response::success([
        'list'     => $list,
        'total'    => $total,
        'page'     => $page,
        'per_page' => $perPage,
    ], '获取成功');
}

$subResource = $segments[1] ?? null;

if ($resource === 'download' && $subResource === null && $method === 'POST') {
    $user = requireAuth();
    $input = getJsonInput();

    $softwareId = (int)($input['software_id'] ?? 0);
    if ($softwareId <= 0) {
        Response::error('软件 ID 不能为空');
    }

    $software = Database::fetch(
        "SELECT id, name, version, download_count FROM software WHERE id = ? AND status = 1",
        [$softwareId]
    );
    if (!$software) {
        Response::error('软件不存在或已下架');
    }

    $needLogin = (bool)getUserConfigValue('download', 'need_login', true);
    if ($needLogin && !$user) {
        Response::unauthorized('请先登录后再下载');
    }

    $limit = (int)getUserConfigValue('download', 'download_limit', 100);
    if ($limit > 0) {
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        $todayCount = Database::fetch(
            "SELECT COUNT(*) as cnt FROM download_record
             WHERE user_id = ? AND created_at BETWEEN ? AND ?",
            [(int)$user['id'], $todayStart, $todayEnd]
        );
        if ((int)($todayCount['cnt'] ?? 0) >= $limit) {
            Response::error('今日下载量已达上限');
        }
    }

    $recordId = Database::insert('download_record', [
        'user_id'       => (int)$user['id'],
        'software_id'   => $softwareId,
        'software_name' => $software['name'],
        'version'       => $software['version'],
        'ip'            => getClientIp(),
        'user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? '',
    ]);

    Database::query(
        "UPDATE software SET download_count = download_count + 1 WHERE id = ?",
        [$softwareId]
    );

    Response::success([
        'record_id' => $recordId,
        'software'  => [
            'id'            => $software['id'],
            'name'          => $software['name'],
            'version'       => $software['version'],
            'download_url'  => $software['download_url'] ?? '',
            'download_count' => (int)$software['download_count'] + 1,
        ],
    ], '下载记录已创建');
}

if ($resource === 'download' && $subResource !== null && $method === 'POST') {
    $user = requireAuth();
    $recordId = (int)$segments[1];
    $progressAction = $segments[2] ?? '';

    if ($progressAction !== 'progress') {
        Response::error('未知的下载操作', Response::CODE_BAD_REQUEST);
    }

    $record = Database::fetch(
        "SELECT id, user_id, software_id FROM download_record WHERE id = ? AND user_id = ?",
        [$recordId, (int)$user['id']]
    );
    if (!$record) {
        Response::error('下载记录不存在', Response::CODE_NOT_FOUND);
    }

    $input = getJsonInput();
    $progress = (int)($input['progress'] ?? 0);
    $status = $input['status'] ?? 'downloading';

    $allowedStatuses = ['downloading', 'paused', 'completed', 'failed', 'canceled'];
    if (!in_array($status, $allowedStatuses, true)) {
        Response::error('无效的状态值');
    }

    if ($progress < 0 || $progress > 100) {
        Response::error('进度值必须在 0-100 之间');
    }

    $progressTableSql = "CREATE TABLE IF NOT EXISTS `download_progress` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `record_id` BIGINT UNSIGNED NOT NULL,
        `user_id` INT UNSIGNED NOT NULL,
        `software_id` INT UNSIGNED NOT NULL,
        `progress` TINYINT UNSIGNED NOT NULL DEFAULT 0,
        `status` VARCHAR(32) NOT NULL DEFAULT 'downloading',
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_record` (`record_id`),
        KEY `idx_user_id` (`user_id`),
        KEY `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    try {
        Database::query($progressTableSql);
    } catch (\Throwable $e) {
    }

    $existingMeta = Database::fetch(
        "SELECT id FROM download_progress WHERE record_id = ?",
        [$recordId]
    );

    if ($existingMeta) {
        Database::update('download_progress', [
            'progress'   => $progress,
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'record_id = ?', [$recordId]);
    } else {
        Database::insert('download_progress', [
            'record_id'   => $recordId,
            'user_id'     => (int)$user['id'],
            'software_id' => (int)$record['software_id'],
            'progress'    => $progress,
            'status'      => $status,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    Response::success([
        'record_id' => $recordId,
        'progress'  => $progress,
        'status'    => $status,
    ], '进度已更新');
}

Response::error('请求方法不允许', Response::CODE_BAD_REQUEST);
