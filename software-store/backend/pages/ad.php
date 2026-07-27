<?php
$pageTitle = '广告管理';
$breadcrumb = '内容管理 / 广告配置';
require __DIR__ . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>开屏广告 (Splash Ad)</h3>
        <button class="btn btn-primary" onclick="openAdModal(1)">+ 新增开屏广告</button>
    </div>
    <div class="card-body">
        <div class="grid-view" id="splash-grid">
            <div class="empty-state" style="grid-column:1/-1;">加载中...</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>首页Banner广告</h3>
        <button class="btn btn-primary" onclick="openAdModal(2)">+ 新增Banner广告</button>
    </div>
    <div class="card-body">
        <div class="grid-view" id="banner-ad-grid">
            <div class="empty-state" style="grid-column:1/-1;">加载中...</div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="ad-modal">
    <div class="modal" style="max-width:550px;">
        <div class="modal-header">
            <h3 id="ad-modal-title">新增广告</h3>
            <button class="modal-close" onclick="AdminApp.closeModal('ad-modal')">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="ad-id">
            <input type="hidden" id="ad-type">
            <div class="form-group">
                <label>广告图片 *</label>
                <div class="image-uploader" onclick="document.getElementById('ad-image-file').click()">
                    <img id="ad-image-preview" class="preview" style="display:none;max-width:100%;max-height:200px;">
                    <div class="placeholder" id="ad-image-placeholder">点击上传广告图片</div>
                </div>
                <input type="file" id="ad-image-file" accept="image/*" style="display:none;" onchange="previewImage(this,'ad-image-preview','ad-image-placeholder')">
            </div>
            <div class="form-group">
                <label>标题</label>
                <input type="text" id="ad-title" class="form-control" placeholder="广告标题">
            </div>
            <div class="form-group">
                <label>跳转链接</label>
                <input type="text" id="ad-link" class="form-control" placeholder="https://...">
            </div>
            <div class="form-row">
                <div class="form-group" id="ad-duration-group">
                    <label>展示时长 (秒)</label>
                    <input type="number" id="ad-duration" class="form-control" min="1" max="30" value="3">
                </div>
                <div class="form-group">
                    <label>排序 (越大越靠前)</label>
                    <input type="number" id="ad-sort" class="form-control" value="0">
                </div>
            </div>
            <div class="form-group">
                <label>状态</label>
                <select id="ad-status" class="form-control">
                    <option value="1">启用</option>
                    <option value="0">禁用</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="AdminApp.closeModal('ad-modal')">取消</button>
            <button class="btn btn-primary" onclick="saveAd()">保存</button>
        </div>
    </div>
</div>

<script>
function loadAds() {
    AdminApp.get('/admin/ad').then(function(res) {
        if (res.code === 200) {
            var list = res.data || [];
            renderAdGroup(list, 'splash-grid', 1);
            renderAdGroup(list, 'banner-ad-grid', 2);
        }
    });
}

function renderAdGroup(list, gridId, type) {
    var grid = document.getElementById(gridId);
    var filtered = list.filter(function(item) {
        var pos = (item.position || '').toLowerCase();
        if (type === 1) return pos === 'splash';
        return pos !== 'splash';
    });

    if (!filtered.length) {
        grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><div class="icon">📢</div>暂无广告</div>';
        return;
    }

    grid.innerHTML = filtered.map(function(item) {
        var statusBadge = item.status == 1
            ? '<span class="badge badge-success">启用</span>'
            : '<span class="badge badge-default">禁用</span>';
        var img = item.image
            ? '<img src="' + AdminApp.escapeHtml(item.image) + '" class="thumb" alt="">'
            : '<div class="thumb" style="display:flex;align-items:center;justify-content:center;color:var(--text-muted);">无图</div>';
        return '<div class="grid-card">' +
            img +
            '<div class="info">' +
                '<h4>' + AdminApp.escapeHtml(item.title || '未命名') + '</h4>' +
                '<div class="meta">' +
                    statusBadge + ' · 排序: ' + item.sort +
                '</div>' +
                '<div style="margin-top:10px;display:flex;gap:6px;flex-wrap:wrap;">' +
                    '<button class="btn btn-sm btn-secondary" onclick="editAd(' + item.id + ')">编辑</button>' +
                    '<button class="btn btn-sm ' + (item.status == 1 ? 'btn-warning' : 'btn-success') + '" onclick="toggleAd(' + item.id + ')">' + (item.status == 1 ? '禁用' : '启用') + '</button>' +
                    '<button class="btn btn-sm btn-danger" onclick="deleteAd(' + item.id + ')">删除</button>' +
                '</div>' +
            '</div>' +
        '</div>';
    }).join('');
}

