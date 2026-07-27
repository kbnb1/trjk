<?php
$page_title = '登录';
require_once __DIR__ . '/../include/header.php';
?>

<div class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <div class="logo-icon-lg">
                <i class="fas fa-store"></i>
            </div>
            <h1>软件商店</h1>
            <p>后台管理系统</p>
        </div>

        <div id="loginError" class="form-group" style="display:none; margin-bottom: 18px;">
            <div class="alert alert-danger" style="background-color:#FDEDEC; color:#C0392B; padding:10px 14px; border-radius:8px; font-size:0.88rem; border:1px solid #F5B7B1;"></div>
        </div>

        <form id="loginForm" autocomplete="off">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="请输入用户名" required autocomplete="username">
                <div class="form-error"></div>
            </div>

            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="请输入密码" required autocomplete="current-password">
                <div class="form-error"></div>
            </div>

            <div class="form-group" style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 20px;">
                <div class="form-check" style="margin-bottom:0;">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">记住我</label>
                </div>
                <a href="#" id="forgotLink" style="font-size:0.85rem;">忘记密码？</a>
            </div>

            <button type="submit" class="btn btn-primary btn-block" id="loginBtn">
                <span class="btn-text">登 录</span>
            </button>
        </form>

        <div class="login-footer">
            &copy; <?= date('Y') ?> 软件商店. All rights reserved.
        </div>
    </div>
</div>

<script>
(function () {
    var form = document.getElementById('loginForm');
    var errorBox = document.getElementById('loginError');
    var errorMsg = errorBox.querySelector('.alert');
    var loginBtn = document.getElementById('loginBtn');
    var btnText = loginBtn.querySelector('.btn-text');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        errorBox.style.display = 'none';

        var username = document.getElementById('username').value.trim();
        var password = document.getElementById('password').value;
        var remember = document.getElementById('remember').checked;

        if (!username || !password) {
            showError('请输入用户名和密码');
            return;
        }

        loginBtn.disabled = true;
        loginBtn.classList.add('loading-btn');
        btnText.textContent = '登录中...';

        try {
            var result = await Admin.api('POST', '/api/admin/login', {
                username: username,
                password: password,
                remember: remember
            });

            if (result.code === 0 || result.success) {
                Admin.saveToken(result.data.token || result.token, remember);
                Admin.showToast('登录成功，正在跳转...', 'success');
                setTimeout(function () {
                    window.location.href = 'dashboard.php';
                }, 800);
            } else {
                showError(result.message || '登录失败');
                resetBtn();
            }
        } catch (err) {
            showError(err.message || '登录失败，请检查网络连接');
            resetBtn();
        }
    });

    function showError(msg) {
        errorMsg.textContent = msg;
        errorBox.style.display = 'block';
    }

    function resetBtn() {
        loginBtn.disabled = false;
        loginBtn.classList.remove('loading-btn');
        btnText.textContent = '登 录';
    }
})();
</script>

<?php require_once __DIR__ . '/../include/footer.php'; ?>