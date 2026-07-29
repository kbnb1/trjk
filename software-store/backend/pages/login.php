<?php
/**
 * 后台登录页
 * Bootstrap 风格，AJAX 提交登录
 */
require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../include/functions.php';

// 已登录直接跳转
require_once __DIR__ . '/../include/Auth.php';
if (Auth::getAdminId()) {
    header('Location: dashboard.php');
    exit;
}
$pageTitle = '登录 - 软件库后台';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-logo">
                <div class="logo-icon"><i class="fas fa-cube"></i></div>
                <h1>软件库后台管理</h1>
                <p>Software Store Admin System</p>
            </div>
            <form class="login-form" id="loginForm">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" name="username" id="username" placeholder="请输入用户名" autocomplete="username" value="admin">
                    </div>
                </div>
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" name="password" id="password" placeholder="请输入密码" autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button" id="togglePwd" tabindex="-1"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" checked>
                    <label class="form-check-label" for="remember">记住登录状态</label>
                </div>
                <button type="submit" class="btn btn-primary-gradient w-100" id="loginBtn">
                    <span class="btn-text">登 录</span>
                </button>
            </form>
            <div class="login-tip">
                <p>默认账号：<code>admin</code> / 密码：<code>admin123</code></p>
                <p><a href="preview-init.php">快速预览（免登录）</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
    $(function () {
        // 密码显示切换
        $('#togglePwd').on('click', function () {
            var $pwd = $('#password');
            var $icon = $(this).find('i');
            if ($pwd.attr('type') === 'password') {
                $pwd.attr('type', 'text');
                $icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                $pwd.attr('type', 'password');
                $icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // 登录提交
        $('#loginForm').on('submit', function (e) {
            e.preventDefault();
            var username = $.trim($('#username').val());
            var password = $('#password').val();
            if (!username || !password) {
                AdminApp.UI.toast('请输入用户名和密码', 'warning');
                return;
            }
            var $btn = $('#loginBtn');
            $btn.prop('disabled', true).find('.btn-text').html('<span class="loading-spinner"></span> 登录中...');

            AdminApp.Api.post('login', { username: username, password: password }).then(function (res) {
                if (res.code === 200 && res.data && res.data.token) {
                    AdminApp.Auth.setToken(res.data.token);
                    AdminApp.Auth.setAdmin(res.data);
                    AdminApp.UI.toast('登录成功，正在跳转...', 'success');
                    setTimeout(function () {
                        location.href = 'dashboard.php';
                    }, 600);
                } else {
                    AdminApp.UI.toast(res.message || '登录失败', 'error');
                    $btn.prop('disabled', false).find('.btn-text').text('登 录');
                }
            }).fail(function (err) {
                AdminApp.UI.toast(err.message || '登录失败', 'error');
                $btn.prop('disabled', false).find('.btn-text').text('登 录');
            });
        });
    });
    </script>
</body>
</html>
