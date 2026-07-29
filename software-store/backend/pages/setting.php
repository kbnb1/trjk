<?php
/**
 * 系统设置页
 * 站点设置、验证开关、联系信息等
 */
$pageTitle = '系统设置 - 软件库后台';
$currentPage = 'setting.php';
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
<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><span class="brand-icon"><i class="fas fa-cube"></i></span>软件库后台</div>
        <ul class="sidebar-menu">
            <li class="menu-section">主菜单</li>
            <li class="menu-item"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>仪表盘</span></a></li>
            <li class="menu-item"><a href="software.php"><i class="fas fa-box"></i><span>软件管理</span></a></li>
            <li class="menu-item"><a href="category.php"><i class="fas fa-th-large"></i><span>分类管理</span></a></li>
            <li class="menu-section">运营</li>
            <li class="menu-item"><a href="banner.php"><i class="fas fa-image"></i><span>轮播图</span></a></li>
            <li class="menu-item"><a href="notice.php"><i class="fas fa-bullhorn"></i><span>公告管理</span></a></li>
            <li class="menu-item"><a href="toolbar.php"><i class="fas fa-tools"></i><span>工具栏</span></a></li>
            <li class="menu-item"><a href="advertisement.php"><i class="fas fa-ad"></i><span>广告管理</span></a></li>
            <li class="menu-section">用户</li>
            <li class="menu-item"><a href="user.php"><i class="fas fa-users"></i><span>用户管理</span></a></li>
            <li class="menu-section">系统</li>
            <li class="menu-item active"><a href="setting.php"><i class="fas fa-cog"></i><span>系统设置</span></a></li>
            <li class="menu-item"><a href="javascript:;" class="btn-logout"><i class="fas fa-sign-out-alt"></i><span>退出登录</span></a></li>
        </ul>
        <div class="sidebar-footer">软件库后台 v2.0.0</div>
    </aside>
    <div class="sidebar-overlay"></div>

    <div class="main-content">
        <nav class="topbar">
            <div class="topbar-left">
                <button class="topbar-toggle"><i class="fas fa-bars"></i></button>
                <span class="page-title">系统设置</span>
            </div>
            <div class="topbar-right">
                <button class="topbar-icon-btn"><i class="fas fa-bell"></i><span class="badge-dot"></span></button>
                <div class="topbar-user"><div class="user-avatar">A</div><div class="user-info"><div class="user-name">管理员</div><div class="user-role">超级管理员</div></div></div>
            </div>
        </nav>

        <div class="content-body fade-in">
            <div class="row g-3">
                <!-- 站点设置 -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h5><i class="fas fa-globe text-primary-custom"></i> 站点设置</h5></div>
                        <div class="card-body">
                            <form id="siteForm">
                                <div class="mb-3">
                                    <label class="form-label">站点名称</label>
                                    <input type="text" class="form-control" data-key="site_name">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">站点描述</label>
                                    <input type="text" class="form-control" data-key="site_description">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">站点Logo地址</label>
                                    <input type="text" class="form-control" data-key="site_logo" placeholder="https://...">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">App当前版本</label>
                                    <input type="text" class="form-control" data-key="app_version">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">备案信息</label>
                                    <input type="text" class="form-control" data-key="icp">
                                </div>
                                <button type="button" class="btn btn-primary" id="btnSaveSite"><i class="fas fa-save me-1"></i>保存站点设置</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 注册与验证 -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h5><i class="fas fa-shield-alt text-primary-custom"></i> 注册与验证</h5></div>
                        <div class="card-body">
                            <form id="verifyForm">
                                <div class="mb-3">
                                    <label class="form-label">是否允许注册</label>
                                    <select class="form-select" data-key="register_switch">
                                        <option value="1">允许</option>
                                        <option value="0">关闭</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">手机号验证</label>
                                    <select class="form-select" data-key="phone_verify">
                                        <option value="1">开启</option>
                                        <option value="0">关闭</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">邮箱验证</label>
                                    <select class="form-select" data-key="email_verify">
                                        <option value="1">开启</option>
                                        <option value="0">关闭</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary" id="btnSaveVerify"><i class="fas fa-save me-1"></i>保存验证设置</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 联系方式 -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h5><i class="fas fa-address-book text-primary-custom"></i> 联系方式</h5></div>
                        <div class="card-body">
                            <form id="contactForm">
                                <div class="mb-3">
                                    <label class="form-label">联系邮箱</label>
                                    <input type="email" class="form-control" data-key="contact_email">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">联系电话</label>
                                    <input type="text" class="form-control" data-key="contact_phone">
                                </div>
                                <button type="button" class="btn btn-primary" id="btnSaveContact"><i class="fas fa-save me-1"></i>保存联系方式</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 上传设置 -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h5><i class="fas fa-upload text-primary-custom"></i> 上传设置</h5></div>
                        <div class="card-body">
                            <form id="uploadForm">
                                <div class="mb-3">
                                    <label class="form-label">上传目录</label>
                                    <input type="text" class="form-control" data-key="upload_path">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">最大上传大小（字节）</label>
                                    <input type="number" class="form-control" data-key="upload_max_size">
                                    <small class="text-muted">100MB = 104857600 字节</small>
                                </div>
                                <button type="button" class="btn btn-primary" id="btnSaveUpload"><i class="fas fa-save me-1"></i>保存上传设置</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 修改密码 -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h5><i class="fas fa-key text-primary-custom"></i> 修改管理员密码</h5></div>
                        <div class="card-body">
                            <form id="pwdForm">
                                <div class="mb-3">
                                    <label class="form-label">原密码</label>
                                    <input type="password" class="form-control" id="oldPassword" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">新密码</label>
                                    <input type="password" class="form-control" id="newPassword" required>
                                    <small class="text-muted">至少 6 位</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">确认新密码</label>
                                    <input type="password" class="form-control" id="confirmPassword" required>
                                </div>
                                <button type="button" class="btn btn-primary" id="btnSavePwd"><i class="fas fa-save me-1"></i>修改密码</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/admin.js"></script>
