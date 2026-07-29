<?php
/**
 * 用户管理页
 */
$pageTitle = '用户管理 - 软件库后台';
$currentPage = 'user.php';
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
            <li class="menu-item active"><a href="user.php"><i class="fas fa-users"></i><span>用户管理</span></a></li>
            <li class="menu-section">系统</li>
            <li class="menu-item"><a href="setting.php"><i class="fas fa-cog"></i><span>系统设置</span></a></li>
            <li class="menu-item"><a href="javascript:;" class="btn-logout"><i class="fas fa-sign-out-alt"></i><span>退出登录</span></a></li>
        </ul>
        <div class="sidebar-footer">软件库后台 v2.0.0</div>
    </aside>
    <div class="sidebar-overlay"></div>

    <div class="main-content">
        <nav class="topbar">
            <div class="topbar-left">
                <button class="topbar-toggle"><i class="fas fa-bars"></i></button>
                <span class="page-title">用户管理</span>
            </div>
            <div class="topbar-right">
                <button class="topbar-icon-btn"><i class="fas fa-bell"></i><span class="badge-dot"></span></button>
                <div class="topbar-user"><div class="user-avatar">A</div><div class="user-info"><div class="user-name">管理员</div><div class="user-role">超级管理员</div></div></div>
            </div>
        </nav>

        <div class="content-body fade-in">
            <div class="filter-bar">
                <input type="text" class="form-control search-input" id="searchKeyword" placeholder="搜索用户名/昵称/手机/邮箱">
                <select class="form-select filter-control" id="filterStatus">
                    <option value="">全部状态</option>
                    <option value="1">正常</option>
                    <option value="0">禁用</option>
                </select>
                <button class="btn btn-primary" id="btnSearch"><i class="fas fa-search me-1"></i>搜索</button>
            </div>

            <div class="card table-card">
                <div class="card-body">
                    <table class="table data-table">
                        <thead>
                            <tr>
                                <th width="60">ID</th>
                                <th>头像</th>
                                <th>用户名</th>
                                <th>昵称</th>
                                <th class="hide-mobile">手机号</th>
                                <th class="hide-mobile">邮箱</th>
                                <th>状态</th>
                                <th class="hide-mobile">注册时间</th>
                                <th class="hide-mobile">最后登录</th>
                                <th width="120">操作</th>
                            </tr>
                        </thead>
                        <tbody id="dataList"></tbody>
                    </table>
                </div>
                <div class="pagination-wrapper" id="pagination"></div>
            </div>
        </div>
    </div>
</div>

