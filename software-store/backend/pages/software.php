<?php
/**
 * 软件管理页
 * 列表 + 添加/编辑（弹窗）
 */
$pageTitle = '软件管理 - 软件库后台';
$currentPage = 'software.php';
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
            <li class="menu-item active"><a href="software.php"><i class="fas fa-box"></i><span>软件管理</span></a></li>
            <li class="menu-item"><a href="category.php"><i class="fas fa-th-large"></i><span>分类管理</span></a></li>
            <li class="menu-section">运营</li>
            <li class="menu-item"><a href="banner.php"><i class="fas fa-image"></i><span>轮播图</span></a></li>
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
                <span class="page-title">软件管理</span>
            </div>
            <div class="topbar-right">
                <button class="topbar-icon-btn"><i class="fas fa-bell"></i><span class="badge-dot"></span></button>
                <div class="topbar-user">
                    <div class="user-avatar">A</div>
                    <div class="user-info"><div class="user-name">管理员</div><div class="user-role">超级管理员</div></div>
                </div>
            </div>
        </nav>

        <div class="content-body fade-in">
            <!-- 筛选区 -->
            <div class="filter-bar">
                <input type="text" class="form-control search-input" id="searchKeyword" placeholder="搜索软件名称">
                <select class="form-select filter-control" id="filterCategory">
                    <option value="0">全部分类</option>
                </select>
                <select class="form-select filter-control" id="filterStatus">
                    <option value="">全部状态</option>
                    <option value="1">已上架</option>
                    <option value="0">已下架</option>
                </select>
                <button class="btn btn-primary" id="btnSearch"><i class="fas fa-search me-1"></i>搜索</button>
                <button class="btn btn-success" id="btnAdd"><i class="fas fa-plus me-1"></i>添加软件</button>
            </div>

            <!-- 列表 -->
            <div class="card table-card">
                <div class="card-body">
                    <table class="table data-table">
                        <thead>
                            <tr>
                                <th width="60">ID</th>
                                <th>软件名称</th>
                                <th class="hide-mobile">分类</th>
                                <th class="hide-mobile">版本</th>
                                <th class="hide-mobile">大小</th>
                                <th>下载量</th>
                                <th>排序</th>
                                <th>状态</th>
                                <th class="hide-mobile">标识</th>
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

