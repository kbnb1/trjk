<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <span class="logo-icon"><i class="fas fa-store"></i></span>
            软件商店
        </div>
    </div>
    <ul class="sidebar-nav">
        <li>
            <a href="/pages/dashboard.php" class="<?= $current_page === 'dashboard' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="fas fa-gauge-high"></i></span>
                仪表盘
            </a>
        </li>
        <li class="has-submenu <?= in_array($current_page, ['category', 'software']) ? 'open' : '' ?>">
            <a href="#">
                <span class="nav-icon"><i class="fas fa-cubes"></i></span>
                软件管理
            </a>
            <ul class="submenu <?= in_array($current_page, ['category', 'software']) ? 'open' : '' ?>">
                <li>
                    <a href="/pages/category.php" class="<?= $current_page === 'category' ? 'active' : '' ?>">分类管理</a>
                </li>
                <li>
                    <a href="/pages/software.php" class="<?= $current_page === 'software' ? 'active' : '' ?>">软件列表</a>
                </li>
            </ul>
        </li>
        <li class="has-submenu <?= in_array($current_page, ['banner', 'notice', 'page']) ? 'open' : '' ?>">
            <a href="#">
                <span class="nav-icon"><i class="fas fa-bullhorn"></i></span>
                公告管理
            </a>
            <ul class="submenu <?= in_array($current_page, ['banner', 'notice', 'page']) ? 'open' : '' ?>">
                <li>
                    <a href="/pages/banner.php" class="<?= $current_page === 'banner' ? 'active' : '' ?>">轮播图</a>
                </li>
                <li>
                    <a href="/pages/notice.php" class="<?= $current_page === 'notice' ? 'active' : '' ?>">滚动公告</a>
                </li>
                <li>
                    <a href="/pages/page.php" class="<?= $current_page === 'page' ? 'active' : '' ?>">静态页面</a>
                </li>
            </ul>
        </li>
        <li>
            <a href="/pages/toolbar.php" class="<?= $current_page === 'toolbar' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="fas fa-toolbox"></i></span>
                工具栏管理
            </a>
        </li>
        <li>
            <a href="/pages/users.php" class="<?= $current_page === 'users' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="fas fa-users"></i></span>
                用户管理
            </a>
        </li>
        <li>
            <a href="/pages/ad.php" class="<?= $current_page === 'ad' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="fas fa-ad"></i></span>
                广告管理
            </a>
        </li>
        <li>
            <a href="/pages/settings.php" class="<?= $current_page === 'settings' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="fas fa-gear"></i></span>
                系统设置
            </a>
        </li>
    </ul>
</aside>