<?php

namespace App;

require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/Database.php';
require_once __DIR__ . '/../../include/Response.php';
require_once __DIR__ . '/../../include/Validator.php';
require_once __DIR__ . '/../../include/Auth.php';
require_once __DIR__ . '/../../include/functions.php';

$config = require __DIR__ . '/../../include/config.php';
\Database::setConfig($config['db'] ?? []);
Auth::setConfig($config['auth'] ?? []);

function getUserFromToken(): ?array
{
    $token = Auth::getTokenFromHeader();
    if (!$token) {
        return null;
    }

    $payload = Auth::verifyToken($token);
    if (!$payload || ($payload['type'] ?? '') !== 'user') {
        return null;
    }

    $userId = (int)($payload['sub'] ?? 0);
    if ($userId <= 0) {
        return null;
    }

    $user = Database::fetch(
        "SELECT id, username, nickname, avatar, email, phone, gender, birthday, signature, balance, status, created_at, updated_at
         FROM user WHERE id = ? AND status = 1",
        [$userId]
    );

    return $user ?: null;
}

function requireAuth(): array
{
    $user = getUserFromToken();
    if (!$user) {
        Response::unauthorized('请先登录');
    }
    return $user;
}

function getPagination(): array
{
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 20)));
    return [
        'page'     => $page,
        'per_page' => $perPage,
        'offset'   => ($page - 1) * $perPage,
    ];
}

function getJsonInput(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return $_POST;
    }
    $data = json_decode($raw, true);
    if (is_array($data)) {
        return $data;
    }
    return $_POST;
}

function formatSoftwareRow(array $row): array
{
    if (isset($row['images']) && is_string($row['images'])) {
        $row['images'] = json_decode($row['images'], true) ?: [];
    }

    if (isset($row['tags']) && is_string($row['tags'])) {
        $row['tags'] = array_filter(array_map('trim', explode(',', $row['tags'])));
    }

    $row['formatted_size'] = function_exists('formatSize') ? formatSize((int)($row['size'] ?? 0)) : '';
    $row['url'] = '/software/' . $row['id'];

    if (!empty($row['icon'])) {
        $row['icon_url'] = $row['icon'];
    }
    if (!empty($row['cover'])) {
        $row['cover_url'] = $row['cover'];
    }

    return $row;
}

function getSoftwareListSql(array $filters = []): array
{
    $where = ['s.status = 1'];
    $params = [];

    if (!empty($filters['category_id'])) {
        $where[] = 's.category_id = ?';
        $params[] = (int)$filters['category_id'];
    }

    if (!empty($filters['keyword'])) {
        $where[] = '(s.name LIKE ? OR s.subtitle LIKE ? OR s.description LIKE ?)';
        $keyword = '%' . $filters['keyword'] . '%';
        $params[] = $keyword;
        $params[] = $keyword;
        $params[] = $keyword;
    }

    $sort = $filters['sort'] ?? 's.sort DESC, s.id DESC';
    $allowedSorts = [
        's.sort DESC, s.id DESC',
        's.sort ASC, s.id ASC',
        's.download_count DESC',
        's.view_count DESC',
        's.rating DESC',
        's.created_at DESC',
        's.price ASC',
        's.id DESC',
    ];
    if (!in_array($sort, $allowedSorts, true)) {
        $sort = 's.sort DESC, s.id DESC';
    }

    $whereStr = implode(' AND ', $where);

    $countSql = "SELECT COUNT(*) as total FROM software s WHERE {$whereStr}";
    $dataSql  = "SELECT s.*, c.name AS category_name
                 FROM software s
                 LEFT JOIN category c ON c.id = s.category_id
                 WHERE {$whereStr}
                 ORDER BY {$sort}";

    return [$countSql, $dataSql, $params];
}

function checkIsFavorite(int $userId, int $softwareId): bool
{
    $record = Database::fetch(
        "SELECT id FROM favorite WHERE user_id = ? AND software_id = ?",
        [$userId, $softwareId]
    );
    return !empty($record);
}

function getUserConfigValue(string $group, string $key, $default = null)
{
    $row = Database::fetch(
        "SELECT value, type FROM config WHERE group_name = ? AND key_name = ?",
        [$group, $key]
    );

    if (!$row) {
        return $default;
    }

    $value = $row['value'];
    $type = $row['type'] ?? 'string';

    switch ($type) {
        case 'bool':
        case 'boolean':
            return $value === '1' || $value === 'true' || $value === true;
        case 'number':
            return (float)$value;
        case 'json':
            return json_decode($value, true) ?: $default;
        case 'string':
        default:
            return $value;
    }
}