<script>
$(function () {
    if (!AdminApp.requireAuth()) return;
    var configMap = {};

    // 加载配置
    function loadConfig() {
        AdminApp.UI.showLoading();
        AdminApp.Api.get('config/get').then(function (res) {
            AdminApp.UI.hideLoading();
            if (res.code !== 200) { AdminApp.UI.toast(res.message, 'error'); return; }
            configMap = res.data.config || {};
            // 填充表单
            $('[data-key]').each(function () {
                var key = $(this).data('key');
                var val = configMap[key];
                if (val !== undefined && val !== null) {
                    $(this).val(val);
                }
            });
        }).fail(function (err) { AdminApp.UI.hideLoading(); AdminApp.UI.toast(err.message, 'error'); });
    }

    // 通用保存函数
    function saveConfig(keys, btn) {
        var data = {};
        keys.forEach(function (key) {
            var $el = $('[data-key="' + key + '"]');
            if ($el.length) data[key] = $el.val();
        });
        var $btn = $(btn); $btn.prop('disabled', true);
        AdminApp.Api.post('config/save', { data: data }).then(function (res) {
            $btn.prop('disabled', false);
            if (res.code === 200) {
                AdminApp.UI.toast('保存成功', 'success');
                // 更新缓存
                Object.keys(data).forEach(function (k) { configMap[k] = data[k]; });
            } else { AdminApp.UI.toast(res.message, 'error'); }
        }).fail(function (err) { $btn.prop('disabled', false); AdminApp.UI.toast(err.message, 'error'); });
    }

    $('#btnSaveSite').on('click', function () {
        saveConfig(['site_name', 'site_description', 'site_logo', 'app_version', 'icp'], this);
    });
    $('#btnSaveVerify').on('click', function () {
        saveConfig(['register_switch', 'phone_verify', 'email_verify'], this);
    });
    $('#btnSaveContact').on('click', function () {
        saveConfig(['contact_email', 'contact_phone'], this);
    });
    $('#btnSaveUpload').on('click', function () {
        saveConfig(['upload_path', 'upload_max_size'], this);
    });

    // 修改密码
    $('#btnSavePwd').on('click', function () {
        var oldPwd = $('#oldPassword').val();
        var newPwd = $('#newPassword').val();
        var confirmPwd = $('#confirmPassword').val();
        if (!oldPwd || !newPwd) { AdminApp.UI.toast('请填写原密码和新密码', 'warning'); return; }
        if (newPwd.length < 6) { AdminApp.UI.toast('新密码至少6位', 'warning'); return; }
        if (newPwd !== confirmPwd) { AdminApp.UI.toast('两次输入的新密码不一致', 'warning'); return; }
        var $btn = $(this); $btn.prop('disabled', true);
        AdminApp.Api.post('admin/password', { old_password: oldPwd, new_password: newPwd }).then(function (res) {
            $btn.prop('disabled', false);
            if (res.code === 200) {
                AdminApp.UI.toast('密码修改成功', 'success');
                $('#pwdForm')[0].reset();
            } else { AdminApp.UI.toast(res.message, 'error'); }
        }).fail(function (err) { $btn.prop('disabled', false); AdminApp.UI.toast(err.message, 'error'); });
    });

    loadConfig();
});
</script>
</body>
</html>
