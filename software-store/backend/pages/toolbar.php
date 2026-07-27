<?php
$pageTitle = '工具栏管理';
$breadcrumb = '内容管理 / 工具栏';
$headerActions = '<button class="btn btn-primary" onclick="openToolbarModal()">+ 新增工具栏项</button>';
require __DIR__ . '/header.php';
?>

<div class="card">
    <div class="card-body">
        <div id="toolbar-list"></div>
        <div id="toolbar-empty" class="empty-state" style="display:none;">
            <div class="icon">🔧</div>
            暂无工具栏项，点击右上角添加
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>App 端预览</h3></div>
    <div class="card-body">
        <div style="max-width:300px;margin:0 auto;background:var(--bg-body);border-radius:16px;padding:16px;min-height:200px;">
            <div style="font-size:0.85rem;color:var(--text-muted);margin-bottom:8px;">工具栏预览</div>
            <div id="toolbar-preview" style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;"></div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="toolbar-modal">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <h3 id="tb-modal-title">新增工具栏项</h3>
            <button class="modal-close" onclick="AdminApp.closeModal('toolbar-modal')">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="tb-id">
            <div class="form-group">
                <label>名称 *</label>
                <input type="text" id="tb-name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>图标</label>
                <div class="image-uploader" onclick="document.getElementById('tb-icon-file').click()">
                    <img id="tb-icon-preview" class="preview" style="display:none;">
                    <div class="placeholder" id="tb-icon-placeholder">点击上传图标</div>
                </div>
                <input type="file" id="tb-icon-file" accept="image/*" style="display:none;" onchange="previewImage(this,'tb-icon-preview','tb-icon-placeholder')">
            </div>
            <div class="form-group">
                <label>描述</label>
                <input type="text" id="tb-desc" class="form-control" placeholder="简短描述">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>链接类型</label>
                    <select id="tb-link-type" class="form-control">
                        <option value="1">外部链接</option>
                        <option value="2">内部页面</option>
                        <option value="3">小程序</option>
                        <option value="4">其他</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>链接值</label>
                    <input type="text" id="tb-link-value" class="form-control" placeholder="URL 或页面标识">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>排序 (越大越靠前)</label>
                    <input type="number" id="tb-sort" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label>状态</label>
                    <select id="tb-status" class="form-control">
                        <option value="1">启用</option>
                        <option value="0">禁用</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="AdminApp.closeModal('toolbar-modal')">取消</button>
            <button class="btn btn-primary" onclick="saveToolbar()">保存</button>
        </div>
    </div>
</div>

<script>
var toolbarItems = [];

function loadToolbar() {
    AdminApp.get('/admin/toolbar').then(function(res) {
        if (res.code === 200) {
            toolbarItems = res.data || [];
            renderToolbar();
            renderPreview();
        }
    });
}

function renderToolbar() {
    var list = document.getElementById('toolbar-list');
    var empty = document.getElementById('toolbar-empty');
    if (!toolbarItems.length) {
        list.innerHTML = '';
        empty.style.display = 'block';
        return;
    }
    empty.style.display = 'none';
    list.innerHTML = toolbarItems.map(function(item, idx) {
        var statusBadge = item.status == 1
            ? '<span class="badge badge-success">启用</span>'
            : '<span class="badge badge-default">禁用</span>';
        var iconHtml = item.icon
            ? '<img src="' + AdminApp.escapeHtml(item.icon) + '" style="width:40px;height:40px;border-radius:8px;object-fit:cover;">'
            : '<div style="width:40px;height:40px;border-radius:8px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;font-size:1.2rem;">🔧</div>';
        return '<div class="toolbar-item" draggable="true" data-id="' + item.id + '">' +
            '<span class="drag-handle">☰</span>' +
            iconHtml +
            '<div class="info">' +
                '<div class="name">' + AdminApp.escapeHtml(item.name) + ' ' + statusBadge + '</div>' +
                '<div class="desc">' + AdminApp.escapeHtml(item.link_value || item.description || '-') + ' · 排序: ' + item.sort + '</div>' +
            '</div>' +
            '<div class="sort-buttons">' +
                '<button class="btn btn-sm btn-secondary" onclick="moveItem(' + idx + ', -1)">↑</button>' +
                '<button class="btn btn-sm btn-secondary" onclick="moveItem(' + idx + ', 1)">↓</button>' +
                '<button class="btn btn-sm ' + (item.status == 1 ? 'btn-warning' : 'btn-success') + '" onclick="toggleToolbar(' + item.id + ', ' + item.status + ')">' + (item.status == 1 ? '禁用' : '启用') + '</button>' +
                '<button class="btn btn-sm btn-secondary" onclick="editToolbar(' + item.id + ')">编辑</button>' +
                '<button class="btn btn-sm btn-danger" onclick="deleteToolbar(' + item.id + ')">删除</button>' +
            '</div>' +
        '</div>';
    }).join('');
}

