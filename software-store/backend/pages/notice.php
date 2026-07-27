<?php
$pageTitle = '公告管理';
$breadcrumb = '内容管理 / 公告与声明';
require __DIR__ . '/header.php';
?>

<div class="card">
    <div class="card-body">
        <div class="tab-nav">
            <div class="tab-item active" data-type="1" onclick="switchTab(1)">滚动公告</div>
            <div class="tab-item" data-type="2" onclick="switchTab(2)">公告声明</div>
            <div class="tab-item" data-type="3" onclick="switchTab(3)">常见问题</div>
        </div>

        <div id="tab-content">
            <div id="type-1-panel">
                <div class="form-group">
                    <label>滚动公告内容</label>
                    <textarea id="notice-1-content" class="form-control" rows="3" placeholder="输入滚动公告内容，在App首页顶部滚动显示..."></textarea>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span id="save-hint-1" style="color:var(--text-muted);font-size:0.85rem;"></span>
                    <button class="btn btn-primary" onclick="saveNotice(1)">保存</button>
                </div>
            </div>

            <div id="type-2-panel" style="display:none;">
                <div class="form-group">
                    <label>公告声明标题</label>
                    <input type="text" id="notice-2-title" class="form-control" placeholder="公告声明标题">
                </div>
                <div class="form-group">
                    <label>公告声明内容</label>
                    <textarea id="notice-2-content" class="form-control" rows="10" placeholder="请输入公告声明的详细内容..."></textarea>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span id="save-hint-2" style="color:var(--text-muted);font-size:0.85rem;"></span>
                    <button class="btn btn-primary" onclick="saveNotice(2)">保存</button>
                </div>
            </div>

            <div id="type-3-panel" style="display:none;">
                <div class="form-group">
                    <label>常见问题标题</label>
                    <input type="text" id="notice-3-title" class="form-control" placeholder="常见问题标题">
                </div>
                <div class="form-group">
                    <label>常见问题内容</label>
                    <textarea id="notice-3-content" class="form-control" rows="12" placeholder="请输入常见问题解答内容，支持基本格式..."></textarea>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span id="save-hint-3" style="color:var(--text-muted);font-size:0.85rem;"></span>
                    <button class="btn btn-primary" onclick="saveNotice(3)">保存</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var currentType = 1;
var noticesCache = {};

function switchTab(type) {
    currentType = type;
    document.querySelectorAll('.tab-item').forEach(function(el) {
        el.classList.toggle('active', parseInt(el.dataset.type) === type);
    });
    ['1','2','3'].forEach(function(t) {
        document.getElementById('type-' + t + '-panel').style.display = (t == type) ? 'block' : 'none';
    });
    loadNotice(type);
}

function loadNotice(type) {
    AdminApp.get('/admin/notice?type=' + type).then(function(res) {
        if (res.code === 200) {
            var list = res.data || [];
            var item = list.find(function(n) { return n.type == type; });
            if (item) {
                noticesCache[type] = item;
                if (type == 1) {
                    document.getElementById('notice-1-content').value = item.content || '';
                } else {
                    document.getElementById('notice-' + type + '-title').value = item.title || '';
                    document.getElementById('notice-' + type + '-content').value = item.content || '';
                }
            } else {
                if (type == 1) {
                    document.getElementById('notice-1-content').value = '';
                } else {
                    document.getElementById('notice-' + type + '-title').value = '';
                    document.getElementById('notice-' + type + '-content').value = '';
                }
            }
            updateSaveHint(type, '数据已加载');
        }
    });
}

function saveNotice(type) {
    var data = {
        type: type,
        title: '',
        content: '',
        status: 1,
    };

    if (type == 1) {
        data.title = '滚动公告';
        data.content = document.getElementById('notice-1-content').value;
    } else {
        data.title = document.getElementById('notice-' + type + '-title').value;
        data.content = document.getElementById('notice-' + type + '-content').value;
    }

    var existing = noticesCache[type];
    var url = existing ? '/admin/notice/' + existing.id : '/admin/notice';
    var method = existing ? 'put' : 'post';

    if (!data.content) {
        AdminApp.toast('内容不能为空', 'warning');
        return;
    }

    AdminApp[method](url, data).then(function(res) {
        if (res.code === 200) {
            AdminApp.toast(res.message || '保存成功', 'success');
            if (res.data && res.data.id) {
                loadNotice(type);
            }
            updateSaveHint(type, '保存成功 ' + new Date().toLocaleTimeString());
        } else {
            AdminApp.toast(res.message || '保存失败', 'error');
        }
    });
}

function updateSaveHint(type, msg) {
    var hint = document.getElementById('save-hint-' + type);
    if (hint) hint.textContent = msg;
}

document.querySelectorAll('#notice-1-content, #notice-2-content, #notice-3-content').forEach(function(el) {
    var type = el.id.replace('notice-', '').replace('-content', '');
    var timer;
    el.addEventListener('input', function() {
        updateSaveHint(parseInt(type), '编辑中...');
        clearTimeout(timer);
        timer = setTimeout(function() {
            updateSaveHint(parseInt(type), '可保存');
        }, 500);
    });
});

switchTab(1);
</script>

<?php require __DIR__ . '/footer.php'; ?>
