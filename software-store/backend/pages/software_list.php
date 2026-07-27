<?php
$pageTitle = '软件管理';
$breadcrumb = '内容管理 / 软件列表';
$headerActions = '<button class="btn btn-primary" onclick="openSoftwareModal()">+ 新增软件</button>';
require __DIR__ . '/header.php';
?>

<div class="card">
    <div class="card-body">
        <div class="search-bar" style="margin-bottom:16px;">
            <input type="text" id="search-input" class="form-control" placeholder="搜索软件名称...">
            <select id="category-filter" class="form-control" style="min-width:150px;">
                <option value="">全部分类</option>
            </select>
            <select id="status-filter" class="form-control" style="min-width:120px;">
                <option value="">全部状态</option>
                <option value="1">已上架</option>
                <option value="0">已下架</option>
            </select>
            <button class="btn btn-primary" onclick="loadSoftware(1)">搜索</button>
            <button class="btn btn-secondary" onclick="resetFilters()">重置</button>
        </div>

        <div style="overflow-x:auto;">
            <table class="table" id="software-table">
                <thead>
                    <tr>
                        <th>图标</th>
                        <th>名称</th>
                        <th>分类</th>
                        <th>大小</th>
                        <th>版本</th>
                        <th>下载量</th>
                        <th>状态</th>
                        <th>更新时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="software-tbody">
                    <tr><td colspan="9" class="empty-state">加载中...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="pagination" id="pagination"></div>
    </div>
</div>

<div class="modal-overlay" id="software-modal">
    <div class="modal" style="max-width:700px;">
        <div class="modal-header">
            <h3 id="modal-title">新增软件</h3>
            <button class="modal-close" onclick="closeSoftwareModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="sw-id">
            <div class="form-row">
                <div class="form-group">
                    <label>软件名称 *</label>
                    <input type="text" id="sw-name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>版本号</label>
                    <input type="text" id="sw-version" class="form-control" placeholder="如 1.0.0">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>分类 *</label>
                    <select id="sw-category" class="form-control" required>
                        <option value="">请选择</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>大小 (字节)</label>
                    <input type="number" id="sw-size" class="form-control" min="0">
                </div>
            </div>
            <div class="form-group">
                <label>描述</label>
                <textarea id="sw-description" class="form-control" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label>更新公告</label>
                <textarea id="sw-notice" class="form-control" rows="2" placeholder="新版本更新内容"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>置顶</label>
                    <div>
                        <label class="switch"><input type="checkbox" id="sw-top"><span class="switch-slider"></span></label>
                    </div>
                </div>
                <div class="form-group">
                    <label>状态</label>
                    <select id="sw-status" class="form-control">
                        <option value="1">上架</option>
                        <option value="0">下架</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>图标上传</label>
                    <div class="image-uploader" onclick="document.getElementById('sw-icon-file').click()">
                        <img id="sw-icon-preview" class="preview" style="display:none;">
                        <div class="placeholder" id="sw-icon-placeholder">点击上传图标</div>
                    </div>
                    <input type="file" id="sw-icon-file" accept="image/*" style="display:none;" onchange="previewImage(this,'sw-icon-preview','sw-icon-placeholder')">
                </div>
                <div class="form-group">
                    <label>APK 上传</label>
                    <div class="image-uploader" onclick="document.getElementById('sw-apk-file').click()">
                        <div class="placeholder" id="sw-apk-placeholder">点击上传 APK 文件</div>
                    </div>
                    <input type="file" id="sw-apk-file" accept=".apk" style="display:none;" onchange="document.getElementById('sw-apk-placeholder').textContent = this.files[0]?.name || '已选择'">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeSoftwareModal()">取消</button>
            <button class="btn btn-primary" onclick="saveSoftware()">保存</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="detail-modal">
    <div class="modal" style="max-width:600px;">
        <div class="modal-header">
            <h3>软件详情</h3>
            <button class="modal-close" onclick="AdminApp.closeModal('detail-modal')">×</button>
        </div>
        <div class="modal-body" id="detail-content"></div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="AdminApp.closeModal('detail-modal')">关闭</button>
        </div>
    </div>
