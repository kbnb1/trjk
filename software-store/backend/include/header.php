<?php
session_start();

$current_page = basename($_SERVER['PHP_SELF'], '.php');

$public_pages = ['login'];

if (!in_array($current_page, $public_pages)) {
    if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
        $current_script = basename($_SERVER['PHP_SELF']);
        if ($current_script !== 'login.php') {
            header('Location: login.php');
            exit;
        }
    }
}

$admin_username = $_SESSION['admin_username'] ?? '管理员';
$admin_avatar = $_SESSION['admin_avatar'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="软件商店后台管理系统">
    <title><?= $page_title ?? '后台管理' ?> - 软件商店</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<div class="layout">
    <?php if (!in_array($current_page, $public_pages)): ?>
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="菜单">
        <i class="fas fa-bars"></i>
    </button>
    <?php endif; ?>