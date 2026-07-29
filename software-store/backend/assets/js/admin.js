/* ============================================================
 * 软件库后台管理系统核心 JS
 * 包含：API helper（封装 fetch 带 token）、Auth 管理、UI 工具函数、
 *       侧边栏切换、通用数据加载等
 * 依赖：jQuery 3
 * ============================================================ */
(function (global, $) {
    'use strict';

    // 后台 API 基础地址（相对 pages/ 目录）
    var API_BASE = '../api/admin/index.php?action=';

    // ============================================================
    // Auth 管理
    // ============================================================
    var Auth = {
        /** 获取 token */
        getToken: function () {
            return localStorage.getItem('admin_token') || '';
        },
        /** 保存 token */
        setToken: function (token) {
            localStorage.setItem('admin_token', token);
        },
        /** 获取管理员信息 */
        getAdmin: function () {
            var str = localStorage.getItem('admin_info');
            return str ? JSON.parse(str) : null;
        },
        /** 保存管理员信息 */
        setAdmin: function (info) {
            localStorage.setItem('admin_info', JSON.stringify(info));
        },
        /** 是否已登录 */
        isLogin: function () {
            return !!this.getToken();
        },
        /** 退出登录 */
        logout: function () {
            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_info');
            location.href = 'login.php';
        }
    };

    // ============================================================
    // API helper
    // ============================================================
    var Api = {
        /**
         * 统一请求封装
         * @param {string} action 接口动作，如 'software/list'
         * @param {object} data 请求数据
         * @param {string} method 请求方法 GET/POST
         * @returns {Promise}
         */
        request: function (action, data, method) {
            method = method || 'GET';
            data = data || {};
            var url = API_BASE + action;
            var headers = { 'token': Auth.getToken() };
            var ajaxOptions = {
                url: url,
                type: method,
                dataType: 'json',
                headers: headers
            };
            if (method.toUpperCase() === 'GET') {
                ajaxOptions.data = data;
            } else {
                // POST 用 JSON 请求体，兼容 form-data
                headers['Content-Type'] = 'application/json';
                ajaxOptions.data = JSON.stringify(data);
                ajaxOptions.processData = false;
                ajaxOptions.contentType = 'application/json';
            }
            return $.ajax(ajaxOptions).then(function (res) {
                // 统一拦截未授权
                if (res.code === 401) {
                    UI.toast('登录已过期，请重新登录', 'error');
                    setTimeout(function () {
                        Auth.logout();
                    }, 1200);
                    return $.Deferred().reject(res);
                }
                return res;
            }, function (xhr) {
                var msg = '网络请求失败';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                    if (xhr.responseJSON.code === 401) {
                        UI.toast('登录已过期，请重新登录', 'error');
                        setTimeout(function () { Auth.logout(); }, 1200);
                    }
                }
                return $.Deferred().reject({ code: xhr.status, message: msg, data: null });
            });
        },
        get: function (action, data) {
            return this.request(action, data, 'GET');
        },
        post: function (action, data) {
            return this.request(action, data, 'POST');
        },
        /** 上传文件（multipart） */
        upload: function (file, type) {
            var formData = new FormData();
            formData.append('file', file);
            return $.ajax({
                url: API_BASE + 'upload&type=' + (type || 'image'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                headers: { 'token': Auth.getToken() },
                processData: false,
                contentType: false
            });
        }
    };

    // ============================================================
    // UI 工具函数
    // ============================================================
    var UI = {
        /** 显示 toast 提示 */
        toast: function (message, type) {
            type = type || 'info';
            var icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-circle', info: 'fa-info-circle' };
            var $container = $('.toast-container');
            if ($container.length === 0) {
                $container = $('<div class="toast-container"></div>').appendTo('body');
            }
            var $toast = $('<div class="toast-item ' + type + '"><i class="fas ' + (icons[type] || icons.info) + '"></i><span>' + UI.escape(message) + '</span></div>');
            $container.append($toast);
            setTimeout(function () {
                $toast.fadeOut(300, function () { $(this).remove(); });
            }, 3000);
        },
        /** 转义 HTML */
        escape: function (str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        },
        /** 确认弹窗 */
        confirm: function (message, callback) {
            if (window.confirm(message)) {
                callback && callback();
            }
        },
        /** 显示加载中 */
        showLoading: function () {
            if ($('#global-loading').length === 0) {
                $('body').append('<div id="global-loading" style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,.6);z-index:9998;display:flex;align-items:center;justify-content:center;"><div class="loading-spinner" style="border-color:rgba(59,130,246,.3);border-top-color:#3b82f6;width:32px;height:32px;"></div></div>');
            }
        },
        hideLoading: function () {
            $('#global-loading').remove();
        },
        /** 格式化数字（万） */
        formatNumber: function (num) {
            num = parseInt(num, 10) || 0;
            if (num >= 100000000) return (num / 100000000).toFixed(1) + '亿';
            if (num >= 10000) return (num / 10000).toFixed(1) + '万';
            return num.toString();
        },
        /** 格式化时间 */
        formatDate: function (datetime, format) {
            if (!datetime) return '-';
            format = format || 'YYYY-MM-DD HH:mm';
            var d = new Date(datetime.replace(/-/g, '/'));
            if (isNaN(d.getTime())) return datetime;
            var map = {
                'YYYY': d.getFullYear(),
                'MM': String(d.getMonth() + 1).padStart(2, '0'),
                'DD': String(d.getDate()).padStart(2, '0'),
                'HH': String(d.getHours()).padStart(2, '0'),
                'mm': String(d.getMinutes()).padStart(2, '0'),
                'ss': String(d.getSeconds()).padStart(2, '0')
            };
            var result = format;
            for (var k in map) {
                result = result.replace(k, map[k]);
            }
            return result;
        },
        /** 友好时间 */
        timeAgo: function (datetime) {
            if (!datetime) return '-';
            var d = new Date(datetime.replace(/-/g, '/'));
            if (isNaN(d.getTime())) return datetime;
            var diff = (Date.now() - d.getTime()) / 1000;
            if (diff < 60) return Math.floor(diff) + '秒前';
            if (diff < 3600) return Math.floor(diff / 60) + '分钟前';
            if (diff < 86400) return Math.floor(diff / 3600) + '小时前';
            if (diff < 2592000) return Math.floor(diff / 86400) + '天前';
            return this.formatDate(datetime, 'YYYY-MM-DD');
        },
        /** 状态标签 HTML */
        statusTag: function (status, onText, offText) {
            onText = onText || '启用';
            offText = offText || '禁用';
            if (parseInt(status, 10) === 1) {
                return '<span class="status-tag active">' + onText + '</span>';
            }
            return '<span class="status-tag inactive">' + offText + '</span>';
        },
        /** 获取 URL 查询参数 */
        getQuery: function (name) {
            var match = new RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
            return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
        },
        /** 软件图标占位符 */
        thumbPlaceholder: function (name) {
            var ch = (name || 'A').charAt(0).toUpperCase();
            return '<div class="thumb-placeholder">' + UI.escape(ch) + '</div>';
        }
    };

    // ============================================================
    // 侧边栏切换
    // ============================================================
    function initSidebar() {
        // 移动端切换
        $('.topbar-toggle').on('click', function () {
            $('.sidebar').toggleClass('show');
            $('.sidebar-overlay').toggleClass('show');
        });
        $('.sidebar-overlay').on('click', function () {
            $('.sidebar').removeClass('show');
            $('.sidebar-overlay').removeClass('show');
        });
        // 高亮当前菜单
        var current = location.pathname.split('/').pop() || 'dashboard.php';
        $('.sidebar-menu .menu-item a').each(function () {
            var href = $(this).attr('href');
            if (href === current) {
                $(this).parent().addClass('active');
            }
        });
    }

    // ============================================================
    // 登录守卫
    // ============================================================
    function requireAuth() {
        if (!Auth.isLogin() && location.pathname.indexOf('login.php') === -1 && location.pathname.indexOf('preview-init.php') === -1) {
            location.href = 'login.php';
            return false;
        }
        return true;
    }

    // ============================================================
    // 渲染顶部用户信息
    // ============================================================
    function renderUserInfo() {
        var admin = Auth.getAdmin();
        if (admin) {
            var initial = (admin.name || admin.username || 'A').charAt(0).toUpperCase();
            $('.topbar-user .user-avatar').text(initial);
            $('.topbar-user .user-name').text(admin.name || admin.username);
            $('.topbar-user .user-role').text(admin.role === 'super' ? '超级管理员' : '管理员');
        }
    }

    // ============================================================
    // 退出登录绑定
    // ============================================================
    function bindLogout() {
        $(document).on('click', '.btn-logout', function (e) {
            e.preventDefault();
            UI.confirm('确定要退出登录吗？', function () {
                Auth.logout();
            });
        });
    }

    // ============================================================
    // 通用分页渲染
    // ============================================================
    function renderPagination(container, data, onPageChange) {
        var $container = $(container);
        if (!data) return;
        var total = parseInt(data.total, 10) || 0;
        var page = parseInt(data.page, 10) || 1;
        var size = parseInt(data.size, 10) || 20;
        var pages = parseInt(data.pages, 10) || Math.ceil(total / size);
        var start = total === 0 ? 0 : (page - 1) * size + 1;
        var end = Math.min(page * size, total);

        var html = '<div class="pagination-info">共 ' + total + ' 条，显示 ' + start + '-' + end + '</div>';
        html += '<ul class="pagination">';
        html += '<li class="page-item ' + (page <= 1 ? 'disabled' : '') + '"><a class="page-link" href="javascript:;" data-page="' + (page - 1) + '"><i class="fas fa-chevron-left"></i></a></li>';
        // 页码
        var startPage = Math.max(1, page - 2);
        var endPage = Math.min(pages, page + 2);
        if (startPage > 1) {
            html += '<li class="page-item"><a class="page-link" href="javascript:;" data-page="1">1</a></li>';
            if (startPage > 2) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        for (var i = startPage; i <= endPage; i++) {
            html += '<li class="page-item ' + (i === page ? 'active' : '') + '"><a class="page-link" href="javascript:;" data-page="' + i + '">' + i + '</a></li>';
        }
        if (endPage < pages) {
            if (endPage < pages - 1) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            html += '<li class="page-item"><a class="page-link" href="javascript:;" data-page="' + pages + '">' + pages + '</a></li>';
        }
        html += '<li class="page-item ' + (page >= pages ? 'disabled' : '') + '"><a class="page-link" href="javascript:;" data-page="' + (page + 1) + '"><i class="fas fa-chevron-right"></i></a></li>';
        html += '</ul>';

        $container.html(html);
        $container.off('click', '.page-link').on('click', '.page-link', function (e) {
            e.preventDefault();
            var p = $(this).data('page');
            if (p && !$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active')) {
                onPageChange && onPageChange(parseInt(p, 10));
            }
        });
    }

    // ============================================================
    // 通用 CRUD 删除
    // ============================================================
    function bindDelete(action, callback) {
        $(document).on('click', '.btn-delete', function () {
            var id = $(this).data('id');
            var name = $(this).data('name') || '';
            UI.confirm('确定要删除「' + name + '」吗？此操作不可恢复。', function () {
                Api.post(action, { id: id }).then(function (res) {
                    if (res.code === 200) {
                        UI.toast('删除成功', 'success');
                        callback && callback();
                    } else {
                        UI.toast(res.message || '删除失败', 'error');
                    }
                }).fail(function (err) {
                    UI.toast(err.message || '删除失败', 'error');
                });
            });
        });
    }

    // ============================================================
    // 初始化
    // ============================================================
    $(function () {
        initSidebar();
        bindLogout();
        renderUserInfo();
        requireAuth();
    });

    // 暴露到全局
    global.AdminApp = {
        Auth: Auth,
        Api: Api,
        UI: UI,
        renderPagination: renderPagination,
        bindDelete: bindDelete,
        initSidebar: initSidebar,
        renderUserInfo: renderUserInfo,
        requireAuth: requireAuth,
        API_BASE: API_BASE
    };

})(window, jQuery);
