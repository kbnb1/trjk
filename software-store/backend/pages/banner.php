<?php
$pageTitle = 'Banner管理';
$breadcrumb = '内容管理 / Banner列表';
$headerActions = '<button class="btn btn-primary" onclick="openBannerModal()">+ 新增Banner</button>';
require __DIR__ . '/header.php';
?>

<div class="card">
    <div class="card-body">
        <div class="search-bar" style="margin-bottom:16px;">
            <select id="banner-status-filter" class="form-control" style="min-width:120px;" onchange="loadBanners()">
                <option value="">全部状态</option>
                <option value="1">显示中</option>
                <option value="0">已隐藏</option>
            </select>
        </div>
        <div class="grid-view" id="banner-grid">
            <div class="empty-state" style="grid-column:1/-1;">加载中...</div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="banner-modal">
    <div class="modal" style="max-width:550px;">
        <div class="modal-header">
            <h3 id="banner-modal-title">新增Banner</h3>
            <button class="modal-close" onclick="AdminApp.closeModal('banner-modal')">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="banner-id">
            <div class="form-group">
                <label>Banner图片 *</label>
                <div class="image-uploader" onclick="document.getElementById('banner-image-file').click()">
                    <img id="banner-image-preview" class="preview" style="display:none;max-width:100%;max-height:200px;">
                    <div class="placeholder" id="banner-image-placeholder">点击上传Banner图片 (建议尺寸: 宽屏)</div>
                </div>
                <input type="file" id="banner-image-file" accept="image/*" style="display:none;" onchange="previewImage(this,'banner-image-preview','banner-image-placeholder')">
            </div>
            <div class="form-group">
                <label>标题</label>
                <input type="text" id="banner-title" class="form-control" placeholder="Banner标题">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>跳转链接</label>
                    <input type="text" id="banner-link" class="form-control" placeholder="https://...">
                </div>
                <div class="form-group">
                    <label>位置</label>
                    <select id="banner-position" class="form-control">
                        <option value="home">首页</option>
                        <option value="splash">开屏</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>排序 (越大越靠前)</label>
                    <input type="number" id="banner-sort" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label>状态</label>
                    <select id="banner-status" class="form-control">
                        <option value="1">显示</option>
                        <option value="0">隐藏</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="AdminApp.closeModal('banner-modal')">取消</button>
            <button class="btn btn-primary" onclick="saveBanner()">保存</button>
        </div>
    </div>
</div>

<script>
function loadBanners() {
    var status = document.getElementById('banner-status-filter').value;
    var url = '/admin/banner';
    if (status !== '') url += '?status=' + status;

    AdminApp.get(url).then(function(res) {
        if (res.code === 200) {
            renderBanners(res.data || []);
        }
    });
}

function renderBanners(list) {
    var grid = document.getElementById('banner-grid');
    if (!list.length) {
        grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><div class="icon">🖼️</div>暂无Banner，点击右上角添加</div>';
        return;
    }
    grid.innerHTML = list.map(function(item) {
        var statusBadge = item.status == 1
            ? '<span class="badge badge-success">显示中</span>'
            : '<span class="badge badge-default">已隐藏</span>';
        var img = item.image
            ? '<img src="' + AdminApp.escapeHtml(item.image) + '" class="thumb" alt="">'
            : '<div class="thumb" style="display:flex;align-items:center;justify-content:center;color:var(--text-muted);">无图</div>';
        return '<div class="grid-card">' +
            img +
            '<div class="info">' +
                '<h4>' + AdminApp.escapeHtml(item.title || '未命名') + '</h4>' +
                '<div class="meta">' +
                    statusBadge +
                    ' · 排序: ' + item.sort +
                    ' · ' + AdminApp.escapeHtml(item.position || 'home') +
                '</div>' +
                '<div style="margin-top:10px;display:flex;gap:6px;flex-wrap:wrap;">' +
                    '<button class="btn btn-sm btn-secondary" onclick="editBanner(' + item.id + ')">编辑</button>' +
                    '<button class="btn btn-sm ' + (item.status == 1 ? 'btn-warning' : 'btn-success') + '" onclick="toggleBanner(' + item.id + ', ' + item.status + ')">' + (item.status == 1 ? '隐藏' : '显示') + '</button>' +
                    '<button class="btn btn-sm btn-danger" onclick="deleteBanner(' + item.id + ')">删除</button>' +
                '</div>' +
            '</div>' +
        '</div>';
    }).join('');
}

function openBannerModal() {
    document.getElementById('banner-modal-title').textContent = '新增Banner';
    ['banner-id','banner-title','banner-link'].forEach(function(id) { document.getElementById(id).value = ''; });
    document.getElementById('banner-position').value = 'home';
    document.getElementById('banner-sort').value = '0';
    document.getElementById('banner-status').value = '1';
    document.getElementById('banner-image-preview').style.display = 'none';
    document.getElementById('banner-image-placeholder').style.display = 'block';
    document.getElementById('banner-image-file').value = '';
    AdminApp.modal('banner-modal');
}

function editBanner(id) {
    AdminApp.get('/admin/banner').then(function(res) {
        if (res.code === 200) {
            var item = (res.data || []).find(function(b) { return b.id === id; });
            if (!item) return;
            document.getElementById('banner-modal-title').textContent = '编辑Banner';
            document.getElementById('banner-id').value = item.id;
            document.getElementById('banner-title').value = item.title || '';
            document.getElementById('banner-link').value = item.link_value || '';
            document.getElementById('banner-position').value = item.position || 'home';
            document.getElementById('banner-sort').value = item.sort;
            document.getElementById('banner-status').value = item.status;
            if (item.image) {
                var preview = document.getElementById('banner-image-preview');
                preview.src = item.image;
                preview.style.display = 'block';
                document.getElementById('banner-image-placeholder').style.display = 'none';
            }
            AdminApp.modal('banner-modal');
        }
    });
}

function saveBanner() {
    var id = document.getElementById('banner-id').value;
    var data = {
        title: document.getElementById('banner-title').value,
        link_value: document.getElementById('banner-link').value,
        position: document.getElementById('banner-position').value,
        sort: parseInt(document.getElementById('banner-sort').value) || 0,
        status: parseInt(document.getElementById('banner-status').value),
    };
    var imgFile = document.getElementById('banner-image-file').files[0];

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
        var url = id ? '/admin/banner/' + id : '/admin/banner';
        var method = id ? 'put' : 'post';
        AdminApp[method](url, data).then(function(res) {
            if (res.code === 200) {
                AdminApp.toast(res.message || '保存成功', 'success');
                AdminApp.closeModal('banner-modal');
                loadBanners();
            } else {
                AdminApp.toast(res.message || '保存失败', 'error');
            }
        });
    });
}

function toggleBanner(id, status) {
    AdminApp.post('/admin/banner/' + id + '/toggle').then(function(res) {
        if (res.code === 200) {
            AdminApp.toast(res.message, 'success');
            loadBanners();
        } else {
            AdminApp.toast(res.message || '操作失败', 'error');
        }
    });
}

function deleteBanner(id) {
    AdminApp.confirm('确定要删除此Banner吗？').then(function(ok) {
        if (!ok) return;
        AdminApp.del('/admin/banner/' + id).then(function(res) {
            if (res.code === 200) {
                AdminApp.toast('删除成功', 'success');
                loadBanners();
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

loadBanners();
</script>

<?php require __DIR__ . '/footer.php'; ?>
