<?php
$pageTitle = '用户管理';
$breadcrumb = '用户与系统 / 用户管理';
require __DIR__ . '/header.php';
?>

<div class="card">
    <div class="card-body">
        <div class="search-bar" style="margin-bottom:16px;">
            <input type="text" id="user-search" class="form-control" placeholder="搜索用户名、昵称、手机、邮箱...">
            <select id="user-status-filter" class="form-control" style="min-width:120px;">
                <option value="">全部状态</option>
                <option value="1">正常</option>
                <option value="0">已禁用</option>
            </select>
            <button class="btn btn-primary" onclick="loadUsers(1)">搜索</button>
            <button class="btn btn-secondary" onclick="resetUserFilters()">重置</button>
        </div>

        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>头像</th>
                        <th>用户名</th>
                        <th>手机</th>
                        <th>邮箱</th>
                        <th>状态</th>
                        <th>注册时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="user-tbody">
                    <tr><td colspan="7" class="empty-state">加载中...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="pagination" id="user-pagination"></div>
    </div>
</div>

<div class="modal-overlay" id="user-detail-modal">
    <div class="modal" style="max-width:550px;">
        <div class="modal-header">
            <h3>用户详情</h3>
            <button class="modal-close" onclick="AdminApp.closeModal('user-detail-modal')">×</button>
        </div>
        <div class="modal-body" id="user-detail-content"></div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="AdminApp.closeModal('user-detail-modal')">关闭</button>
        </div>
    </div>
</div>

<script>
var userPage = 1;

function loadUsers(page) {
    userPage = page || 1;
    var search = document.getElementById('user-search').value;
    var status = document.getElementById('user-status-filter').value;

    var url = '/admin/user?page=' + userPage + '&per_page=15';
    if (search) url += '&search=' + encodeURIComponent(search);
    if (status !== '') url += '&status=' + status;

    AdminApp.get(url).then(function(res) {
        if (res.code === 200) {
            renderUsers(res.data.list || []);
            renderUserPagination(res.data.total || 0, res.data.page || 1, res.data.per_page || 15);
        }
    });
}

function renderUsers(list) {
    var tbody = document.getElementById('user-tbody');
    if (!list.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="empty-state"><div class="icon">👥</div>暂无用户</td></tr>';
        return;
    }
    tbody.innerHTML = list.map(function(item) {
        var statusBadge = item.status == 1
            ? '<span class="badge badge-success">正常</span>'
            : '<span class="badge badge-danger">已禁用</span>';
        var avatar = item.avatar
            ? '<img src="' + AdminApp.escapeHtml(item.avatar) + '" class="avatar" alt="">'
            : '<div class="avatar" style="display:flex;align-items:center;justify-content:center;background:var(--primary-light);color:var(--primary);font-weight:600;">' + (item.nickname || item.username).charAt(0).toUpperCase() + '</div>';
        return '<tr>' +
            '<td>' + avatar + '</td>' +
            '<td><strong>' + AdminApp.escapeHtml(item.nickname || item.username) + '</strong><br><span style="color:var(--text-muted);font-size:0.8rem;">@' + AdminApp.escapeHtml(item.username) + '</span></td>' +
            '<td>' + AdminApp.escapeHtml(item.phone || '-') + '</td>' +
            '<td>' + AdminApp.escapeHtml(item.email || '-') + '</td>' +
            '<td>' + statusBadge + '</td>' +
            '<td>' + AdminApp.formatDate(item.created_at) + '</td>' +
            '<td class="actions">' +
                '<button class="btn-link" onclick="viewUserDetail(' + item.id + ')">查看</button>' +
                '<button class="btn-link ' + (item.status == 1 ? 'danger' : '') + '" onclick="toggleUser(' + item.id + ', ' + item.status + ')">' + (item.status == 1 ? '禁用' : '启用') + '</button>' +
                '<button class="btn-link danger" onclick="deleteUser(' + item.id + ')">删除</button>' +
            '</td>' +
        '</tr>';
    }).join('');
}

