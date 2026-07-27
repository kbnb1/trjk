<?php

namespace App;

$route = $GLOBALS['_route'];
$method = $route['method'];
$action = $route['action'];

if ($method === 'GET' && $action === null) {
    $softwareCount = (int)Database::fetch("SELECT COUNT(*) as cnt FROM software")['cnt'];
    $userCount = (int)Database::fetch("SELECT COUNT(*) as cnt FROM user")['cnt'];
    $downloadCount = (int)Database::fetch("SELECT COUNT(*) as cnt FROM download_record")['cnt'];

    $todayStart = date('Y-m-d 00:00:00');
    $todayEnd = date('Y-m-d 23:59:59');

    $todayActive = (int)Database::fetch(
        "SELECT COUNT(DISTINCT user_id) as cnt FROM download_record WHERE created_at BETWEEN ? AND ?",
        [$todayStart, $todayEnd]
    )['cnt'];

    $todayDownloads = (int)Database::fetch(
        "SELECT COUNT(*) as cnt FROM download_record WHERE created_at BETWEEN ? AND ?",
        [$todayStart, $todayEnd]
    )['cnt'];

    $softwareStatus = Database::fetch(
        "SELECT
            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as published,
            SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as unpublished
         FROM software"
    );

    $userStatus = Database::fetch(
        "SELECT
            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as disabled
         FROM user"
    );

    $adStatus = Database::fetch(
        "SELECT
            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as inactive
         FROM advertisement"
    );

    Response::success([
        'counts' => [
            'software'   => $softwareCount,
            'users'      => $userCount,
            'downloads'  => $downloadCount,
            'today_active' => $todayActive,
        ],
        'today' => [
            'downloads' => $todayDownloads,
            'active'    => $todayActive,
        ],
        'software_status' => [
            'published'   => (int)($softwareStatus['published'] ?? 0),
            'unpublished' => (int)($softwareStatus['unpublished'] ?? 0),
        ],
        'user_status' => [
            'active'   => (int)($userStatus['active'] ?? 0),
            'disabled' => (int)($userStatus['disabled'] ?? 0),
        ],
        'ad_status' => [
            'active'   => (int)($adStatus['active'] ?? 0),
            'inactive' => (int)($adStatus['inactive'] ?? 0),
        ],
    ]);
}

if ($method === 'GET' && $action === 'trend') {
    $days = max(1, min(30, (int)($_GET['days'] ?? 7)));

    $trend = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $dayStart = "{$date} 00:00:00";
        $dayEnd = "{$date} 23:59:59";

        $downloads = (int)Database::fetch(
            "SELECT COUNT(*) as cnt FROM download_record WHERE created_at BETWEEN ? AND ?",
            [$dayStart, $dayEnd]
        )['cnt'];

        $newUsers = (int)Database::fetch(
            "SELECT COUNT(*) as cnt FROM user WHERE created_at BETWEEN ? AND ?",
            [$dayStart, $dayEnd]
        )['cnt'];

        $newSoftware = (int)Database::fetch(
            "SELECT COUNT(*) as cnt FROM software WHERE created_at BETWEEN ? AND ?",
            [$dayStart, $dayEnd]
        )['cnt'];

        $trend[] = [
            'date'        => $date,
            'downloads'   => $downloads,
            'new_users'   => $newUsers,
            'new_software' => $newSoftware,
        ];
    }

    $totalDownloads = (int)Database::fetch(
        "SELECT COUNT(*) as cnt FROM download_record WHERE created_at >= ?",
        [date('Y-m-d 00:00:00', strtotime("-{$days} days"))]
    )['cnt'];

    Response::success([
        'days'           => $days,
        'total_downloads' => $totalDownloads,
        'trend'          => $trend,
    ]);
}

Response::error('请求方式不允许', Response::CODE_BAD_REQUEST);