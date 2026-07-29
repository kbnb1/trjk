<?php
/**
 * 工具栏管理页
 */
$pageTitle = '工具栏管理 - 软件库后台';
$currentPage = 'toolbar.php';
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
            <li class="menu-item active"><a href="toolbar.php"><i class="fas fa-tools"></i><span>工具栏</span></a></li>
            <li class="menu-item"><a href="advertisement.php"><i class="fas fa-ad"></i><span>广告管理</span></a></li>
            <li class="menu-section">用户</li>
            <li class="menu-item"><a href="user.php"><i class="fas fa-users"></i><span>用户管理</span></a></li>
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
                <span class="page-title">工具栏管理</span>
            </div>
            <div class="topbar-right">
                <button class="topbar-icon-btn"><i class="fas fa-bell"></i><span class="badge-dot"></span></button>
                <div class="topbar-user"><div class="user-avatar">A</div><div class="user-info"><div class="user-name">管理员</div><div class="user-role">超级管理员</div></div></div>
            </div>
        </nav>

        <div class="content-body fade-in">
            <div class="filter-bar">
                <button class="btn btn-success" id="btnAdd"><i class="fas fa-plus me-1"></i>添加工具栏项</button>
            </div>

            <div class="card table-card">
                <div class="card-body">
                    <table class="table data-table">
                        <thead>
                            <tr>
                                <th width="60">ID</th>
                                <th>图标</th>
                                <th>名称</th>
                                <th class="hide-mobile">跳转链接</th>
                                <th>排序</th>
                                <th>是否显示</th>
                                <th>状态</th>
                                <th class="hide-mobile">创建时间</th>
                                <th width="120">操作</th>
                            </tr>
                        </thead>
                        <tbody id="dataList"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">添加工具栏项</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label class="form-label">名称 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="editName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">图标类名（Font Awesome）</label>
                        <input type="text" class="form-control" name="icon" id="editIcon" placeholder="如 fas fa-home">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">跳转链接</label>
                        <input type="text" class="form-control" name="link" id="editLink" placeholder="/home">
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">排序</label>
                            <input type="number" class="form-control" name="sort" id="editSort" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">是否显示</label>
                            <select class="form-select" name="is_show" id="editIsShow">
                                <option value="1">显示</option>
                                <option value="0">隐藏</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">状态</label>
                            <select class="form-select" name="status" id="editStatus">
                                <option value="1">启用</option>
                                <option value="0">禁用</option>
                            </select>
                        </div>
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
    var cache = [];

    function loadList() {
        AdminApp.UI.showLoading();
        AdminApp.Api.get('toolbar/list').then(function (res) {
            AdminApp.UI.hideLoading();
            if (res.code !== 200) { AdminApp.UI.toast(res.message, 'error'); return; }
            cache = res.data || [];
            renderList(cache);
        }).fail(function (err) { AdminApp.UI.hideLoading(); AdminApp.UI.toast(err.message, 'error'); });
    }

    function renderList(list) {
        var $list = $('#dataList');
        if (list.length === 0) {
            $list.html('<tr><td colspan="9"><div class="empty-state"><i class="fas fa-tools"></i><p>暂无工具栏数据</p></div></td></tr>');
            return;
        }
        var html = '';
        list.forEach(function (item) {
            var icon = item.icon ? '<i class="' + AdminApp.UI.escape(item.icon) + '" style="font-size:20px;color:#3b82f6;"></i>' : '<span class="text-muted">-</span>';
            html += '<tr>' +
                '<td>' + item.id + '</td>' +
                '<td>' + icon + '</td>' +
                '<td class="fw-medium">' + AdminApp.UI.escape(item.name) + '</td>' +
                '<td class="hide-mobile"><small>' + AdminApp.UI.escape(item.link || '-') + '</small></td>' +
                '<td>' + (item.sort || 0) + '</td>' +
                '<td>' + AdminApp.UI.statusTag(item.is_show, '显示', '隐藏') + '</td>' +
                '<td>' + AdminApp.UI.statusTag(item.status) + '</td>' +
                '<td class="hide-mobile">' + AdminApp.UI.formatDate(item.create_time) + '</td>' +
                '<td><button class="btn-action edit" data-id="' + item.id + '"><i class="fas fa-edit"></i></button>' +
                '<button class="btn-action delete btn-delete" data-id="' + item.id + '" data-name="' + AdminApp.UI.escape(item.name) + '" data-action="toolbar/delete"><i class="fas fa-trash"></i></button></td>' +
                '</tr>';
        });
        $list.html(html);
    }

    $('#btnAdd').on('click', function () {
        $('#modalTitle').text('添加工具栏项');
        $('#editForm')[0].reset();
        $('#editId').val('');
        $('#editIsShow').val(1); $('#editStatus').val(1); $('#editSort').val(0);
        modal.show();
    });

    $(document).on('click', '.btn-action.edit', function () {
        var id = $(this).data('id');
        var item = cache.find(function (c) { return parseInt(c.id, 10) === parseInt(id, 10); });
        if (!item) return;
        $('#modalTitle').text('编辑工具栏项');
        $('#editId').val(item.id);
        $('#editName').val(item.name);
        $('#editIcon').val(item.icon);
        $('#editLink').val(item.link);
        $('#editSort').val(item.sort);
        $('#editIsShow').val(item.is_show);
        $('#editStatus').val(item.status);
        modal.show();
    });

    $('#btnSave').on('click', function () {
        var id = $('#editId').val();
        var data = {
            name: $('#editName').val(),
            icon: $('#editIcon').val(),
            link: $('#editLink').val(),
            sort: $('#editSort').val(),
            is_show: $('#editIsShow').val(),
            status: $('#editStatus').val()
        };
        if (!data.name) { AdminApp.UI.toast('请输入名称', 'warning'); return; }
        var action = id ? 'toolbar/update' : 'toolbar/create';
        if (id) data.id = id;
        var $btn = $(this); $btn.prop('disabled', true);
        AdminApp.Api.post(action, data).then(function (res) {
            $btn.prop('disabled', false);
            if (res.code === 200) { AdminApp.UI.toast(id ? '更新成功' : '添加成功', 'success'); modal.hide(); loadList(); }
            else { AdminApp.UI.toast(res.message, 'error'); }
        }).fail(function (err) { $btn.prop('disabled', false); AdminApp.UI.toast(err.message, 'error'); });
    });

    AdminApp.bindDelete('toolbar/delete', loadList);
    loadList();
});
</script>
</body>
</html>