<!-- 编辑弹窗 -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">编辑用户</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label class="form-label">用户名</label>
                        <input type="text" class="form-control" id="editUsername" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">昵称</label>
                        <input type="text" class="form-control" name="nickname" id="editNickname">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">手机号</label>
                            <input type="text" class="form-control" name="phone" id="editPhone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">邮箱</label>
                            <input type="text" class="form-control" name="email" id="editEmail">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">头像地址</label>
                        <input type="text" class="form-control" name="avatar" id="editAvatar">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">重置密码（留空则不修改）</label>
                        <input type="password" class="form-control" name="password" id="editPassword" placeholder="输入新密码可重置">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">状态</label>
                        <select class="form-select" name="status" id="editStatus">
                            <option value="1">正常</option>
                            <option value="0">禁用</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="btnSave"><i class="fas fa-save me-1"></i>保存</button>
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
    var modal = new bootstrap.Modal(document.getElementById('editModal'));
    var query = { page: 1, size: 20 };

    function loadList() {
        AdminApp.UI.showLoading();
        var params = { page: query.page, size: query.size };
        if ($('#searchKeyword').val()) params.keyword = $('#searchKeyword').val();
        if ($('#filterStatus').val() !== '') params.status = $('#filterStatus').val();
        AdminApp.Api.get('user/list', params).then(function (res) {
            AdminApp.UI.hideLoading();
            if (res.code !== 200) { AdminApp.UI.toast(res.message, 'error'); return; }
            renderList(res.data.list || []);
            AdminApp.renderPagination('#pagination', res.data, function (p) { query.page = p; loadList(); });
        }).fail(function (err) { AdminApp.UI.hideLoading(); AdminApp.UI.toast(err.message, 'error'); });
    }

    function renderList(list) {
        var $list = $('#dataList');
        if (list.length === 0) {
            $list.html('<tr><td colspan="10"><div class="empty-state"><i class="fas fa-users"></i><p>暂无用户数据</p></div></td></tr>');
            return;
        }
        var html = '';
        list.forEach(function (item) {
            var avatar = item.avatar ? '<img class="thumb" style="border-radius:50%;" src="' + AdminApp.UI.escape(item.avatar) + '">' : AdminApp.UI.thumbPlaceholder(item.nickname || item.username);
            html += '<tr>' +
                '<td>' + item.id + '</td>' +
                '<td>' + avatar + '</td>' +
                '<td>' + AdminApp.UI.escape(item.username) + '</td>' +
                '<td>' + AdminApp.UI.escape(item.nickname || '-') + '</td>' +
                '<td class="hide-mobile">' + AdminApp.UI.escape(item.phone || '-') + '</td>' +
                '<td class="hide-mobile"><small>' + AdminApp.UI.escape(item.email || '-') + '</small></td>' +
                '<td>' + AdminApp.UI.statusTag(item.status, '正常', '禁用') + '</td>' +
                '<td class="hide-mobile">' + AdminApp.UI.formatDate(item.register_time) + '</td>' +
                '<td class="hide-mobile">' + AdminApp.UI.formatDate(item.last_login) + '</td>' +
                '<td><button class="btn-action edit" data-id="' + item.id + '"><i class="fas fa-edit"></i></button>' +
                '<button class="btn-action delete btn-delete" data-id="' + item.id + '" data-name="' + AdminApp.UI.escape(item.username) + '" data-action="user/delete"><i class="fas fa-trash"></i></button></td>' +
                '</tr>';
        });
        $list.html(html);
    }

    $(document).on('click', '.btn-action.edit', function () {
        var id = $(this).data('id');
        AdminApp.Api.get('user/detail', { id: id }).then(function (res) {
            if (res.code !== 200) { AdminApp.UI.toast(res.message, 'error'); return; }
            var d = res.data;
            $('#modalTitle').text('编辑用户 - ' + d.username);
            $('#editId').val(d.id);
            $('#editUsername').val(d.username);
            $('#editNickname').val(d.nickname);
            $('#editPhone').val(d.phone);
            $('#editEmail').val(d.email);
            $('#editAvatar').val(d.avatar);
            $('#editPassword').val('');
            $('#editStatus').val(d.status);
            modal.show();
        });
    });

    $('#btnSave').on('click', function () {
        var id = $('#editId').val();
        var data = {
            id: id,
            nickname: $('#editNickname').val(),
            phone: $('#editPhone').val(),
            email: $('#editEmail').val(),
            avatar: $('#editAvatar').val(),
            status: $('#editStatus').val()
        };
        if ($('#editPassword').val()) data.password = $('#editPassword').val();
        var $btn = $(this); $btn.prop('disabled', true);
        AdminApp.Api.post('user/update', data).then(function (res) {
            $btn.prop('disabled', false);
            if (res.code === 200) { AdminApp.UI.toast('更新成功', 'success'); modal.hide(); loadList(); }
            else { AdminApp.UI.toast(res.message, 'error'); }
        }).fail(function (err) { $btn.prop('disabled', false); AdminApp.UI.toast(err.message, 'error'); });
    });

    $('#btnSearch').on('click', function () { query.page = 1; loadList(); });
    $('#searchKeyword').on('keypress', function (e) { if (e.which === 13) { query.page = 1; loadList(); } });
    $('#filterStatus').on('change', function () { query.page = 1; loadList(); });
    AdminApp.bindDelete('user/delete', loadList);
    loadList();
});
</script>
</body>
</html>
