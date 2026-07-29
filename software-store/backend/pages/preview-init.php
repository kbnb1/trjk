<?php
/**
 * 预览初始化页
 * 绕过登录，直接为 admin 账号生成 token 并写入 localStorage，跳转仪表盘
 * 仅用于快速预览，生产环境应删除或禁用本文件
 */
require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../include/functions.php';
require_once __DIR__ . '/../include/Database.php';
require_once __DIR__ . '/../include/Auth.php';
require_once __DIR__ . '/../include/Response.php';

// 直接查询 admin 账号信息并生成 token
try {
    $db = Database::getInstance();
    $admin = $db->fetch('SELECT id, username, name, avatar, role FROM admin ORDER BY id ASC LIMIT 1');
    if (!$admin) {
        die('未找到管理员账号，请先导入 database.sql 初始化数据。');
    }
    // 更新最后登录时间
    $db->update('admin', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$admin['id']]);
    $token = Auth::generateToken($admin['id'], 'admin');
    $adminInfo = [
        'admin_id' => (int) $admin['id'],
        'username' => $admin['username'],
        'name'     => $admin['name'],
        'avatar'   => $admin['avatar'],
        'role'     => $admin['role'],
        'token'    => $token,
    ];
} catch (Exception $e) {
    die('数据库连接失败：' . $e->getMessage() . '<br>请检查 include/config.php 数据库配置，并确保已导入 database.sql。');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>预览初始化 - 软件库后台</title>
    <style>
        body { font-family: "Microsoft YaHei", sans-serif; background:#f1f5f9; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
        .box { background:#fff; padding:40px 50px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,.08); text-align:center; max-width:420px; }
        .icon { width:64px; height:64px; background:linear-gradient(135deg,#3b82f6,#8b5cf6); border-radius:16px; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:28px; margin-bottom:18px; }
        h2 { color:#1e293b; margin:0 0 10px; font-size:20px; }
        p { color:#64748b; margin:0 0 20px; font-size:14px; line-height:1.7; }
        .info { background:#f8fafc; border-radius:8px; padding:14px; text-align:left; font-size:13px; color:#475569; margin-bottom:20px; }
        .info b { color:#1e293b; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">⚡</div>
        <h2>正在进入预览模式</h2>
        <p>已绕过登录，使用管理员身份直接访问后台。</p>
        <div class="info">
            <b>管理员账号：</b><?= htmlspecialchars($adminInfo['username']) ?><br>
            <b>姓名：</b><?= htmlspecialchars($adminInfo['name']) ?><br>
            <b>角色：</b><?= $adminInfo['role'] === 'super' ? '超级管理员' : '管理员' ?><br>
            <b>Token：</b>已生成（有效期 7 天）
        </div>
        <p style="font-size:12px;color:#94a3b8;">即将自动跳转到仪表盘...</p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
    (function () {
        // 写入 token 与管理员信息后跳转
        localStorage.setItem('admin_token', '<?= $adminInfo['token'] ?>');
        localStorage.setItem('admin_info', '<?= json_encode($adminInfo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>');
        setTimeout(function () {
            location.href = 'dashboard.php';
        }, 1200);
    })();
    </script>
</body>
</html>
