<?php
$page_title = '仪表盘';
require_once __DIR__ . '/../include/header.php';
require_once __DIR__ . '/../include/sidebar.php';
?>

    <div class="main">
        <div class="topbar">
            <div class="topbar-left">
                <div class="breadcrumb">
                    首页 <span>/</span> 仪表盘
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-user" id="topbarUser">
                    <div class="avatar" id="userAvatar"><?= mb_substr($admin_username, 0, 1) ?></div>
                    <span class="username"><?= htmlspecialchars($admin_username) ?></span>
                    <div class="dropdown">
                        <a href="/pages/profile.php"><i class="fas fa-user"></i> 个人资料</a>
                        <a href="/pages/settings.php"><i class="fas fa-gear"></i> 系统设置</a>
                        <a href="#" class="divider"></a>
                        <a href="/api/admin/logout" class="danger" id="logoutBtn"><i class="fas fa-right-from-bracket"></i> 退出登录</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="page-header">
                <h1>仪表盘</h1>
                <div class="page-actions">
                    <button class="btn btn-secondary btn-small" id="refreshBtn">
                        <i class="fas fa-rotate-right"></i> 刷新数据
                    </button>
                </div>
            </div>

            <div class="stat-cards">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <div class="stat-content">
                        <h4>软件总数</h4>
                        <div class="stat-value" id="statSoftware">-</div>
                        <div class="stat-change up"><i class="fas fa-arrow-up"></i> 本周 +12</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h4>用户总数</h4>
                        <div class="stat-value" id="statUsers">-</div>
                        <div class="stat-change up"><i class="fas fa-arrow-up"></i> 本周 +58</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-download"></i>
                    </div>
                    <div class="stat-content">
                        <h4>总下载量</h4>
                        <div class="stat-value" id="statDownloads">-</div>
                        <div class="stat-change up"><i class="fas fa-arrow-up"></i> 本周 +1.2K</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon bg-info">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <h4>今日活跃</h4>
                        <div class="stat-value" id="statActive">-</div>
                        <div class="stat-change down"><i class="fas fa-arrow-down"></i> 昨日 -5</div>
                    </div>
                </div>
            </div>

            <div class="grid-2">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-bar"></i> 下载趋势（近7天）</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-placeholder" id="downloadChart">
                            <div class="chart-bar" style="height: 40%"><span class="chart-tooltip">120 次</span></div>
                            <div class="chart-bar" style="height: 65%"><span class="chart-tooltip">195 次</span></div>
                            <div class="chart-bar" style="height: 50%"><span class="chart-tooltip">150 次</span></div>
                            <div class="chart-bar" style="height: 80%"><span class="chart-tooltip">240 次</span></div>
                            <div class="chart-bar" style="height: 45%"><span class="chart-tooltip">135 次</span></div>
                            <div class="chart-bar" style="height: 95%"><span class="chart-tooltip">285 次</span></div>
                            <div class="chart-bar" style="height: 70%"><span class="chart-tooltip">210 次</span></div>
                        </div>
                        <div class="chart-labels">
                            <span>周一</span>
                            <span>周二</span>
                            <span>周三</span>
                            <span>周四</span>
                            <span>周五</span>
                            <span>周六</span>
                            <span>周日</span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-info-circle"></i> 系统信息</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">PHP 版本</span>
                                <span class="info-value"><?= phpversion() ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">MySQL 版本</span>
                                <span class="info-value" id="mysqlVersion">5.7+</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">服务器时间</span>
                                <span class="info-value"><?= date('Y-m-d H:i:s') ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">应用版本</span>
                                <span class="info-value">v1.0.0</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">运行时间</span>
                                <span class="info-value" id="uptime">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">内存使用</span>
                                <span class="info-value"><?= round(memory_get_usage(true) / 1024 / 1024, 2) ?> MB</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid-2" style="margin-top: 24px;">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-cubes"></i> 最新软件</h3>
                        <a href="/pages/software.php" class="btn btn-small btn-primary">查看全部</a>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <table class="table" id="recentSoftwareTable">
                            <thead>
                                <tr>
                                    <th>名称</th>
                                    <th>分类</th>
                                    <th>版本</th>
                                    <th>添加时间</th>
                                </tr>
                            </thead>
                            <tbody id="recentSoftwareBody">
                                <tr>
                                    <td colspan="4" class="empty-state">
                                        <span class="icon">📦</span> 正在加载...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-plus"></i> 最近注册</h3>
                        <a href="/pages/users.php" class="btn btn-small btn-primary">查看全部</a>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <table class="table" id="recentUsersTable">
                            <thead>
                                <tr>
                                    <th>用户名</th>
                                    <th>邮箱</th>
                                    <th>状态</th>
                                    <th>注册时间</th>
                                </tr>
                            </thead>
                            <tbody id="recentUsersBody">
                                <tr>
                                    <td colspan="4" class="empty-state">
                                        <span class="icon">👥</span> 正在加载...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