function renderUserPagination(total, page, perPage) {
    var totalPages = Math.ceil(total / perPage);
    var el = document.getElementById('user-pagination');
    if (totalPages <= 1) {
        el.innerHTML = '<span>共 ' + total + ' 条</span><div class="page-controls"></div>';
        return;
    }
    var controls = '';
    var start = Math.max(1, page - 2);
    var end = Math.min(totalPages, start + 4);
    start = Math.max(1, end - 4);
    controls += '<button ' + (page <= 1 ? 'disabled' : '') + ' onclick="loadUsers(' + (page - 1) + ')">上一页</button>';
    for (var i = start; i <= end; i++) {
        controls += '<button class="' + (i === page ? 'active' : '') + '" onclick="loadUsers(' + i + ')">' + i + '</button>';
    }
    controls += '<button ' + (page >= totalPages ? 'disabled' : '') + ' onclick="loadUsers(' + (page + 1) + ')">下一页</button>';
    el.innerHTML = '<span>共 ' + total + ' 条 / 第 ' + page + '/' + totalPages + ' 页</span><div class="page-controls">' + controls + '</div>';
}

function resetUserFilters() {
    document.getElementById('user-search').value = '';
    document.getElementById('user-status-filter').value = '';
    loadUsers(1);
}

function viewUserDetail(id) {
    AdminApp.get('/admin/user/' + id).then(function(res) {
        if (res.code === 200) {
            var d = res.data;
            var avatar = d.avatar
                ? '<img src="' + AdminApp.escapeHtml(d.avatar) + '" style="width:64px;height:64px;border-radius:50%;object-fit:cover;">'
                : '<div style="width:64px;height:64px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:600;">' + (d.nickname || d.username).charAt(0).toUpperCase() + '</div>';

            document.getElementById('user-detail-content').innerHTML =
                '<div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">' +
                    avatar +
                    '<div>' +
                        '<h3 style="margin-bottom:4px;">' + AdminApp.escapeHtml(d.nickname || d.username) + '</h3>' +
                        '<span style="color:var(--text-muted);font-size:0.85rem;">@' + AdminApp.escapeHtml(d.username) + '</span>' +
                    '</div>' +
                '</div>' +
                '<div class="detail-section"><h4>基本信息</h4>' +
                    '<div class="detail-grid">' +
                        '<div class="label">用户ID</div><div>' + d.id + '</div>' +
                        '<div class="label">手机号</div><div>' + AdminApp.escapeHtml(d.phone || '-') + '</div>' +
                        '<div class="label">邮箱</div><div>' + AdminApp.escapeHtml(d.email || '-') + '</div>' +
                        '<div class="label">性别</div><div>' + (d.gender == 1 ? '男' : (d.gender == 2 ? '女' : '未知')) + '</div>' +
                        '<div class="label">余额</div><div>¥' + (d.balance || '0.00') + '</div>' +
                        '<div class="label">状态</div><div>' + (d.status == 1 ? '<span class="badge badge-success">正常</span>' : '<span class="badge badge-danger">已禁用</span>') + '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="detail-section"><h4>统计数据</h4>' +
                    '<div class="detail-grid">' +
                        '<div class="label">下载次数</div><div>' + (d.download_count || 0) + ' 次</div>' +
                        '<div class="label">收藏次数</div><div>' + (d.favorite_count || 0) + ' 次</div>' +
                        '<div class="label">最后登录</div><div>' + AdminApp.formatDate(d.last_login_at) + '</div>' +
                        '<div class="label">注册时间</div><div>' + AdminApp.formatDate(d.created_at) + '</div>' +
                    '</div>' +
                '</div>';
            AdminApp.modal('user-detail-modal');
        }
    });
}

function toggleUser(id, status) {
    var action = status == 1 ? '禁用' : '启用';
    AdminApp.confirm('确定要' + action + '此用户吗？').then(function(ok) {
        if (!ok) return;
        AdminApp.post('/admin/user/' + id + '/toggle').then(function(res) {
            if (res.code === 200) {
                AdminApp.toast(res.message, 'success');
                loadUsers(userPage);
            } else {
                AdminApp.toast(res.message || '操作失败', 'error');
            }
        });
    });
}

function deleteUser(id) {
    AdminApp.confirm('确定要删除此用户吗？此操作不可恢复。').then(function(ok) {
        if (!ok) return;
        AdminApp.del('/admin/user/' + id).then(function(res) {
            if (res.code === 200) {
                AdminApp.toast('删除成功', 'success');
                loadUsers(userPage);
            } else {
                AdminApp.toast(res.message || '删除失败', 'error');
            }
        });
    });
}

document.getElementById('user-search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') loadUsers(1);
});
document.getElementById('user-status-filter').addEventListener('change', function() { loadUsers(1); });

loadUsers(1);
</script>

<?php require __DIR__ . '/footer.php'; ?>