</div>

<script>
var categoriesCache = [];
var currentPage = 1;

function loadCategories() {
    AdminApp.get('/admin/category').then(function(res) {
        if (res.code === 200) {
            categoriesCache = res.data.list || [];
            var selects = ['category-filter', 'sw-category'];
            selects.forEach(function(id) {
                var sel = document.getElementById(id);
                if (!sel) return;
                categoriesCache.forEach(function(c) {
                    var opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name;
                    sel.appendChild(opt);
                });
            });
        }
    });
}

function loadSoftware(page) {
    currentPage = page || 1;
    var search = document.getElementById('search-input').value;
    var categoryId = document.getElementById('category-filter').value;
    var status = document.getElementById('status-filter').value;

    var url = '/admin/software?page=' + currentPage + '&per_page=15';
    if (search) url += '&search=' + encodeURIComponent(search);
    if (categoryId) url += '&category_id=' + categoryId;
    if (status !== '') url += '&status=' + status;

    AdminApp.get(url).then(function(res) {
        if (res.code === 200) {
            renderTable(res.data.list || []);
            renderPagination(res.data.total || 0, res.data.page || 1, res.data.per_page || 15);
        }
    });
}

function renderTable(list) {
    var tbody = document.getElementById('software-tbody');
    if (!list.length) {
        tbody.innerHTML = '<tr><td colspan="9" class="empty-state"><div class="icon">📦</div>暂无数据</td></tr>';
        return;
    }
    tbody.innerHTML = list.map(function(item) {
        var statusBadge = item.status == 1
            ? '<span class="badge badge-success">已上架</span>'
            : '<span class="badge badge-default">已下架</span>';
        var icon = item.icon
            ? '<img src="' + AdminApp.escapeHtml(item.icon) + '" class="thumb" alt="">'
            : '<div class="thumb" style="width:36px;height:36px;background:var(--bg-body);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:18px;">📱</div>';
        return '<tr>' +
            '<td>' + icon + '</td>' +
            '<td><strong>' + AdminApp.escapeHtml(item.name) + '</strong></td>' +
            '<td>' + AdminApp.escapeHtml(item.category_name || '-') + '</td>' +
            '<td>' + AdminApp.formatSize(item.size) + '</td>' +
            '<td>' + AdminApp.escapeHtml(item.version || '-') + '</td>' +
            '<td>' + (item.download_count || 0) + '</td>' +
            '<td>' + statusBadge + '</td>' +
            '<td>' + AdminApp.formatDate(item.updated_at) + '</td>' +
            '<td class="actions">' +
                '<button class="btn-link" onclick="viewDetail(' + item.id + ')">查看</button>' +
                '<button class="btn-link" onclick="editSoftware(' + item.id + ')">编辑</button>' +
                '<button class="btn-link ' + (item.status == 1 ? 'danger' : '') + '" onclick="toggleStatus(' + item.id + ', ' + item.status + ')">' + (item.status == 1 ? '下架' : '上架') + '</button>' +
                '<button class="btn-link danger" onclick="deleteSoftware(' + item.id + ')">删除</button>' +
            '</td>' +
        '</tr>';
    }).join('');
}

function renderPagination(total, page, perPage) {
    var totalPages = Math.ceil(total / perPage);
    var el = document.getElementById('pagination');
    if (totalPages <= 1) {
        el.innerHTML = '<span>共 ' + total + ' 条</span><div class="page-controls"></div>';
        return;
    }
    var controls = '';
    var start = Math.max(1, page - 2);
    var end = Math.min(totalPages, start + 4);
    start = Math.max(1, end - 4);
    controls += '<button ' + (page <= 1 ? 'disabled' : '') + ' onclick="loadSoftware(' + (page - 1) + ')">上一页</button>';
    for (var i = start; i <= end; i++) {
        controls += '<button class="' + (i === page ? 'active' : '') + '" onclick="loadSoftware(' + i + ')">' + i + '</button>';
    }
    controls += '<button ' + (page >= totalPages ? 'disabled' : '') + ' onclick="loadSoftware(' + (page + 1) + ')">下一页</button>';
    el.innerHTML = '<span>共 ' + total + ' 条 / 第 ' + page + '/' + totalPages + ' 页</span><div class="page-controls">' + controls + '</div>';
}

function resetFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('category-filter').value = '';
    document.getElementById('status-filter').value = '';
    loadSoftware(1);
}

function openSoftwareModal() {
    document.getElementById('modal-title').textContent = '新增软件';
    ['sw-id','sw-name','sw-version','sw-size','sw-description','sw-notice'].forEach(function(id) {
        document.getElementById(id).value = '';
    });
    document.getElementById('sw-category').value = '';
    document.getElementById('sw-status').value = '1';
    document.getElementById('sw-top').checked = false;
    document.getElementById('sw-icon-preview').style.display = 'none';
    document.getElementById('sw-icon-placeholder').style.display = 'block';
    document.getElementById('sw-apk-placeholder').textContent = '点击上传 APK 文件';
    document.getElementById('sw-icon-file').value = '';
    document.getElementById('sw-apk-file').value = '';
    AdminApp.modal('software-modal');
}

function closeSoftwareModal() {
    AdminApp.closeModal('software-modal');
}

function previewImage(input, previewId, placeholderId) {
    var file = input.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var preview = document.getElementById(previewId);
        var placeholder = document.getElementById(placeholderId);
        preview.src = e.target.result;
        preview.style.display = 'block';
        placeholder.style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function editSoftware(id) {
    AdminApp.get('/admin/software/' + id).then(function(res) {
        if (res.code === 200) {
            var d = res.data;
            document.getElementById('modal-title').textContent = '编辑软件';
            document.getElementById('sw-id').value = d.id;
            document.getElementById('sw-name').value = d.name || '';
            document.getElementById('sw-version').value = d.version || '';
            document.getElementById('sw-category').value = d.category_id || '';
            document.getElementById('sw-size').value = d.size || 0;
            document.getElementById('sw-description').value = d.description || '';
            document.getElementById('sw-notice').value = d.subtitle || '';
            document.getElementById('sw-top').checked = d.is_recommend == 1;
            document.getElementById('sw-status').value = d.status;
            if (d.icon) {
                var preview = document.getElementById('sw-icon-preview');
                var placeholder = document.getElementById('sw-icon-placeholder');
                preview.src = d.icon;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            }
            AdminApp.modal('software-modal');
        }
    });
}

function saveSoftware() {
    var id = document.getElementById('sw-id').value;
    var data = {
        name: document.getElementById('sw-name').value,
        category_id: parseInt(document.getElementById('sw-category').value) || 0,
        version: document.getElementById('sw-version').value,
        size: parseInt(document.getElementById('sw-size').value) || 0,
        description: document.getElementById('sw-description').value,
        subtitle: document.getElementById('sw-notice').value,
        is_recommend: document.getElementById('sw-top').checked ? 1 : 0,
        status: parseInt(document.getElementById('sw-status').value),
    };

    if (!data.name) { AdminApp.toast('请填写软件名称', 'warning'); return; }
    if (!data.category_id) { AdminApp.toast('请选择分类', 'warning'); return; }

    var iconFile = document.getElementById('sw-icon-file').files[0];
    var apkFile = document.getElementById('sw-apk-file').files[0];

    var sendData = function(extraData) {
        Object.assign(data, extraData || {});
        var url = id ? '/admin/software/' + id : '/admin/software';
        var method = id ? 'put' : 'post';
        AdminApp[method](url, data).then(function(res) {
            if (res.code === 200) {
                AdminApp.toast(res.message || '保存成功', 'success');
                closeSoftwareModal();
                loadSoftware(currentPage);
            } else {
                AdminApp.toast(res.message || '保存失败', 'error');
            }
        });
    };

    var handleFile = function(file, fieldName) {
        return new Promise(function(resolve) {
            if (!file) resolve({});
            else {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var result = {};
                    result[fieldName] = {
                        name: file.name,
                        type: file.type,
                        size: file.size,
                        content: e.target.result
                    };
                    resolve(result);
                };
                reader.readAsDataURL(file);
            }
        });
    };

    Promise.all([
        handleFile(iconFile, 'icon_file'),
        handleFile(apkFile, 'apk_file')
    ]).then(function(results) {
        var extra = {};
        results.forEach(function(r) { Object.assign(extra, r); });
        sendData(extra);
    });
}