function openAdModal(type) {
    document.getElementById('ad-modal-title').textContent = type === 1 ? '新增开屏广告' : '新增Banner广告';
    document.getElementById('ad-id').value = '';
    document.getElementById('ad-type').value = type;
    document.getElementById('ad-title').value = '';
    document.getElementById('ad-link').value = '';
    document.getElementById('ad-sort').value = '0';
    document.getElementById('ad-status').value = '1';
    document.getElementById('ad-duration').value = '3';
    document.getElementById('ad-duration-group').style.display = type === 1 ? 'block' : 'none';
    document.getElementById('ad-image-preview').style.display = 'none';
    document.getElementById('ad-image-placeholder').style.display = 'block';
    document.getElementById('ad-image-file').value = '';
    AdminApp.modal('ad-modal');
}

function editAd(id) {
    AdminApp.get('/admin/ad/' + id).then(function(res) {
        if (res.code === 200) {
            var item = res.data;
            var pos = (item.position || '').toLowerCase();
            var type = pos === 'splash' ? 1 : 2;
            document.getElementById('ad-modal-title').textContent = (type === 1 ? '编辑开屏广告' : '编辑Banner广告');
            document.getElementById('ad-id').value = item.id;
            document.getElementById('ad-type').value = type;
            document.getElementById('ad-title').value = item.title || '';
            document.getElementById('ad-link').value = item.link || '';
            document.getElementById('ad-sort').value = item.sort;
            document.getElementById('ad-status').value = item.status;
            document.getElementById('ad-duration').value = item.duration || 3;
            document.getElementById('ad-duration-group').style.display = type === 1 ? 'block' : 'none';
            if (item.image) {
                var preview = document.getElementById('ad-image-preview');
                preview.src = item.image;
                preview.style.display = 'block';
                document.getElementById('ad-image-placeholder').style.display = 'none';
            }
            AdminApp.modal('ad-modal');
        }
    });
}

function saveAd() {
    var id = document.getElementById('ad-id').value;
    var type = parseInt(document.getElementById('ad-type').value);
    var data = {
        title: document.getElementById('ad-title').value,
        link: document.getElementById('ad-link').value,
        position: type === 1 ? 'splash' : 'home',
        sort: parseInt(document.getElementById('ad-sort').value) || 0,
        status: parseInt(document.getElementById('ad-status').value),
    };

    var imgFile = document.getElementById('ad-image-file').files[0];
    var handleFile = function(file) {
        return new Promise(function(resolve) {
            if (!file) resolve({});
            else {
                var reader = new FileReader();
                reader.onload = function(e) {
                    resolve({ image_file: { name: file.name, type: file.type, size: file.size, content: e.target.result } });
                };
                reader.readAsDataURL(file);
            }
        });
    };

    handleFile(imgFile).then(function(extra) {
        Object.assign(data, extra);
        var url = id ? '/admin/ad/' + id : '/admin/ad';
        var method = id ? 'put' : 'post';
        AdminApp[method](url, data).then(function(res) {
            if (res.code === 200) {
                AdminApp.toast(res.message || '保存成功', 'success');
                AdminApp.closeModal('ad-modal');
                loadAds();
            } else {
                AdminApp.toast(res.message || '保存失败', 'error');
            }
        });
    });
}

function toggleAd(id) {
    AdminApp.post('/admin/ad/' + id + '/toggle').then(function(res) {
        if (res.code === 200) {
            AdminApp.toast(res.message, 'success');
            loadAds();
        } else {
            AdminApp.toast(res.message || '操作失败', 'error');
        }
    });
}

function deleteAd(id) {
    AdminApp.confirm('确定要删除此广告吗？').then(function(ok) {
        if (!ok) return;
        AdminApp.del('/admin/ad/' + id).then(function(res) {
            if (res.code === 200) {
                AdminApp.toast('删除成功', 'success');
                loadAds();
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

loadAds();
</script>

<?php require __DIR__ . '/footer.php'; ?>
