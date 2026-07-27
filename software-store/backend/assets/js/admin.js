(function () {
  'use strict';

  var API_BASE = '';

  function getToken() {
    return localStorage.getItem('admin_token') || sessionStorage.getItem('admin_token') || '';
  }

  function saveToken(token, remember) {
    if (remember) {
      localStorage.setItem('admin_token', token);
    } else {
      sessionStorage.setItem('admin_token', token);
    }
  }

  function clearToken() {
    localStorage.removeItem('admin_token');
    sessionStorage.removeItem('admin_token');
  }

  function checkToken() {
    var token = getToken();
    if (!token) {
      window.location.href = 'login.php';
      return false;
    }
    return true;
  }

  function checkHttpStatus(response) {
    if (response.status === 401) {
      clearToken();
      showToast('登录已过期，请重新登录', 'error');
      setTimeout(function () {
        window.location.href = 'login.php';
      }, 1500);
      throw new Error('Unauthorized');
    }
    if (!response.ok) {
      return response.json().then(function (data) {
        throw new Error(data.message || '请求失败');
      });
    }
    return response;
  }

  async function api(method, url, data) {
    method = method.toUpperCase();
    var headers = {
      'Content-Type': 'application/json',
    };
    var token = getToken();
    if (token) {
      headers['Authorization'] = 'Bearer ' + token;
    }

    var options = {
      method: method,
      headers: headers,
    };

    if (data !== undefined && data !== null && method !== 'GET') {
      options.body = JSON.stringify(data);
    }

    var fullUrl = url.indexOf('http') === 0 ? url : API_BASE + url;

    try {
      var response = await fetch(fullUrl, options);
      var checked = checkHttpStatus(response);
      return await checked.json();
    } catch (error) {
      if (error.message === 'Unauthorized') {
        throw error;
      }
      showToast(error.message || '网络错误', 'error');
      throw error;
    }
  }

  var toastContainer = null;

  function ensureToastContainer() {
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.className = 'toast-container';
      document.body.appendChild(toastContainer);
    }
    return toastContainer;
  }

  function showToast(msg, type) {
    type = type || 'info';
    var container = ensureToastContainer();

    var toast = document.createElement('div');
    toast.className = 'toast ' + type;

    var iconMap = {
      success: '✓',
      error: '✕',
      warning: '⚠',
      info: 'ℹ',
    };

    toast.innerHTML =
      '<span class="toast-icon">' + (iconMap[type] || iconMap.info) + '</span>' +
      '<span class="toast-message"></span>' +
      '<button class="toast-close" aria-label="关闭">✕</button>';

    toast.querySelector('.toast-message').textContent = msg;

    toast.querySelector('.toast-close').addEventListener('click', function () {
      removeToast(toast);
    });

    container.appendChild(toast);

    setTimeout(function () {
      removeToast(toast);
    }, 3500);
  }

  function removeToast(toast) {
    toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    setTimeout(function () {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 300);
  }

  function showConfirm(msg, callback) {
    var overlay = document.createElement('div');
    overlay.className = 'modal-overlay show';
    overlay.innerHTML =
      '<div class="modal modal-sm">' +
      '<div class="modal-header">' +
      '<h3>确认操作</h3>' +
      '<button class="modal-close">&times;</button>' +
      '</div>' +
      '<div class="modal-body"><p style="margin:0;">' + msg + '</p></div>' +
      '<div class="modal-footer">' +
      '<button class="btn btn-secondary btn-cancel">取消</button>' +
      '<button class="btn btn-danger btn-ok">确认</button>' +
      '</div>' +
      '</div>';

    document.body.appendChild(overlay);

    function close() {
      overlay.classList.remove('show');
      setTimeout(function () {
        if (overlay.parentNode) {
          overlay.parentNode.removeChild(overlay);
        }
      }, 200);
    }

    overlay.querySelector('.btn-cancel').addEventListener('click', close);
    overlay.querySelector('.modal-close').addEventListener('click', close);
    overlay.querySelector('.btn-ok').addEventListener('click', function () {
      close();
      if (typeof callback === 'function') {
        callback();
      }
    });

    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) {
        close();
      }
    });
  }

  var loadingOverlay = null;

  function showLoading() {
    if (!loadingOverlay) {
      loadingOverlay = document.createElement('div');
      loadingOverlay.className = 'loading-overlay';
      loadingOverlay.innerHTML = '<div class="spinner"></div>';
      document.body.appendChild(loadingOverlay);
    }
    loadingOverlay.classList.add('show');
  }

  function hideLoading() {
    if (loadingOverlay) {
      loadingOverlay.classList.remove('show');
    }
  }

  function openModal(id) {
    var modal = document.getElementById(id);
    if (modal) {
      modal.classList.add('show');
      var firstInput = modal.querySelector('input, select, textarea');
      if (firstInput) {
        setTimeout(function () {
          firstInput.focus();
        }, 200);
      }
    }
  }

  function closeModal(id) {
    var modal = document.getElementById(id);
    if (modal) {
      modal.classList.remove('show');
      var form = modal.querySelector('form');
      if (form) {
        form.reset();
        clearFormErrors(form);
      }
    }
  }

  function initModalForms() {
    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
      overlay.addEventListener('click', function (e) {
        if (e.target === overlay) {
          overlay.classList.remove('show');
          var form = overlay.querySelector('form');
          if (form) {
            form.reset();
            clearFormErrors(form);
          }
        }
      });

      var closeBtn = overlay.querySelector('.modal-close');
      if (closeBtn) {
        closeBtn.addEventListener('click', function () {
          overlay.classList.remove('show');
          var form = overlay.querySelector('form');
          if (form) {
            form.reset();
            clearFormErrors(form);
          }
        });
      }
    });

    document.querySelectorAll('[data-modal]').forEach(function (trigger) {
      trigger.addEventListener('click', function () {
        var modalId = this.getAttribute('data-modal');
        openModal(modalId);
      });
    });
  }

  function clearFormErrors(form) {
    form.querySelectorAll('.form-group').forEach(function (group) {
      group.classList.remove('has-error');
    });
    form.querySelectorAll('.form-error').forEach(function (err) {
      err.textContent = '';
    });
  }

  function setFormError(form, field, message) {
    var input = form.querySelector('[name="' + field + '"]');
    if (input) {
      var group = input.closest('.form-group');
      if (group) {
        group.classList.add('has-error');
        var errorEl = group.querySelector('.form-error');
        if (errorEl) {
          errorEl.textContent = message;
        }
      }
    }
  }

  function initFormSubmit(formId, apiUrl, method) {
    var form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      clearFormErrors(form);

      var submitBtn = form.querySelector('button[type="submit"]');
      var originalHtml = submitBtn ? submitBtn.innerHTML : '';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('loading-btn');
      }

      var formData = {};
      var inputs = form.querySelectorAll('[name]');
      inputs.forEach(function (input) {
        if (input.type === 'checkbox') {
          formData[input.name] = input.checked;
        } else if (input.type === 'radio') {
          if (input.checked) {
            formData[input.name] = input.value;
          }
        } else if (input.tagName === 'SELECT' && input.multiple) {
          formData[input.name] = Array.from(input.selectedOptions).map(function (o) { return o.value; });
        } else {
          formData[input.name] = input.value;
        }
      });

      try {
        var result = await api(method || 'POST', apiUrl, formData);
        showToast(result.message || '操作成功', 'success');

        if (formId !== 'loginForm') {
          var modal = form.closest('.modal-overlay');
          if (modal) {
            modal.classList.remove('show');
            form.reset();
          }
        }

        if (typeof window.onFormSuccess === 'function') {
          window.onFormSuccess(result, formId);
        }
      } catch (error) {
        if (error.errors) {
          Object.keys(error.errors).forEach(function (field) {
            setFormError(form, field, error.errors[field]);
          });
        }
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.classList.remove('loading-btn');
        }
      }
    });
  }

  function initImageUpload(inputId, previewId) {
    var input = document.getElementById(inputId);
    var preview = document.getElementById(previewId);
    if (!input || !preview) return;

    function handleFiles(files) {
      preview.innerHTML = '';
      Array.from(files).forEach(function (file) {
        if (!file.type.startsWith('image/')) return;

        var reader = new FileReader();
        reader.onload = function (e) {
          var item = document.createElement('div');
          item.className = 'image-preview-item';
          item.innerHTML =
            '<img src="' + e.target.result + '" alt="预览">' +
            '<button type="button" class="remove" title="移除">&times;</button>';

          item.querySelector('.remove').addEventListener('click', function () {
            item.remove();
            if (input.multiple) {
              var dt = new DataTransfer();
              Array.from(input.files).forEach(function (f) {
                if (f !== file) dt.items.add(f);
              });
              input.files = dt.files;
            } else {
              input.value = '';
            }
          });

          preview.appendChild(item);
        };
        reader.readAsDataURL(file);
      });
    }

    input.addEventListener('change', function () {
      handleFiles(input.files);
    });

    var zone = input.closest('.upload-zone');
    if (zone) {
      zone.addEventListener('click', function () {
        input.click();
      });

      zone.addEventListener('dragover', function (e) {
        e.preventDefault();
        zone.classList.add('dragover');
      });

      zone.addEventListener('dragleave', function () {
        zone.classList.remove('dragover');
      });

      zone.addEventListener('drop', function (e) {
        e.preventDefault();
        zone.classList.remove('dragover');
        var files = e.dataTransfer.files;
        if (files.length > 0) {
          if (input.multiple) {
            handleFiles(files);
          } else {
            handleFiles([files[0]]);
          }
          input.files = files;
        }
      });
    }
  }

  function initFileUpload(inputId, type) {
    var input = document.getElementById(inputId);
    if (!input) return;

    input.addEventListener('change', function () {
      var files = input.files;
      if (files.length === 0) return;

      var validTypes = {
        image: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        document: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'],
        archive: ['application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed'],
      };

      if (type && validTypes[type]) {
        for (var i = 0; i < files.length; i++) {
          if (validTypes[type].indexOf(files[i].type) === -1) {
            showToast('不支持的文件类型：' + files[i].name, 'error');
            input.value = '';
            return;
          }
        }
      }

      var maxSize = 50 * 1024 * 1024;
      for (var j = 0; j < files.length; j++) {
        if (files[j].size > maxSize) {
          showToast('文件过大：' + files[j].name + '，最大支持 50MB', 'error');
          input.value = '';
          return;
        }
      }
    });
  }

  function formatDate(dateStr, format) {
    if (!dateStr) return '';
    format = format || 'YYYY-MM-DD HH:mm:ss';
    var date;
    if (typeof dateStr === 'string') {
      date = new Date(dateStr.replace(/-/g, '/'));
    } else if (dateStr instanceof Date) {
      date = dateStr;
    } else {
      date = new Date(dateStr);
    }

    if (isNaN(date.getTime())) return dateStr;

    var map = {
      YYYY: date.getFullYear(),
      MM: String(date.getMonth() + 1).padStart(2, '0'),
      DD: String(date.getDate()).padStart(2, '0'),
      HH: String(date.getHours()).padStart(2, '0'),
      mm: String(date.getMinutes()).padStart(2, '0'),
      ss: String(date.getSeconds()).padStart(2, '0'),
    };

    return format.replace(/YYYY|MM|DD|HH|mm|ss/g, function (match) {
      return map[match];
    });
  }

  function renderTable(container, columns, data, rowActions) {
    var el = typeof container === 'string' ? document.querySelector(container) : container;
    if (!el) return;

    if (!data || data.length === 0) {
      el.innerHTML =
        '<table class="table">' +
        '<thead><tr>' + columns.map(function (c) { return '<th>' + c.label + '</th>'; }).join('') + '</tr></thead>' +
        '<tbody><tr><td colspan="' + columns.length + '" class="empty-state"><span class="icon">📋</span>暂无数据</td></tr></tbody>' +
        '</table>';
      return;
    }

    var html =
      '<table class="table">' +
      '<thead><tr>' + columns.map(function (c) { return '<th>' + c.label + '</th>'; }).join('') + '</tr></thead>' +
      '<tbody>';

    data.forEach(function (row) {
      html += '<tr>';
      columns.forEach(function (col) {
        var value = row[col.key];
        if (typeof col.render === 'function') {
          value = col.render(value, row);
        } else if (col.type === 'date') {
          value = formatDate(value);
        } else if (col.type === 'badge') {
          value = '<span class="badge badge-' + (col.badgeType || 'primary') + '">' + (value || '') + '</span>';
        } else if (col.type === 'image') {
          value = value ? '<img src="' + value + '" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">' : '-';
        }
        html += '<td>' + (value !== undefined && value !== null ? value : '-') + '</td>';
      });

      if (rowActions && rowActions.length > 0) {
        html += '<td><div class="actions">';
        rowActions.forEach(function (action) {
          var cls = 'btn btn-small ' + (action.class || 'btn-secondary');
          html += '<button class="' + cls + '" data-action="' + action.key + '" data-id="' + row.id + '">' + action.label + '</button>';
        });
        html += '</div></td>';
      }

      html += '</tr>';
    });

    html += '</tbody></table>';
    el.innerHTML = html;

    if (rowActions && rowActions.length > 0) {
      el.querySelectorAll('[data-action]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var action = this.getAttribute('data-action');
          var id = this.getAttribute('data-id');
          if (typeof window.onRowAction === 'function') {
            window.onRowAction(action, id);
          }
        });
      });
    }
  }

  function renderPagination(container, currentPage, totalPages, onChange) {
    var el = typeof container === 'string' ? document.querySelector(container) : container;
    if (!el) return;

    if (totalPages <= 1) {
      el.innerHTML = '';
      return;
    }

    var html = '<div class="pagination">';

    html += '<button class="page-btn" data-page="prev"' + (currentPage <= 1 ? ' disabled' : '') + '>&lsaquo;</button>';

    var startPage = Math.max(1, currentPage - 2);
    var endPage = Math.min(totalPages, startPage + 4);
    startPage = Math.max(1, endPage - 4);

    if (startPage > 1) {
      html += '<button class="page-btn" data-page="1">1</button>';
      if (startPage > 2) {
        html += '<span class="page-ellipsis">...</span>';
      }
    }

    for (var i = startPage; i <= endPage; i++) {
      html += '<button class="page-btn' + (i === currentPage ? ' active' : '') + '" data-page="' + i + '">' + i + '</button>';
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        html += '<span class="page-ellipsis">...</span>';
      }
      html += '<button class="page-btn" data-page="' + totalPages + '">' + totalPages + '</button>';
    }

    html += '<button class="page-btn" data-page="next"' + (currentPage >= totalPages ? ' disabled' : '') + '>&rsaquo;</button>';
    html += '<span class="page-info">第 ' + currentPage + ' / ' + totalPages + ' 页</span>';
    html += '</div>';

    el.innerHTML = html;

    el.querySelectorAll('.page-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var page = this.getAttribute('data-page');
        if (page === 'prev') {
          page = currentPage - 1;
        } else if (page === 'next') {
          page = currentPage + 1;
        } else {
          page = parseInt(page, 10);
        }
        if (page >= 1 && page <= totalPages && typeof onChange === 'function') {
          onChange(page);
        }
      });
    });
  }

  function initSidebar() {
    var toggles = document.querySelectorAll('.sidebar-nav .has-submenu > a');
    toggles.forEach(function (toggle) {
      toggle.addEventListener('click', function (e) {
        e.preventDefault();
        var parent = this.parentElement;
        parent.classList.toggle('open');
        var submenu = parent.querySelector('.submenu');
        if (submenu) {
          submenu.classList.toggle('open');
        }
      });
    });

    var activeLink = document.querySelector('.sidebar-nav a.active');
    if (activeLink) {
      var parentSubmenu = activeLink.closest('.submenu');
      if (parentSubmenu) {
        parentSubmenu.classList.add('open');
        var parentItem = parentSubmenu.closest('.has-submenu');
        if (parentItem) {
          parentItem.classList.add('open');
        }
      }
    }

    var mobileToggle = document.querySelector('.sidebar-toggle');
    if (mobileToggle) {
      mobileToggle.addEventListener('click', function () {
        var sidebar = document.querySelector('.sidebar');
        if (sidebar) {
          sidebar.classList.toggle('open');
        }
      });
    }

    var userBtn = document.querySelector('.topbar-user');
    if (userBtn) {
      userBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        this.classList.toggle('open');
      });

      document.addEventListener('click', function () {
        userBtn.classList.remove('open');
      });
    }
  }

  function initWYSIWYG(selector) {
    if (typeof tinymce !== 'undefined') {
      tinymce.init({
        selector: selector,
        height: 300,
        plugins: 'lists link image preview code',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | link image | preview',
        menubar: false,
        branding: false,
        language: 'zh_CN',
      });
    } else {
      var el = document.querySelector(selector);
      if (el) {
        el.style.minHeight = '300px';
        el.style.padding = '12px';
        el.style.border = '1px solid #E1E8ED';
        el.style.borderRadius = '8px';
        el.style.fontFamily = 'inherit';
        el.style.fontSize = '0.9rem';
      }
    }
  }

  function initAutoLogout() {
    var lastActivity = Date.now();
    var timeout = 30 * 60 * 1000;

    function resetTimer() {
      lastActivity = Date.now();
    }

    function checkActivity() {
      if (Date.now() - lastActivity > timeout) {
        clearToken();
        showToast('由于长时间未操作，已自动退出登录', 'info');
        setTimeout(function () {
          window.location.href = 'login.php';
        }, 1500);
      }
    }

    document.addEventListener('mousemove', resetTimer);
    document.addEventListener('keypress', resetTimer);
    document.addEventListener('click', resetTimer);

    setInterval(checkActivity, 60000);
  }

  document.addEventListener('DOMContentLoaded', function () {
    initSidebar();
    initModalForms();
    initAutoLogout();

    document.querySelectorAll('[data-confirm]').forEach(function (el) {
      el.addEventListener('click', function (e) {
        var message = this.getAttribute('data-confirm') || '确定要执行此操作吗？';
        e.preventDefault();
        showConfirm(message, function () {
          if (el.tagName === 'A') {
            window.location.href = el.href;
          } else {
            var form = el.closest('form');
            if (form) {
              form.submit();
            }
          }
        });
      });
    });
  });

  window.Admin = {
    api: api,
    getToken: getToken,
    saveToken: saveToken,
    clearToken: clearToken,
    checkToken: checkToken,
    showToast: showToast,
    showConfirm: showConfirm,
    showLoading: showLoading,
    hideLoading: hideLoading,
    openModal: openModal,
    closeModal: closeModal,
    initFormSubmit: initFormSubmit,
    initImageUpload: initImageUpload,
    initFileUpload: initFileUpload,
    renderTable: renderTable,
    renderPagination: renderPagination,
    formatDate: formatDate,
    initWYSIWYG: initWYSIWYG,
  };
})();