function toggleStatus(id, currentStatus) {
    var newStatus = currentStatus == 1 ? 0 : 1;
    AdminApp.post('/admin/software/' + id + '/toggle', { status: newStatus }).then(function(res) {
        if (res.code === 200) {
            AdminApp.toast(res.message, 'success');
            loadSoftware(currentPage);
        } else {
            AdminApp.toast(res.message || '操作失败', 'error');
        }
    });
}

function deleteSoftware(id) {
    AdminApp.confirm('确定要删除此软件吗？此操作不可恢复。').then(function(ok) {
        if (!ok) return;
        AdminApp.del('/admin/software/' + id).then(function(res) {
            if (res.code === 200) {
                AdminApp.toast('删除成功', 'success');
                loadSoftware(currentPage);
            } else {
                AdminApp.toast(res.message || '删除失败', 'error');
            }
        });
    });
}

function viewDetail(id) {
    AdminApp.get('/admin/software/' + id).then(function(res) {
        if (res.code === 200) {
            var d = res.data;
            document.getElementById('detail-content').innerHTML =
                '<div class="detail-section">' +
                    '<h4>基本信息</h4>' +
                    '<div class="detail-grid">' +
                        '<div class="label">名称</div><div>' + AdminApp.escapeHtml(d.name) + '</div>' +
                        '<div class="label">分类</div><div>' + AdminApp.escapeHtml(d.category_name || '-') + '</div>' +
                        '<div class="label">版本</div><div>' + AdminApp.escapeHtml(d.version || '-') + '</div>' +
                        '<div class="label">大小</div><div>' + AdminApp.formatSize(d.size) + '</div>' +
                        '<div class="label">状态</div><div>' + (d.status == 1 ? '<span class="badge badge-success">已上架</span>' : '<span class="badge badge-default">已下架</span>') + '</div>' +
                        '<div class="label">下载量</div><div>' + (d.download_count || 0) + '</div>' +
                    '</div>' +
                '</div>' +
                (d.description ? '<div class="detail-section"><h4>描述</h4><div style="white-space:pre-wrap;">' + AdminApp.escapeHtml(d.description) + '</div></div>' : '') +
                (d.icon ? '<div class="detail-section"><h4>图标</h4><img src="' + AdminApp.escapeHtml(d.icon) + '" style="width:80px;height:80px;border-radius:8px;"></div>' : '') +
                '<div class="detail-section"><h4>时间信息</h4><div class="detail-grid">' +
                    '<div class="label">创建时间</div><div>' + AdminApp.formatDate(d.created_at) + '</div>' +
                    '<div class="label">更新时间</div><div>' + AdminApp.formatDate(d.updated_at) + '</div>' +
                '</div></div>';
            AdminApp.modal('detail-modal');
        }
    });
}

document.getElementById('search-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') loadSoftware(1);
});
document.getElementById('category-filter').addEventListener('change', function() { loadSoftware(1); });
document.getElementById('status-filter').addEventListener('change', function() { loadSoftware(1); });

loadCategories();
loadSoftware(1);
</script>

<?php require __DIR__ . '/footer.php'; ?>
