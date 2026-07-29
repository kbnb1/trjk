<?php
/**
 * 轮播图管理页
 */
$pageTitle = '轮播图管理 - 软件库后台';
$currentPage = 'banner.php';
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
            <li class="menu-item active"><a href="banner.php"><i class="fas fa-image"></i><span>轮播图</span></a></li>
            <li class="menu-item"><a href="notice.php"><i class="fas fa-bullhorn"></i><span>公告管理</span></a></li>
            <li class="menu-item"><a href="toolbar.php"><i class="fas fa-tools"></i><span>工具栏</span></a></li>
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
                <span class="page-title">轮播图管理</span>
            </div>
            <div class="topbar-right">
                <button class="topbar-icon-btn"><i class="fas fa-bell"></i><span class="badge-dot"></span></button>
                <div class="topbar-user"><div class="user-avatar">A</div><div class="user-info"><div class="user-name">管理员</div><div class="user-role">超级管理员</div></div></div>
            </div>
        </nav>

        <div class="content-body fade-in">
            <div class="filter-bar">
                <button class="btn btn-success" id="btnAdd"><i class="fas fa-plus me-1"></i>添加轮播图</button>
            </div>

            <div class="card table-card">
                <div class="card-body">
                    <table class="table data-table">
                        <thead>
                            <tr>
                                <th width="60">ID</th>
                                <th>标题</th>
                                <th>图片</th>
                                <th class="hide-mobile">跳转链接</th>
                                <th>排序</th>
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
                <h5 class="modal-title" id="modalTitle">添加轮播图</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label class="form-label">标题 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" id="editTitle" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">图片 <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="upload-box" id="imgUpload" style="width:160px;height:80px;"><i class="fas fa-plus upload-icon"></i></div>
                            <input type="hidden" name="image" id="editImage">
                            <small class="text-muted">建议尺寸 750x340</small>
                        </div>
                        <input type="text" class="form-control" name="image_url" id="editImageUrl" placeholder="或输入图片地址">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">跳转链接</label>
                        <input type="text" class="form-control" name="link" id="editLink" placeholder="/software/detail?id=1">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">排序</label>
                            <input type="number" class="form-control" name="sort" id="editSort" value="0">
                        </div>
                        <div class="col-6 mb-3">
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
        AdminApp.Api.get('banner/list').then(function (res) {
            AdminApp.UI.hideLoading();
            if (res.code !== 200) { AdminApp.UI.toast(res.message, 'error'); return; }
            cache = res.data || [];
            renderList(cache);
        }).fail(function (err) { AdminApp.UI.hideLoading(); AdminApp.UI.toast(err.message, 'error'); });
    }

    function renderList(list) {
        var $list = $('#dataList');
        if (list.length === 0) {
            $list.html('<tr><td colspan="8"><div class="empty-state"><i class="fas fa-image"></i><p>暂无轮播图</p></div></td></tr>');
            return;
        }
        var html = '';
        list.forEach(function (item) {
            var img = item.image ? '<img class="thumb" style="width:80px;height:40px;" src="' + AdminApp.UI.escape(item.image) + '">' : '<span class="text-muted">无图</span>';
            html += '<tr>' +
                '<td>' + item.id + '</td>' +
                '<td class="fw-medium">' + AdminApp.UI.escape(item.title) + '</td>' +
                '<td>' + img + '</td>' +
                '<td class="hide-mobile"><small>' + AdminApp.UI.escape(item.link || '-') + '</small></td>' +
                '<td>' + (item.sort || 0) + '</td>' +
                '<td>' + AdminApp.UI.statusTag(item.status) + '</td>' +
                '<td class="hide-mobile">' + AdminApp.UI.formatDate(item.create_time) + '</td>' +
                '<td><button class="btn-action edit" data-id="' + item.id + '"><i class="fas fa-edit"></i></button>' +
                '<button class="btn-action delete btn-delete" data-id="' + item.id + '" data-name="' + AdminApp.UI.escape(item.title) + '" data-action="banner/delete"><i class="fas fa-trash"></i></button></td>' +
                '</tr>';
        });
        $list.html(html);
    }

    $('#btnAdd').on('click', function () {
        $('#modalTitle').text('添加轮播图');
        $('#editForm')[0].reset();
        $('#editId').val(''); $('#editImage').val('');
        $('#imgUpload').html('<i class="fas fa-plus upload-icon"></i>');
        $('#editStatus').val(1); $('#editSort').val(0);
        modal.show();
    });

    $(document).on('click', '.btn-action.edit', function () {
        var id = $(this).data('id');
        var item = cache.find(function (c) { return parseInt(c.id, 10) === parseInt(id, 10); });
        if (!item) return;
        $('#modalTitle').text('编辑轮播图');
        $('#editId').val(item.id);
        $('#editTitle').val(item.title);
        $('#editImage').val(item.image);
        $('#editImageUrl').val(item.image);
        $('#editLink').val(item.link);
        $('#editSort').val(item.sort);
        $('#editStatus').val(item.status);
        if (item.image) $('#imgUpload').html('<img src="' + AdminApp.UI.escape(item.image) + '">'); else $('#imgUpload').html('<i class="fas fa-plus upload-icon"></i>');
        modal.show();
    });

    $('#imgUpload').on('click', function () {
        var $input = $('<input type="file" accept="image/*" style="display:none">');
        $('body').append($input);
        $input.on('change', function () {
            var file = this.files[0]; if (!file) return;
            AdminApp.UI.showLoading();
            AdminApp.Api.upload(file, 'image').then(function (res) {
                AdminApp.UI.hideLoading();
                if (res.code === 200) {
                    $('#editImage').val(res.data.url); $('#editImageUrl').val(res.data.url);
                    $('#imgUpload').html('<img src="' + AdminApp.UI.escape(res.data.url) + '">');
                    AdminApp.UI.toast('上传成功', 'success');
                } else { AdminApp.UI.toast(res.message, 'error'); }
            }).fail(function (err) { AdminApp.UI.hideLoading(); AdminApp.UI.toast(err.message, 'error'); });
            $input.remove();
        });
        $input.click();
    });

    // 图片地址输入框联动
    $('#editImageUrl').on('input', function () { $('#editImage').val($(this).val()); });

    $('#btnSave').on('click', function () {
        var id = $('#editId').val();
        var data = {
            title: $('#editTitle').val(),
            image: $('#editImage').val(),
            link: $('#editLink').val(),
            sort: $('#editSort').val(),
            status: $('#editStatus').val()
        };
        if (!data.title) { AdminApp.UI.toast('请输入标题', 'warning'); return; }
        if (!data.image) { AdminApp.UI.toast('请上传或输入图片地址', 'warning'); return; }
        var action = id ? 'banner/update' : 'banner/create';
        if (id) data.id = id;
        var $btn = $(this); $btn.prop('disabled', true);
        AdminApp.Api.post(action, data).then(function (res) {
            $btn.prop('disabled', false);
            if (res.code === 200) { AdminApp.UI.toast(id ? '更新成功' : '添加成功', 'success'); modal.hide(); loadList(); }
            else { AdminApp.UI.toast(res.message, 'error'); }
        }).fail(function (err) { $btn.prop('disabled', false); AdminApp.UI.toast(err.message, 'error'); });
    });

    AdminApp.bindDelete('banner/delete', loadList);
    loadList();
});
</script>
</body>
</html>