<!-- 添加/编辑弹窗 -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">添加软件</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" name="id" id="editId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">软件名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="editName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">所属分类 <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" id="editCategory" required></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">版本号</label>
                            <input type="text" class="form-control" name="version" id="editVersion" placeholder="如 1.0.0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">软件大小</label>
                            <input type="text" class="form-control" name="size" id="editSize" placeholder="如 32.5MB">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">排序</label>
                            <input type="number" class="form-control" name="sort" id="editSort" value="0">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">下载地址</label>
                            <input type="text" class="form-control" name="download_url" id="editDownloadUrl" placeholder="https://...">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">软件图标</label>
                            <div class="d-flex align-items-center gap-2">
                                <div class="upload-box" id="iconUpload"><i class="fas fa-plus upload-icon"></i></div>
                                <input type="hidden" name="icon" id="editIcon">
                                <small class="text-muted">点击上传图标（jpg/png）</small>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">软件描述</label>
                            <textarea class="form-control" name="description" id="editDescription" rows="3"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">截图地址（每行一个）</label>
                            <textarea class="form-control" name="screenshots" id="editScreenshots" rows="3" placeholder="https://example.com/1.jpg"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">状态</label>
                            <select class="form-select" name="status" id="editStatus">
                                <option value="1">上架</option>
                                <option value="0">下架</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">热门</label>
                            <select class="form-select" name="is_hot" id="editIsHot">
                                <option value="0">否</option>
                                <option value="1">是</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">推荐</label>
                            <select class="form-select" name="is_recommend" id="editIsRecommend">
                                <option value="0">否</option>
                                <option value="1">是</option>
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
    var query = { page: 1, size: 20 };

    // 加载分类下拉
    function loadCategories() {
        return AdminApp.Api.get('category/list').then(function (res) {
            if (res.code === 200) {
                var list = res.data || [];
                var options = '<option value="0">全部分类</option>';
                list.forEach(function (c) {
                    options += '<option value="' + c.id + '">' + AdminApp.UI.escape(c.name) + '</option>';
                });
                $('#filterCategory').html(options);
                $('#editCategory').html(list.map(function (c) {
                    return '<option value="' + c.id + '">' + AdminApp.UI.escape(c.name) + '</option>';
                }).join(''));
            }
        });
    }

    // 加载列表
    function loadList() {
        AdminApp.UI.showLoading();
        var params = { page: query.page, size: query.size };
        if ($('#searchKeyword').val()) params.keyword = $('#searchKeyword').val();
        if ($('#filterCategory').val() && $('#filterCategory').val() !== '0') params.category_id = $('#filterCategory').val();
        if ($('#filterStatus').val() !== '') params.status = $('#filterStatus').val();

        AdminApp.Api.get('software/list', params).then(function (res) {
            AdminApp.UI.hideLoading();
            if (res.code !== 200) { AdminApp.UI.toast(res.message, 'error'); return; }
            renderList(res.data.list || []);
            AdminApp.renderPagination('#pagination', res.data, function (p) { query.page = p; loadList(); });
        }).fail(function (err) { AdminApp.UI.hideLoading(); AdminApp.UI.toast(err.message, 'error'); });
    }

    function renderList(list) {
        var $list = $('#dataList');
        if (list.length === 0) {
            $list.html('<tr><td colspan="10"><div class="empty-state"><i class="fas fa-box-open"></i><p>暂无软件数据</p></div></td></tr>');
            return;
        }
        var html = '';
        list.forEach(function (item) {
            var tags = '';
            if (parseInt(item.is_hot, 10) === 1) tags += '<span class="badge bg-danger me-1">热门</span>';
            if (parseInt(item.is_recommend, 10) === 1) tags += '<span class="badge" style="background:#8b5cf6;">推荐</span>';
            var thumb = item.icon ? '<img class="thumb" src="' + AdminApp.UI.escape(item.icon) + '">' : AdminApp.UI.thumbPlaceholder(item.name);
            html += '<tr>' +
                '<td>' + item.id + '</td>' +
                '<td><div class="d-flex align-items-center gap-2"><div>' + thumb + '</div><div><div class="fw-medium">' + AdminApp.UI.escape(item.name) + '</div></div></div></td>' +
                '<td class="hide-mobile">' + AdminApp.UI.escape(item.category_name || '-') + '</td>' +
                '<td class="hide-mobile">' + AdminApp.UI.escape(item.version || '-') + '</td>' +
                '<td class="hide-mobile">' + AdminApp.UI.escape(item.size || '-') + '</td>' +
                '<td>' + AdminApp.UI.formatNumber(item.download_count) + '</td>' +
                '<td>' + (item.sort || 0) + '</td>' +
                '<td>' + AdminApp.UI.statusTag(item.status, '上架', '下架') + '</td>' +
                '<td class="hide-mobile">' + (tags || '-') + '</td>' +
                '<td><button class="btn-action edit" data-id="' + item.id + '" title="编辑"><i class="fas fa-edit"></i></button>' +
                '<button class="btn-action delete btn-delete" data-id="' + item.id + '" data-name="' + AdminApp.UI.escape(item.name) + '" data-action="software/delete" title="删除"><i class="fas fa-trash"></i></button></td>' +
                '</tr>';
        });
        $list.html(html);
    }

    // 新增
    $('#btnAdd').on('click', function () {
        $('#modalTitle').text('添加软件');
        $('#editForm')[0].reset();
        $('#editId').val('');
        $('#editIcon').val('');
        $('#iconUpload').html('<i class="fas fa-plus upload-icon"></i>');
        $('#editStatus').val(1);
        $('#editSort').val(0);
        modal.show();
    });

    // 编辑
    $(document).on('click', '.btn-action.edit', function () {
        var id = $(this).data('id');
        AdminApp.UI.showLoading();
        AdminApp.Api.get('software/detail', { id: id }).then(function (res) {
            AdminApp.UI.hideLoading();
            if (res.code !== 200) { AdminApp.UI.toast(res.message, 'error'); return; }
            var d = res.data;
            $('#modalTitle').text('编辑软件');
            $('#editId').val(d.id);
            $('#editName').val(d.name);
            $('#editCategory').val(d.category_id);
            $('#editVersion').val(d.version);
            $('#editSize').val(d.size);
            $('#editSort').val(d.sort);
            $('#editDownloadUrl').val(d.download_url);
            $('#editDescription').val(d.description);
            $('#editStatus').val(d.status);
            $('#editIsHot').val(d.is_hot);
            $('#editIsRecommend').val(d.is_recommend);
            $('#editIcon').val(d.icon);
            var shots = d.screenshots || [];
            $('#editScreenshots').val(Array.isArray(shots) ? shots.join('\n') : '');
            if (d.icon) $('#iconUpload').html('<img src="' + AdminApp.UI.escape(d.icon) + '">'); else $('#iconUpload').html('<i class="fas fa-plus upload-icon"></i>');
            modal.show();
        }).fail(function (err) { AdminApp.UI.hideLoading(); AdminApp.UI.toast(err.message, 'error'); });
    });

    // 图标上传
    $('#iconUpload').on('click', function () {
        var $input = $('<input type="file" accept="image/*" style="display:none">');
        $('body').append($input);
        $input.on('change', function () {
            var file = this.files[0];
            if (!file) return;
            AdminApp.UI.showLoading();
            AdminApp.Api.upload(file, 'image').then(function (res) {
                AdminApp.UI.hideLoading();
                if (res.code === 200) {
                    $('#editIcon').val(res.data.url);
                    $('#iconUpload').html('<img src="' + AdminApp.UI.escape(res.data.url) + '">');
                    AdminApp.UI.toast('上传成功', 'success');
                } else {
                    AdminApp.UI.toast(res.message || '上传失败', 'error');
                }
            }).fail(function (err) { AdminApp.UI.hideLoading(); AdminApp.UI.toast(err.message || '上传失败', 'error'); });
            $input.remove();
        });
        $input.click();
    });

    // 保存
    $('#btnSave').on('click', function () {
        var id = $('#editId').val();
        var shots = $('#editScreenshots').val().split('\n').map(function (s) { return $.trim(s); }).filter(Boolean);
        var data = {
            name: $('#editName').val(),
            category_id: $('#editCategory').val(),
            version: $('#editVersion').val(),
            size: $('#editSize').val(),
            sort: $('#editSort').val(),
            download_url: $('#editDownloadUrl').val(),
            description: $('#editDescription').val(),
            status: $('#editStatus').val(),
            is_hot: $('#editIsHot').val(),
            is_recommend: $('#editIsRecommend').val(),
            icon: $('#editIcon').val(),
            screenshots: shots
        };
        if (!data.name) { AdminApp.UI.toast('请输入软件名称', 'warning'); return; }
        var action = id ? 'software/update' : 'software/create';
        if (id) data.id = id;
        var $btn = $(this); $btn.prop('disabled', true);
        AdminApp.Api.post(action, data).then(function (res) {
            $btn.prop('disabled', false);
            if (res.code === 200) {
                AdminApp.UI.toast(id ? '更新成功' : '添加成功', 'success');
                modal.hide();
                loadList();
            } else { AdminApp.UI.toast(res.message || '保存失败', 'error'); }
        }).fail(function (err) { $btn.prop('disabled', false); AdminApp.UI.toast(err.message || '保存失败', 'error'); });
    });

    // 搜索
    $('#btnSearch').on('click', function () { query.page = 1; loadList(); });
    $('#searchKeyword').on('keypress', function (e) { if (e.which === 13) { query.page = 1; loadList(); } });
    $('#filterCategory, #filterStatus').on('change', function () { query.page = 1; loadList(); });

    // 删除
    AdminApp.bindDelete('software/delete', loadList);

    // 初始化
    loadCategories().then(loadList);
});
</script>
</body>
</html>
