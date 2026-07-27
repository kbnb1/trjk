<?php
$pageTitle = '系统设置';
$breadcrumb = '用户与系统 / 系统设置';
require __DIR__ . '/header.php';
?>

<div class="card">
    <div class="card-header"><h3>站点设置</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label>站点名称</label>
            <input type="text" id="cfg-site-name" class="form-control" placeholder="软件商店">
        </div>
        <div class="form-group">
            <label>站点Logo</label>
            <div class="image-uploader" onclick="document.getElementById('cfg-logo-file').click()" style="max-width:200px;">
                <img id="cfg-logo-preview" class="preview" style="display:none;max-width:100%;max-height:120px;">
                <div class="placeholder" id="cfg-logo-placeholder">点击上传Logo</div>
            </div>
            <input type="file" id="cfg-logo-file" accept="image/*" style="display:none;" onchange="previewImage(this,'cfg-logo-preview','cfg-logo-placeholder')">
        </div>
        <div class="form-group">
            <label>客服信息</label>
            <textarea id="cfg-contact-info" class="form-control" rows="3" placeholder="客服电话、邮箱、工作时间等"></textarea>
        </div>
        <button class="btn btn-primary" onclick="saveSection('site')">保存站点设置</button>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>注册设置</h3></div>
    <div class="card-body">
        <div class="form-group" style="display:flex;align-items:center;gap:12px;">
            <label style="flex:1;">启用手机验证码</label>
            <label class="switch">
                <input type="checkbox" id="cfg-phone-verify">
                <span class="slider"></span>
            </label>
        </div>
        <div class="form-group" style="display:flex;align-items:center;gap:12px;">
            <label style="flex:1;">启用邮箱验证码</label>
            <label class="switch">
                <input type="checkbox" id="cfg-email-verify">
                <span class="slider"></span>
            </label>
        </div>
        <button class="btn btn-primary" onclick="saveSection('register')">保存注册设置</button>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>下载设置</h3></div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <label>App版本号</label>
                <input type="text" id="cfg-app-version" class="form-control" placeholder="如 1.0.0">
            </div>
            <div class="form-group">
                <label>强制更新</label>
                <label class="switch">
                    <input type="checkbox" id="cfg-force-update">
                    <span class="slider"></span>
                </label>
            </div>
        </div>
        <div class="form-group">
            <label>App下载地址</label>
            <input type="text" id="cfg-app-download-url" class="form-control" placeholder="https://...">
        </div>
        <button class="btn btn-primary" onclick="saveSection('download')">保存下载设置</button>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>上传设置</h3></div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <label>最大上传大小 (MB)</label>
                <input type="number" id="cfg-max-upload-size" class="form-control" min="1" max="1024" value="50">
            </div>
            <div class="form-group">
                <label>允许的图片类型</label>
                <input type="text" id="cfg-image-types" class="form-control" placeholder="jpg,png,gif,webp">
            </div>
        </div>
        <div class="form-group">
            <label>允许的APK/文件类型</label>
            <input type="text" id="cfg-apk-types" class="form-control" placeholder="apk,ipa,zip">
        </div>
        <button class="btn btn-primary" onclick="saveSection('upload')">保存上传设置</button>
    </div>
</div>

<script>
var configData = {};

function loadConfig() {
    AdminApp.get('/admin/config').then(function(res) {
        if (res.code === 200) {
            configData = res.data || {};
            renderConfig();
        }
    });
}

function renderConfig() {
    var site = configData.site || {};
    document.getElementById('cfg-site-name').value = site.site_name || '';
    if (site.logo) {
        var preview = document.getElementById('cfg-logo-preview');
        preview.src = site.logo;
        preview.style.display = 'block';
        document.getElementById('cfg-logo-placeholder').style.display = 'none';
    }
    document.getElementById('cfg-contact-info').value = site.contact_info || '';

    var reg = configData.register || {};
    document.getElementById('cfg-phone-verify').checked = reg.enable_phone_verify == 1;
    document.getElementById('cfg-email-verify').checked = reg.enable_email_verify == 1;

    var dl = configData.download || {};
    document.getElementById('cfg-app-version').value = dl.app_version || '';
    document.getElementById('cfg-app-download-url').value = dl.app_download_url || '';
    document.getElementById('cfg-force-update').checked = dl.force_update == 1;

    var up = configData.upload || {};
    document.getElementById('cfg-max-upload-size').value = up.max_upload_size || 50;
    document.getElementById('cfg-image-types').value = up.allowed_image_types || 'jpg,png,gif,webp';
    document.getElementById('cfg-apk-types').value = up.allowed_apk_types || 'apk';
}

function saveSection(section) {
    var data = {};

    if (section === 'site') {
        data.site_name = document.getElementById('cfg-site-name').value;
        data.contact_info = document.getElementById('cfg-contact-info').value;

        var logoFile = document.getElementById('cfg-logo-file').files[0];
        var handleLogo = function(file) {
            return new Promise(function(resolve) {
                if (!file) { resolve({}); return; }
                var reader = new FileReader();
                reader.onload = function(e) {
                    resolve({ logo_file: { name: file.name, type: file.type, size: file.size, content: e.target.result } });
                };
                reader.readAsDataURL(file);
            });
        };

        handleLogo(logoFile).then(function(extra) {
            Object.assign(data, extra);
            AdminApp.post('/admin/config', { site: data }).then(function(res) {
                if (res.code === 200) {
                    AdminApp.toast('站点设置已保存', 'success');
                    loadConfig();
                } else {
                    AdminApp.toast(res.message || '保存失败', 'error');
                }
            });
        });
        return;
    }

    if (section === 'register') {
        data.enable_phone_verify = document.getElementById('cfg-phone-verify').checked ? 1 : 0;
        data.enable_email_verify = document.getElementById('cfg-email-verify').checked ? 1 : 0;
    }

    if (section === 'download') {
        data.app_version = document.getElementById('cfg-app-version').value;
        data.app_download_url = document.getElementById('cfg-app-download-url').value;
        data.force_update = document.getElementById('cfg-force-update').checked ? 1 : 0;
    }

    if (section === 'upload') {
        data.max_upload_size = parseInt(document.getElementById('cfg-max-upload-size').value) || 50;
        data.allowed_image_types = document.getElementById('cfg-image-types').value;
        data.allowed_apk_types = document.getElementById('cfg-apk-types').value;
    }

    AdminApp.post('/admin/config', { [section]: data }).then(function(res) {
        if (res.code === 200) {
            AdminApp.toast('保存成功', 'success');
            loadConfig();
        } else {
            AdminApp.toast(res.message || '保存失败', 'error');
        }
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

loadConfig();
</script>

<?php require __DIR__ . '/footer.php'; ?>
