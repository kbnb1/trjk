<?php
session_start();

$adminToken = $_SESSION['admin_token'] ?? '';
$config = require __DIR__ . '/include/config.php';
$apiBaseUrl = $config['api']['base_url'] ?? '/api';

if (!empty($adminToken)) {
    $ch = curl_init($apiBaseUrl . '/admin/login/info');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $adminToken]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['code']) && $data['code'] === 200) {
            header('Location: pages/index.php');
            exit;
        }
    }
}

header('Location: pages/login.php');
exit;
