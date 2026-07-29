<?php
/**
 * 仪表盘页
 * 统计卡片 + 趋势图 + 下载排行 + 系统信息
 */
$pageTitle = '仪表盘 - 软件库后台';
$currentPage = 'dashboard.php';
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
    <!-- 侧边栏 -->
    <aside class="sidebar">
        <div class="sidebar-brand"><span class="brand-icon"><i class="fas fa-cube"></i></span>软件库后台</div>
        <ul class="sidebar-menu">
            <li class="menu-section">主菜单</li>
            <li class="menu-item active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>仪表盘</span></a></li>
            <li class="menu-item"><a href="software.php"><i class="fas fa-box"></i><span>软件管理</span></a></li>
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

    <!-- 主内容 -->
    <div class="main-content">
        <nav class="topbar">
            <div class="topbar-left">
                <button class="topbar-toggle"><i class="fas fa-bars"></i></button>
                <span class="page-title">仪表盘</span>
            </div>
            <div class="topbar-right">
                <button class="topbar-icon-btn"><i class="fas fa-bell"></i><span class="badge-dot"></span></button>
                <div class="topbar-user">
                    <div class="user-avatar">A</div>
                    <div class="user-info">
                        <div class="user-name">管理员</div>
                        <div class="user-role">超级管理员</div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="content-body fade-in">
            <!-- 统计卡片 -->
            <div class="row g-3 mb-2" id="statCards">
                <div class="col-6 col-xl-2 col-lg-3 col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-blue"><i class="fas fa-box"></i></div>
                        <div class="stat-info"><div class="stat-label">软件总数</div><div class="stat-value" id="stat-software">-</div></div>
                    </div>
                </div>
                <div class="col-6 col-xl-2 col-lg-3 col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-purple"><i class="fas fa-users"></i></div>
                        <div class="stat-info"><div class="stat-label">用户总数</div><div class="stat-value" id="stat-user">-</div></div>
                    </div>
                </div>
                <div class="col-6 col-xl-2 col-lg-3 col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-green"><i class="fas fa-download"></i></div>
                        <div class="stat-info"><div class="stat-label">累计下载</div><div class="stat-value" id="stat-download">-</div></div>
                    </div>
                </div>
                <div class="col-6 col-xl-2 col-lg-3 col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-cyan"><i class="fas fa-th-large"></i></div>
                        <div class="stat-info"><div class="stat-label">分类总数</div><div class="stat-value" id="stat-category">-</div></div>
                    </div>
                </div>
                <div class="col-6 col-xl-2 col-lg-3 col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-orange"><i class="fas fa-user-plus"></i></div>
                        <div class="stat-info"><div class="stat-label">今日新增</div><div class="stat-value" id="stat-today-register">-</div></div>
                    </div>
                </div>
                <div class="col-6 col-xl-2 col-lg-3 col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-red"><i class="fas fa-exclamation-circle"></i></div>
                        <div class="stat-info"><div class="stat-label">待处理反馈</div><div class="stat-value" id="stat-feedback">-</div></div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <!-- 趋势图 -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5><i class="fas fa-chart-line text-primary-custom"></i> 近 7 天数据趋势</h5></div>
                        <div class="card-body">
                            <div class="chart-container">
                                <div class="chart-bars" id="chartBars"></div>
                                <div class="chart-legend">
                                    <span><span class="legend-dot blue"></span>下载量</span>
                                    <span><span class="legend-dot purple"></span>注册量</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 下载排行 -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h5><i class="fas fa-trophy text-primary-custom"></i> 下载排行 Top5</h5></div>
                        <div class="card-body" id="rankList"></div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <!-- 系统信息 -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h5><i class="fas fa-server text-primary-custom"></i> 系统信息</h5></div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm mb-0">
                                <tbody>
                                    <tr><td class="text-muted" style="width:140px;">系统版本</td><td>v2.0.0</td></tr>
                                    <tr><td class="text-muted">运行环境</td><td>PHP <?= htmlspecialchars(PHP_VERSION) ?></td></tr>
                                    <tr><td class="text-muted">服务器时间</td><td id="serverTime"><?= date('Y-m-d H:i:s') ?></td></tr>
                                    <tr><td class="text-muted">客户端 IP</td><td><?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0') ?></td></tr>
                                    <tr><td class="text-muted">数据库名</td><td>software_store</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 快捷入口 -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h5><i class="fas fa-bolt text-primary-custom"></i> 快捷入口</h5></div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-6 col-md-4 mb-2"><a href="software.php" class="btn btn-outline-primary w-100"><i class="fas fa-box me-1"></i>软件管理</a></div>
                                <div class="col-6 col-md-4 mb-2"><a href="category.php" class="btn btn-outline-primary w-100"><i class="fas fa-th-large me-1"></i>分类管理</a></div>
                                <div class="col-6 col-md-4 mb-2"><a href="banner.php" class="btn btn-outline-primary w-100"><i class="fas fa-image me-1"></i>轮播图</a></div>
                                <div class="col-6 col-md-4 mb-2"><a href="notice.php" class="btn btn-outline-primary w-100"><i class="fas fa-bullhorn me-1"></i>公告管理</a></div>
                                <div class="col-6 col-md-4 mb-2"><a href="user.php" class="btn btn-outline-primary w-100"><i class="fas fa-users me-1"></i>用户管理</a></div>
                                <div class="col-6 col-md-4 mb-2"><a href="setting.php" class="btn btn-outline-primary w-100"><i class="fas fa-cog me-1"></i>系统设置</a></div>
                            </div>
                        </div>
                    </div>
                </div>
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

    // 加载仪表盘数据
    AdminApp.Api.get('dashboard').then(function (res) {
        if (res.code !== 200) {
            AdminApp.UI.toast(res.message || '数据加载失败', 'error');
            return;
        }
        var d = res.data || {};
        $('#stat-software').text(d.software_count || 0);
        $('#stat-user').text(d.user_count || 0);
        $('#stat-download').text(AdminApp.UI.formatNumber(d.download_count || 0));
        $('#stat-category').text(d.category_count || 0);
        $('#stat-today-register').text(d.today_register || 0);
        $('#stat-feedback').text(d.feedback_pending || 0);

        // 渲染趋势图
        renderChart(d.download_trend || [], d.register_trend || []);
        // 渲染排行
        renderRank(d.download_rank || []);
    }).fail(function (err) {
        AdminApp.UI.toast(err.message || '数据加载失败', 'error');
    });

    // 时钟
    setInterval(function () {
        var now = new Date();
        $('#serverTime').text(now.getFullYear() + '-' +
            String(now.getMonth() + 1).padStart(2, '0') + '-' +
            String(now.getDate()).padStart(2, '0') + ' ' +
            String(now.getHours()).padStart(2, '0') + ':' +
            String(now.getMinutes()).padStart(2, '0') + ':' +
            String(now.getSeconds()).padStart(2, '0'));
    }, 1000);

    // 趋势图渲染
    function renderChart(downloadTrend, registerTrend) {
        var $bars = $('#chartBars');
        $bars.empty();
        if (downloadTrend.length === 0 && registerTrend.length === 0) {
            $bars.html('<div class="empty-state"><i class="fas fa-chart-bar"></i><p>暂无趋势数据</p></div>');
            return;
        }
        // 合并日期
        var dateMap = {};
        downloadTrend.forEach(function (item) { dateMap[item.date] = { download: parseInt(item.count, 10) || 0, register: 0 }; });
        registerTrend.forEach(function (item) {
            if (!dateMap[item.date]) dateMap[item.date] = { download: 0, register: 0 };
            dateMap[item.date].register = parseInt(item.count, 10) || 0;
        });
        var dates = Object.keys(dateMap).sort();
        var maxVal = 1;
        dates.forEach(function (date) { maxVal = Math.max(maxVal, dateMap[date].download, dateMap[date].register); });

        dates.forEach(function (date) {
            var item = dateMap[date];
            var dH = Math.max(4, (item.download / maxVal) * 200);
            var rH = Math.max(4, (item.register / maxVal) * 200);
            var label = date.substring(5); // MM-DD
            var html = '<div class="chart-bar-group">' +
                '<div style="display:flex;gap:4px;align-items:flex-end;height:200px;">' +
                '<div class="chart-bar" style="height:' + dH + 'px" title="下载 ' + item.download + '"><span class="chart-bar-value">' + item.download + '</span></div>' +
                '<div class="chart-bar purple" style="height:' + rH + 'px" title="注册 ' + item.register + '"><span class="chart-bar-value">' + item.register + '</span></div>' +
                '</div><div class="chart-bar-label">' + label + '</div></div>';
            $bars.append(html);
        });
    }

    // 排行渲染
    function renderRank(list) {
        var $list = $('#rankList');
        if (list.length === 0) {
            $list.html('<div class="empty-state"><i class="fas fa-trophy"></i><p>暂无数据</p></div>');
            return;
        }
        var colors = ['#f59e0b', '#94a3b8', '#b45309', '#cbd5e1', '#cbd5e1'];
        var html = '';
        list.forEach(function (item, idx) {
            var color = colors[idx] || '#cbd5e1';
            html += '<div class="d-flex align-items-center py-2 ' + (idx < list.length - 1 ? 'border-bottom' : '') + '">' +
                '<div style="width:26px;height:26px;border-radius:50%;background:' + color + ';color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:600;font-size:13px;flex-shrink:0;">' + (idx + 1) + '</div>' +
                '<div class="ms-3 flex-grow-1">' +
                '<div class="fw-medium">' + AdminApp.UI.escape(item.name) + '</div>' +
                '<div class="text-muted" style="font-size:12px;">' + AdminApp.UI.formatNumber(item.download_count) + ' 次下载</div>' +
                '</div></div>';
        });
        $list.html(html);
    }
});
</script>
</body>
</html>
