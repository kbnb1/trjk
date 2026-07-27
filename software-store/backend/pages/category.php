<?php
$pageTitle = '分类管理';
$breadcrumb = '内容管理 / 分类管理';
$headerActions = '<button class="btn btn-primary" onclick="openCategoryModal()">+ 新增分类</button>';
require __DIR__ . '/header.php';
?>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:40px;">排序</th>
                    <th>图标</th>
                    <th>分类名称</th>
                    <th>软件数量</th>
                    <th>状态</th>
                    <th>排序值</th>
                    <th>更新时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id="category-tbody">
                <tr><td colspan="8" class="empty-state">加载中...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="category-modal">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <h3 id="cat-modal-title">新增分类</h3>
            <button class="modal-close" onclick="AdminApp.closeModal('category-modal')">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="cat-id">
            <div class="form-group">
                <label>分类名称 *</label>
                <input type="text" id="cat-name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>上级分类</label>
                <select id="cat-parent" class="form-control">
                    <option value="0">作为顶级分类</option>
                </select>
            </div>
            <div class="form-group">
                <label>图标</label>
                <div class="image-uploader" onclick="document.getElementById('cat-icon-file').click()">
                    <img id="cat-icon-preview" class="preview" style="display:none;">
                    <div class="placeholder" id="cat-icon-placeholder">点击上传图标</div>
                </div>
                <input type="file" id="cat-icon-file" accept="image/*" style="display:none;" onchange="previewImage(this,'cat-icon-preview','cat-icon-placeholder')">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>排序 (越大越靠前)</label>
                    <input type="number" id="cat-sort" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label>状态</label>
                    <select id="cat-status" class="form-control">
                        <option value="1">启用</option>
                        <option value="0">禁用</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="AdminApp.closeModal('category-modal')">取消</button>
            <button class="btn btn-primary" onclick="saveCategory()">保存</button>
        </div>
    </div>
</div>

<script>
var categoryList = [];

function loadCategories() {
    AdminApp.get('/admin/category').then(function(res) {
        if (res.code === 200) {
            categoryList = res.data.list || [];
            renderCategories();
            renderParentOptions();
        }
    });
}

function renderCategories() {
    var tbody = document.getElementById('category-tbody');
    if (!categoryList.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="empty-state"><div class="icon">📂</div>暂无分类，请添加</td></tr>';
        return;
    }
    tbody.innerHTML = categoryList.map(function(item) {
        var statusBadge = item.status == 1
            ? '<span class="badge badge-success">启用</span>'
            : '<span class="badge badge-default">禁用</span>';
        var icon = item.icon
            ? '<img src="' + AdminApp.escapeHtml(item.icon) + '" class="thumb" alt="">'
            : '<span style="color:var(--text-muted);">—</span>';
        return '<tr>' +
            '<td><span class="drag-handle">☰</span></td>' +
            '<td>' + icon + '</td>' +
            '<td><strong>' + AdminApp.escapeHtml(item.name) + '</strong></td>' +
            '<td>' + (item.software_count || 0) + ' 个</td>' +
            '<td>' + statusBadge + '</td>' +
            '<td>' + item.sort + '</td>' +
            '<td>' + AdminApp.formatDate(item.updated_at || item.created_at) + '</td>' +
            '<td class="actions">' +
                '<button class="btn-link" onclick="editCategory(' + item.id + ')">编辑</button>' +
                '<button class="btn-link" onclick="toggleCategoryStatus(' + item.id + ', ' + item.status + ')">' + (item.status == 1 ? '禁用' : '启用') + '</button>' +
                '<button class="btn-link danger" onclick="deleteCategory(' + item.id + ', ' + (item.software_count || 0) + ')">删除</button>' +
            '</td>' +
        '</tr>';
    }).join('');
}

function renderParentOptions() {
    var sel = document.getElementById('cat-parent');
    var currentId = parseInt(document.getElementById('cat-id').value);
    sel.innerHTML = '<option value="0">作为顶级分类</option>';
    categoryList.forEach(function(c) {
        if (c.id !== currentId) {
            var opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name;
            sel.appendChild(opt);
        }
    });
}

function openCategoryModal() {
    document.getElementById('cat-modal-title').textContent = '新增分类';
    document.getElementById('cat-id').value = '';
    document.getElementById('cat-name').value = '';
    document.getElementById('cat-sort').value = '0';
    document.getElementById('cat-status').value = '1';
    document.getElementById('cat-parent').value = '0';
    document.getElementById('cat-icon-preview').style.display = 'none';
    document.getElementById('cat-icon-placeholder').style.display = 'block';
    document.getElementById('cat-icon-file').value = '';
    renderParentOptions();
    AdminApp.modal('category-modal');
}

function editCategory(id) {
    var item = categoryList.find(function(c) { return c.id === id; });
    if (!item) return;
    document.getElementById('cat-modal-title').textContent = '编辑分类';
    document.getElementById('cat-id').value = item.id;
    document.getElementById('cat-name').value = item.name;
    document.getElementById('cat-sort').value = item.sort;
    document.getElementById('cat-status').value = item.status;
    document.getElementById('cat-parent').value = item.parent_id || 0;
    if (item.icon) {
        var preview = document.getElementById('cat-icon-preview');
        preview.src = item.icon;
        preview.style.display = 'block';
        document.getElementById('cat-icon-placeholder').style.display = 'none';
    }
    renderParentOptions();
    AdminApp.modal('category-modal');
}

function saveCategory() {
    var id = document.getElementById('cat-id').value;
    var data = {
        name: document.getElementById('cat-name').value,
        parent_id: parseInt(document.getElementById('cat-parent').value) || 0,
        sort: parseInt(document.getElementById('cat-sort').value) || 0,
        status: parseInt(document.getElementById('cat-status').value),
    };
    if (!data.name) { AdminApp.toast('请填写分类名称', 'warning'); return; }

    var iconFile = document.getElementById('cat-icon-file').files[0];
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
        var url = id ? '/admin/category/' + id : '/admin/category';
        var method = id ? 'put' : 'post';
        AdminApp[method](url, data).then(function(res) {
            if (res.code === 200) {
                AdminApp.toast(res.message || '保存成功', 'success');
                AdminApp.closeModal('category-modal');
                loadCategories();
            } else {
                AdminApp.toast(res.message || '保存失败', 'error');
            }
        });
    });
}

function toggleCategoryStatus(id, currentStatus) {
    var newStatus = currentStatus == 1 ? 0 : 1;
    AdminApp.put('/admin/category/' + id, { status: newStatus }).then(function(res) {
        if (res.code === 200) {
            AdminApp.toast(res.message || '操作成功', 'success');
            loadCategories();
        } else {
            AdminApp.toast(res.message || '操作失败', 'error');
        }
    });
}

function deleteCategory(id, softwareCount) {
    var msg = '确定要删除此分类吗？';
    if (softwareCount > 0) {
        AdminApp.toast('该分类下有 ' + softwareCount + ' 个软件，无法删除', 'warning');
        return;
    }
    AdminApp.confirm(msg).then(function(ok) {
        if (!ok) return;
        AdminApp.del('/admin/category/' + id).then(function(res) {
            if (res.code === 200) {
                AdminApp.toast('删除成功', 'success');
                loadCategories();
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

loadCategories();
</script>

<?php require __DIR__ . '/footer.php'; ?>