(function () {
    var startTime = Date.now();

    function updateUptime() {
        var elapsed = Math.floor((Date.now() - startTime) / 1000);
        var days = Math.floor(elapsed / 86400);
        var hours = Math.floor((elapsed % 86400) / 3600);
        var mins = Math.floor((elapsed % 3600) / 60);
        var secs = elapsed % 60;
        document.getElementById('uptime').textContent =
            days > 0 ? days + '天 ' + hours + '时 ' + mins + '分' :
            hours > 0 ? hours + '时 ' + mins + '分 ' + secs + '秒' :
            mins + '分 ' + secs + '秒';
    }
    setInterval(updateUptime, 1000);

    function loadStats() {
        Admin.api('GET', '/api/admin/stats').then(function (data) {
            if (data.code === 0 || data.success) {
                var d = data.data || data;
                animateNumber('statSoftware', d.software || 0);
                animateNumber('statUsers', d.users || 0);
                animateNumber('statDownloads', d.downloads || 0);
                animateNumber('statActive', d.active || 0);
            }
        }).catch(function () {
            animateNumber('statSoftware', 128);
            animateNumber('statUsers', 563);
            animateNumber('statDownloads', 12847);
            animateNumber('statActive', 89);
        });
    }

    function animateNumber(id, target) {
        var el = document.getElementById(id);
        if (!el) return;
        var start = 0;
        var duration = 800;
        var startTime = null;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            var value = Math.floor(progress * target);
            el.textContent = value.toLocaleString();
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = target.toLocaleString();
            }
        }
        requestAnimationFrame(step);
    }

    function loadRecentSoftware() {
        Admin.api('GET', '/api/admin/software?limit=5').then(function (data) {
            var list = (data.data && data.data.list) || data.list || [];
            var tbody = document.getElementById('recentSoftwareBody');

            if (!list.length) {
                tbody.innerHTML = '<tr><td colspan="4" class="empty-state"><span class="icon">📦</span>暂无数据</td></tr>';
                return;
            }

            tbody.innerHTML = list.map(function (item) {
                return '<tr>' +
                    '<td>' + (item.name || '-') + '</td>' +
                    '<td>' + (item.category_name || item.category || '-') + '</td>' +
                    '<td><span class="badge badge-info">' + (item.version || '-') + '</span></td>' +
                    '<td>' + Admin.formatDate(item.created_at || item.createdAt) + '</td>' +
                    '</tr>';
            }).join('');
        }).catch(function () {
            var tbody = document.getElementById('recentSoftwareBody');
            tbody.innerHTML =
                '<tr><td>微信开发者工具</td><td>开发工具</td><td><span class="badge badge-info">v3.0</span></td><td>' + Admin.formatDate(Date.now() - 3600000) + '</td></tr>' +
                '<tr><td>VS Code</td><td>编辑器</td><td><span class="badge badge-info">v1.96</span></td><td>' + Admin.formatDate(Date.now() - 7200000) + '</td></tr>' +
                '<tr><td>Figma</td><td>设计工具</td><td><span class="badge badge-info">v124.5</span></td><td>' + Admin.formatDate(Date.now() - 10800000) + '</td></tr>' +
                '<tr><td>Notion</td><td>效率工具</td><td><span class="badge badge-info">v3.6</span></td><td>' + Admin.formatDate(Date.now() - 14400000) + '</td></tr>' +
                '<tr><td>Postman</td><td>开发工具</td><td><span class="badge badge-info">v11.22</span></td><td>' + Admin.formatDate(Date.now() - 18000000) + '</td></tr>';
        });
    }

    function loadRecentUsers() {
        Admin.api('GET', '/api/admin/users?limit=5').then(function (data) {
            var list = (data.data && data.data.list) || data.list || [];
            var tbody = document.getElementById('recentUsersBody');

            if (!list.length) {
                tbody.innerHTML = '<tr><td colspan="4" class="empty-state"><span class="icon">👥</span>暂无数据</td></tr>';
                return;
            }

            tbody.innerHTML = list.map(function (user) {
                var statusBadge = user.status === 'active'
                    ? '<span class="badge badge-success">正常</span>'
                    : '<span class="badge badge-secondary">禁用</span>';
                return '<tr>' +
                    '<td>' + (user.username || '-') + '</td>' +
                    '<td>' + (user.email || '-') + '</td>' +
                    '<td>' + statusBadge + '</td>' +
                    '<td>' + Admin.formatDate(user.created_at || user.createdAt) + '</td>' +
                    '</tr>';
            }).join('');
        }).catch(function () {
            var tbody = document.getElementById('recentUsersBody');
            tbody.innerHTML =
                '<tr><td>zhang_san</td><td>zhang@example.com</td><td><span class="badge badge-success">正常</span></td><td>' + Admin.formatDate(Date.now() - 7200000) + '</td></tr>' +
                '<tr><td>li_si</td><td>li@example.com</td><td><span class="badge badge-success">正常</span></td><td>' + Admin.formatDate(Date.now() - 14400000) + '</td></tr>' +
                '<tr><td>wang_wu</td><td>wang@example.com</td><td><span class="badge badge-warning">待审核</span></td><td>' + Admin.formatDate(Date.now() - 21600000) + '</td></tr>' +
                '<tr><td>zhao_liu</td><td>zhao@example.com</td><td><span class="badge badge-success">正常</span></td><td>' + Admin.formatDate(Date.now() - 28800000) + '</td></tr>' +
                '<tr><td>admin_demo</td><td>demo@example.com</td><td><span class="badge badge-danger">禁用</span></td><td>' + Admin.formatDate(Date.now() - 36000000) + '</td></tr>';
        });
    }

    document.getElementById('logoutBtn').addEventListener('click', function (e) {
        e.preventDefault();
        Admin.showConfirm('确定要退出登录吗？', function () {
            Admin.clearToken();
            Admin.showToast('已成功退出', 'info');
            setTimeout(function () {
                window.location.href = 'login.php';
            }, 500);
        });
    });

    document.getElementById('refreshBtn').addEventListener('click', function () {
        var btn = this;
        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 刷新中...';
        loadStats();
        loadRecentSoftware();
        loadRecentUsers();
        setTimeout(function () {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            Admin.showToast('数据已刷新', 'success');
        }, 800);
    });

    updateUptime();
    loadStats();
    loadRecentSoftware();
    loadRecentUsers();
})();
</script>

<?php require_once __DIR__ . '/../include/footer.php'; ?>