function renderPreview() {
    var preview = document.getElementById('toolbar-preview');
    var activeItems = toolbarItems.filter(function(i) { return i.status == 1; });
    if (!activeItems.length) {
        preview.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:var(--text-muted);font-size:0.85rem;">暂无启用的工具栏项</div>';
        return;
    }
    preview.innerHTML = activeItems.map(function(item) {
        var iconHtml = item.icon
            ? '<img src="' + AdminApp.escapeHtml(item.icon) + '" style="width:40px;height:40px;border-radius:8px;object-fit:cover;margin-bottom:4px;">'
            : '<div style="width:40px;height:40px;border-radius:8px;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;margin-bottom:4px;font-size:1rem;">🔧</div>';
        return '<div style="display:flex;flex-direction:column;align-items:center;text-align:center;">' +
            iconHtml +
            '<div style="font-size:0.7rem;color:var(--text-secondary);">' + AdminApp.escapeHtml(item.name) + '</div>' +
        '</div>';
    }).join('');
}

function moveItem(idx, dir) {
    var newIdx = idx + dir;
    if (newIdx < 0 || newIdx >= toolbarItems.length) return;
    var temp = toolbarItems[idx];
    toolbarItems[idx] = toolbarItems[newIdx];
    toolbarItems[newIdx] = temp;
    var items = toolbarItems.map(function(item, i) { return { id: item.id, sort: (toolbarItems.length - i) * 10 }; });
    AdminApp.post('/admin/toolbar/sort', { items: items }).then(function(res) {
        if (res.code === 200) {
            AdminApp.toast('排序已更新', 'success');
            toolbarItems.forEach(function(item, i) { item.sort = (toolbarItems.length - i) * 10; });
            renderToolbar();
        }
    });
}

function openToolbarModal() {
    document.getElementById('tb-modal-title').textContent = '新增工具栏项';
    ['tb-id','tb-name','tb-desc','tb-link-value'].forEach(function(id) { document.getElementById(id).value = ''; });
    document.getElementById('tb-link-type').value = '1';
    document.getElementById('tb-sort').value = '0';
    document.getElementById('tb-status').value = '1';
    document.getElementById('tb-icon-preview').style.display = 'none';
    document.getElementById('tb-icon-placeholder').style.display = 'block';
    document.getElementById('tb-icon-file').value = '';
    AdminApp.modal('toolbar-modal');
}

function editToolbar(id) {
    var item = toolbarItems.find(function(i) { return i.id === id; });
    if (!item) return;
    document.getElementById('tb-modal-title').textContent = '编辑工具栏项';
    document.getElementById('tb-id').value = item.id;
    document.getElementById('tb-name').value = item.name;
    document.getElementById('tb-desc').value = item.description || '';
    document.getElementById('tb-link-type').value = item.link_type || 1;
    document.getElementById('tb-link-value').value = item.link_value || '';
    document.getElementById('tb-sort').value = item.sort;
    document.getElementById('tb-status').value = item.status;
    if (item.icon) {
        var preview = document.getElementById('tb-icon-preview');
        preview.src = item.icon;
        preview.style.display = 'block';
        document.getElementById('tb-icon-placeholder').style.display = 'none';
    }
    AdminApp.modal('toolbar-modal');
}

function saveToolbar() {
    var id = document.getElementById('tb-id').value;
    var data = {
        name: document.getElementById('tb-name').value,
        link_type: parseInt(document.getElementById('tb-link-type').value),
        link_value: document.getElementById('tb-link-value').value,
        sort: parseInt(document.getElementById('tb-sort').value) || 0,
        status: parseInt(document.getElementById('tb-status').value),
    };
    if (!data.name) { AdminApp.toast('请填写名称', 'warning'); return; }

    var iconFile = document.getElementById('tb-icon-file').files[0];
    var handleFile = function(file) {
        return new Promise(function(resolve) {
            if (!file) resolve({});
            else {
                var reader = new FileReader();
                reader.onload = function(e) {
                    resolve({ icon_file: { name: file.name, type: file.type, size: file.size, content: e.target.result } });
                };
                reader.readAsDataURL(file);
            }
        });
    };

    handleFile(iconFile).then(function(extra) {
        Object.assign(data, extra);
        var url = id ? '/admin/toolbar/' + id : '/admin/toolbar';
        var method = id ? 'put' : 'post';
        AdminApp[method](url, data).then(function(res) {
            if (res.code === 200) {
                AdminApp.toast(res.message || '保存成功', 'success');
                AdminApp.closeModal('toolbar-modal');
                loadToolbar();
            } else {
                AdminApp.toast(res.message || '保存失败', 'error');
            }
        });
    });
}

function toggleToolbar(id, status) {
    AdminApp.post('/admin/toolbar/' + id + '/toggle').then(function(res) {
        if (res.code === 200) {
            AdminApp.toast(res.message, 'success');
            loadToolbar();
        } else {
            AdminApp.toast(res.message || '操作失败', 'error');
        }
    });
}

function deleteToolbar(id) {
    AdminApp.confirm('确定要删除此工具栏项吗？').then(function(ok) {
        if (!ok) return;
        AdminApp.del('/admin/toolbar/' + id).then(function(res) {
            if (res.code === 200) {
                AdminApp.toast('删除成功', 'success');
                loadToolbar();
            } else {
                AdminApp.toast(res.message || '删除失败', 'error');
            }
        });
    });
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

loadToolbar();
</script>

<?php require __DIR__ . '/footer.php'; ?>
