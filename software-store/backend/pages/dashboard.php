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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        @keyframes slideInLeft {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .stream-container {
            max-height: 360px;
            overflow-y: auto;
        }
        .stream-container::-webkit-scrollbar { width: 4px; }
        .stream-container::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 2px; }
        .pulse-dot {
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #10b981;
            margin-right: 6px;
            animation: pulse 1.5s ease infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.3); }
        }
        .chart-canvas-wrapper {
            position: relative;
            height: 220px;
        }
    </style>
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

            <!-- 可视化图表：饼图 / 折线图 / 柱状图 -->
            <div class="row g-3 mb-2">
                <!-- 分类分布饼图 -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h5><i class="fas fa-chart-pie text-primary-custom"></i> 软件分类分布</h5></div>
                        <div class="card-body">
                            <div class="chart-canvas-wrapper">
                                <canvas id="categoryChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 用户增长趋势折线图 -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h5><i class="fas fa-chart-line text-success-custom"></i> 用户增长趋势</h5></div>
                        <div class="card-body">
                            <div class="chart-canvas-wrapper">
                                <canvas id="userGrowthChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 下载来源分析柱状图 -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h5><i class="fas fa-chart-bar text-secondary-custom"></i> 下载来源分析</h5></div>
                        <div class="card-body">
                            <div class="chart-canvas-wrapper">
                                <canvas id="downloadSourceChart" height="200"></canvas>
                            </div>
                        </div>
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

            <!-- 用户活跃热力图 / 软件版本分布 -->
            <div class="row g-3 mt-1">
                <!-- 用户活跃热力图 -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h5><i class="fas fa-fire text-warning"></i> 用户活跃热力图</h5></div>
                        <div class="card-body">
                            <div id="heatmapContainer"></div>
                        </div>
                    </div>
                </div>

                <!-- 软件版本分布环形图 -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h5><i class="fas fa-code-branch text-info"></i> 软件版本分布</h5></div>
                        <div class="card-body">
                            <div class="chart-canvas-wrapper">
                                <canvas id="versionChart" height="200"></canvas>
                            </div>
                        </div>
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

            <!-- 实时数据流 -->
            <div class="row g-3 mt-1">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-stream text-primary-custom"></i> 实时数据流 <span class="badge bg-success-subtle text-success-emphasis ms-2"><span class="pulse-dot"></span>实时</span></h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="stream-container" id="streamList"></div>
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

    // ===== Chart.js 全局配置 =====
    Chart.defaults.font.family = '-apple-system, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif';
    Chart.defaults.color = '#64748b';
    Chart.defaults.borderColor = 'rgba(226, 232, 240, 0.5)';

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

    // ===== 分类分布饼图 =====
    var categoryChart = new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: ['工具', '游戏', '影音', '教育', '社交', '其他'],
            datasets: [{
                data: [35, 25, 20, 10, 5, 5],
                backgroundColor: ['#3b82f6', '#8b5cf6', '#ef4444', '#f59e0b', '#10b981', '#06b6d4'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } } }
        }
    });

    // ===== 用户增长趋势折线图 =====
    var userGrowthData = generateGrowthData();
    var userGrowthChart = new Chart(document.getElementById('userGrowthChart'), {
        type: 'line',
        data: {
            labels: userGrowthData.labels,
            datasets: [{
                label: '新增用户',
                data: userGrowthData.values,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { maxTicksLimit: 6, font: { size: 10 } } },
                y: { grid: { color: 'rgba(226,232,240,0.4)' }, ticks: { font: { size: 10 } } }
            }
        }
    });

    // 生成30天增长数据
    function generateGrowthData() {
        var labels = [], values = [];
        for (var i = 29; i >= 0; i--) {
            var d = new Date(); d.setDate(d.getDate() - i);
            labels.push((d.getMonth()+1) + '/' + d.getDate());
            values.push(Math.floor(Math.random() * 80 + 20 + (30-i)*2));
        }
        return { labels: labels, values: values };
    }

    // ===== 下载来源分析柱状图 =====
    var downloadSourceChart = new Chart(document.getElementById('downloadSourceChart'), {
        type: 'bar',
        data: {
            labels: ['首页推荐', '分类浏览', '搜索', '详情推荐'],
            datasets: [{
                data: [3200, 2800, 1900, 1500],
                backgroundColor: ['#3b82f6', '#8b5cf6', '#f59e0b', '#10b981'],
                borderRadius: 6,
                barThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: { grid: { color: 'rgba(226,232,240,0.4)' }, ticks: { font: { size: 10 } } }
            }
        }
    });

    // ===== 软件版本分布环形图 =====
    var versionChart = new Chart(document.getElementById('versionChart'), {
        type: 'polarArea',
        data: {
            labels: ['v1.x', 'v2.x', 'v3.x', 'v4.x', 'v5.x'],
            datasets: [{
                data: [8, 15, 32, 28, 17],
                backgroundColor: ['rgba(59,130,246,0.7)', 'rgba(139,92,246,0.7)', 'rgba(239,68,68,0.7)', 'rgba(245,158,11,0.7)', 'rgba(16,185,129,0.7)'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } } },
            scales: { r: { ticks: { display: false }, grid: { color: 'rgba(226,232,240,0.3)' } } }
        }
    });

    // ===== 用户活跃热力图 =====
    renderHeatmap();
    function renderHeatmap() {
        var days = ['周一','周二','周三','周四','周五','周六','周日'];
        var html = '<div style="display:grid;grid-template-columns:40px repeat(24,1fr);gap:2px;font-size:10px;">';
        html += '<div></div>';
        for (var h = 0; h < 24; h++) html += '<div style="text-align:center;color:#94a3b8;">' + (h % 6 === 0 ? h : '') + '</div>';
        for (var d = 0; d < 7; d++) {
            html += '<div style="display:flex;align-items:center;color:#94a3b8;">' + days[d] + '</div>';
            for (h = 0; h < 24; h++) {
                var intensity = Math.random();
                // 工作时间和晚间活跃度更高
                if (h >= 9 && h <= 22) intensity = Math.min(1, intensity + 0.3);
                if (d >= 5) intensity = Math.min(1, intensity + 0.2);
                var opacity = 0.05 + intensity * 0.85;
                var color = intensity > 0.7 ? 'rgba(239,68,68,' + opacity + ')' :
                            intensity > 0.4 ? 'rgba(245,158,11,' + opacity + ')' :
                            'rgba(59,130,246,' + opacity + ')';
                html += '<div style="height:22px;border-radius:3px;background:' + color + ';" title="' + days[d] + ' ' + h + ':00 活跃度:' + Math.round(intensity*100) + '%"></div>';
            }
        }
        html += '</div>';
        // 添加图例
        html += '<div style="display:flex;align-items:center;gap:6px;margin-top:10px;font-size:11px;color:#94a3b8;">低';
        for (var i = 0; i <= 10; i++) {
            var op = 0.05 + (i/10) * 0.85;
            html += '<div style="width:14px;height:14px;border-radius:2px;background:rgba(59,130,246,' + op + ');"></div>';
        }
        html += '高</div>';
        $('#heatmapContainer').html(html);
    }

    // ===== 实时数据流 =====
    var eventTypes = [
        { type: 'download', icon: 'fa-download', color: '#3b82f6', text: '下载' },
        { type: 'register', icon: 'fa-user-plus', color: '#10b981', text: '注册' },
        { type: 'login', icon: 'fa-sign-in-alt', color: '#8b5cf6', text: '登录' },
        { type: 'feedback', icon: 'fa-comment', color: '#f59e0b', text: '反馈' }
    ];
    var softwareNames = ['影视大全','极速浏览器','清理大师','学习宝典','音乐播放器','美图相机','极速笔记','休闲游戏合集'];

    function addStreamEvent() {
        var evt = eventTypes[Math.floor(Math.random() * eventTypes.length)];
        var sw = softwareNames[Math.floor(Math.random() * softwareNames.length)];
        var user = '用户' + Math.floor(Math.random() * 10000);
        var now = new Date();
        var time = String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0') + ':' + String(now.getSeconds()).padStart(2,'0');

        var html = '<div class="stream-item" style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-bottom:1px solid rgba(226,232,240,0.4);animation:slideInLeft .3s ease;">' +
            '<div style="width:28px;height:28px;border-radius:50%;background:' + evt.color + '20;color:' + evt.color + ';display:flex;align-items:center;justify-content:center;font-size:12px;"><i class="fas ' + evt.icon + '"></i></div>' +
            '<div style="flex:1;font-size:13px;"><span style="color:' + evt.color + ';font-weight:600;">' + evt.text + '</span> <span style="color:#64748b;">' + user + '</span> ' + (evt.type === 'download' || evt.type === 'feedback' ? sw : '') + '</div>' +
            '<div style="font-size:11px;color:#94a3b8;">' + time + '</div>' +
            '</div>';

        var $stream = $('#streamList');
        $stream.prepend(html);
        // 保持最多20条
        if ($stream.children().length > 20) {
            $stream.children().last().remove();
        }
    }

    // 初始化10条
    for (var i = 0; i < 10; i++) addStreamEvent();
    // 每3秒添加一条
    setInterval(addStreamEvent, 3000);
});
</script>
</body>
</html>
