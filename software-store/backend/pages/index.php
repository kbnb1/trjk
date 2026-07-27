<?php
$pageTitle = '仪表盘';
$breadcrumb = '概览 / 仪表盘';
require __DIR__ . '/header.php';
?>

<div class="card">
    <div class="card-body">
        <div id="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px;"></div>
        <div id="recent-activities"></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>趋势数据</h3>
        <select id="trend-range" class="form-control" style="width:auto;">
            <option value="7">最近7天</option>
            <option value="30">最近30天</option>
        </select>
    </div>
    <div class="card-body">
        <canvas id="trend-chart" width="100%" height="300"></canvas>
    </div>
</div>

<script>
(function() {
    function loadStats() {
        AdminApp.get('/admin/stats').then(function(res) {
            if (res.code === 200) {
                var d = res.data;
                var grid = document.getElementById('stats-grid');
                grid.innerHTML = [
                    statCard('软件总数', d.counts.software, '个', 'var(--primary)'),
                    statCard('用户总数', d.counts.users, '人', 'var(--success)'),
                    statCard('总下载量', d.counts.downloads, '次', 'var(--info)'),
                    statCard('今日活跃', d.counts.today_active, '人', 'var(--warning)')
                ].join('');
            }
        });
    }

    function statCard(label, value, unit, color) {
        return '<div style="background:' + color + '15;border-radius:var(--radius-lg);padding:20px;border-left:4px solid ' + color + ';">' +
            '<div style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:8px;">' + label + '</div>' +
            '<div style="font-size:2rem;font-weight:700;color:' + color + ';">' + (value || 0) + '<span style="font-size:0.85rem;font-weight:400;margin-left:4px;color:var(--text-muted);">' + unit + '</span></div>' +
            '</div>';
    }

    function loadTrend(days) {
        AdminApp.get('/admin/stats/trend?days=' + days).then(function(res) {
            if (res.code === 200) {
                drawChart(res.data.trend);
            }
        });
    }

    function drawChart(trend) {
        var canvas = document.getElementById('trend-chart');
        var ctx = canvas.getContext('2d');
        var W = canvas.width = canvas.parentElement.clientWidth;
        var H = canvas.height;
        var padL = 50, padR = 20, padT = 20, padB = 40;
        var chartW = W - padL - padR;
        var chartH = H - padT - padB;

        ctx.clearRect(0, 0, W, H);

        var downloads = trend.map(function(t) { return t.downloads; });
        var users = trend.map(function(t) { return t.new_users; });
        var software = trend.map(function(t) { return t.new_software; });
        var maxVal = Math.max.apply(null, downloads.concat(users, software, [1]));

        function drawGrid() {
            ctx.strokeStyle = '#E1E8ED';
            ctx.lineWidth = 1;
            ctx.font = '11px sans-serif';
            ctx.fillStyle = '#95A5A6';
            for (var i = 0; i <= 4; i++) {
                var y = padT + (chartH * i / 4);
                ctx.beginPath();
                ctx.moveTo(padL, y);
                ctx.lineTo(W - padR, y);
                ctx.stroke();
                var val = Math.round(maxVal - (maxVal * i / 4));
                ctx.fillText(val, 5, y + 4);
            }
            for (var j = 0; j < trend.length; j++) {
                var x = padL + (chartW * j / Math.max(trend.length - 1, 1));
                var label = trend[j].date.substring(5);
                ctx.fillText(label, x - 15, H - 10);
            }
        }

        function drawLine(data, color) {
            ctx.strokeStyle = color;
            ctx.lineWidth = 2;
            ctx.beginPath();
            data.forEach(function(v, i) {
                var x = padL + (chartW * i / Math.max(data.length - 1, 1));
                var y = padT + chartH - (chartH * v / maxVal);
                if (i === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            });
            ctx.stroke();
            ctx.fillStyle = color;
            data.forEach(function(v, i) {
                var x = padL + (chartW * i / Math.max(data.length - 1, 1));
                var y = padT + chartH - (chartH * v / maxVal);
                ctx.beginPath();
                ctx.arc(x, y, 3, 0, Math.PI * 2);
                ctx.fill();
            });
        }

        drawGrid();
        drawLine(downloads, '#1A73E8');
        drawLine(users, '#2ECC71');
        drawLine(software, '#F1C40F');

        var legend = [
            { label: '下载量', color: '#1A73E8' },
            { label: '新增用户', color: '#2ECC71' },
            { label: '新增软件', color: '#F1C40F' }
        ];
        ctx.font = '12px sans-serif';
        legend.forEach(function(item, i) {
            var lx = padL + i * 100;
            var ly = H - 35;
            ctx.fillStyle = item.color;
            ctx.fillRect(lx, ly, 12, 12);
            ctx.fillStyle = '#2C3E50';
            ctx.fillText(item.label, lx + 18, ly + 10);
        });
    }

    document.getElementById('trend-range').addEventListener('change', function() {
        loadTrend(parseInt(this.value));
    });

    loadStats();
    loadTrend(7);
})();
</script>

<?php require __DIR__ . '/footer.php'; ?>
