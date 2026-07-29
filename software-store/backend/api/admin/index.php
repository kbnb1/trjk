<?php
/**
 * 后台管理 API 路由入口
 * 除登录接口外，所有接口需携带管理员 token 头
 * 支持两种访问方式：
 *   1. ?action=software/list
 *   2. /api/admin/index.php/software/list （PATH_INFO）
 */

require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/functions.php';

Response::handleOptions();

// 获取动作路由
$action = isset($_GET['action']) ? $_GET['action'] : '';
if (empty($action) && isset($_SERVER['PATH_INFO'])) {
    $action = trim($_SERVER['PATH_INFO'], '/');
}
if (empty($action)) {
    $action = 'index';
}

api_log('admin/' . $action, all_params());

// 不需要认证的接口
$publicActions = ['login'];

// 路由分发
switch ($action) {
    case '':
    case 'index':
        Response::success(['name' => '后台API', 'version' => SYSTEM_VERSION], '管理后台API服务');
        break;

    case 'login':               // POST 管理员登录
        admin_login();
        break;

    case 'logout':              // POST 退出登录
        Response::success(null, '已退出登录');
        break;

    case 'dashboard':           // GET 仪表盘统计
        require_admin();
        admin_dashboard();
        break;

    case 'upload':              // POST 通用上传
        require_admin();
        admin_upload();
        break;

    // ---- 软件 CRUD ----
    case 'software/list':       // GET
        require_admin();
        software_list();
        break;
    case 'software/detail':     // GET
        require_admin();
        software_detail();
        break;
    case 'software/create':     // POST
        require_admin();
        software_create();
        break;
    case 'software/update':     // POST
        require_admin();
        software_update();
        break;
    case 'software/delete':     // POST
        require_admin();
        software_delete();
        break;

    // ---- 分类 CRUD ----
    case 'category/list':
        require_admin();
        category_list();
        break;
    case 'category/create':
        require_admin();
        category_create();
        break;
    case 'category/update':
        require_admin();
        category_update();
        break;
    case 'category/delete':
        require_admin();
        category_delete();
        break;

    // ---- 轮播图 CRUD ----
    case 'banner/list':
        require_admin();
        banner_list();
        break;
    case 'banner/create':
        require_admin();
        banner_create();
        break;
    case 'banner/update':
        require_admin();
        banner_update();
        break;
    case 'banner/delete':
        require_admin();
        banner_delete();
        break;

    // ---- 公告 CRUD ----
    case 'notice/list':
        require_admin();
        notice_list();
        break;
    case 'notice/create':
        require_admin();
        notice_create();
        break;
    case 'notice/update':
        require_admin();
        notice_update();
        break;
    case 'notice/delete':
        require_admin();
        notice_delete();
        break;

    // ---- 工具栏 CRUD ----
    case 'toolbar/list':
        require_admin();
        toolbar_list();
        break;
    case 'toolbar/create':
        require_admin();
        toolbar_create();
        break;
    case 'toolbar/update':
        require_admin();
        toolbar_update();
        break;
    case 'toolbar/delete':
        require_admin();
        toolbar_delete();
        break;

    // ---- 广告 CRUD ----
    case 'advertisement/list':
        require_admin();
        advertisement_list();
        break;
    case 'advertisement/create':
        require_admin();
        advertisement_create();
        break;
    case 'advertisement/update':
        require_admin();
        advertisement_update();
        break;
    case 'advertisement/delete':
        require_admin();
        advertisement_delete();
        break;

    // ---- 用户管理 ----
    case 'user/list':
        require_admin();
        user_list();
        break;
    case 'user/detail':
        require_admin();
        user_detail();
        break;
    case 'user/update':
        require_admin();
        user_update();
        break;
    case 'user/delete':
        require_admin();
        user_delete();
        break;

    // ---- 反馈管理 ----
    case 'feedback/list':
        require_admin();
        feedback_list();
        break;
    case 'feedback/update':
        require_admin();
        feedback_update();
        break;

    // ---- 线报配置 ----
    case 'report/list':
        require_admin();
        report_list();
        break;
    case 'report/create':
        require_admin();
        report_create();
        break;
    case 'report/update':
        require_admin();
        report_update();
        break;
    case 'report/delete':
        require_admin();
        report_delete();
        break;

    // ---- 系统配置 ----
    case 'config/get':
        require_admin();
        config_get();
        break;
    case 'config/save':
        require_admin();
        config_save();
        break;

    // ---- 管理员自身 ----
    case 'admin/profile':
        require_admin();
        admin_profile();
        break;
    case 'admin/password':
        require_admin();
        admin_password();
        break;

    default:
        Response::notFound('接口不存在：' . $action);
        break;
}

// ============================================================
// 认证辅助
// ============================================================

/** 强制要求管理员登录，并返回 payload */
function require_admin()
{
    return Auth::requireAdmin();
}

/** 获取当前管理员ID */
function admin_id()
{
    $payload = Auth::requireAdmin();
    return (int) $payload['user_id'];
}

// ============================================================
// 管理员认证
// ============================================================

/** POST /admin/login 管理员登录 */
function admin_login()
{
    $username = clean(param('username'));
    $password = param('password');

    if (empty($username) || empty($password)) {
        Response::error('用户名和密码不能为空');
    }

    $db = Database::getInstance();
    $admin = $db->fetch('SELECT * FROM admin WHERE username = ? LIMIT 1', [$username]);
    if (!$admin) {
        Response::error('账号或密码错误');
    }
    if ($admin['status'] != 1) {
        Response::error('账号已被禁用');
    }
    if (!Auth::verifyPassword($password, $admin['password'])) {
        Response::error('账号或密码错误');
    }

    $db->update('admin', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$admin['id']]);
    $token = Auth::generateToken($admin['id'], 'admin');

    Response::success([
        'admin_id' => (int) $admin['id'],
        'username' => $admin['username'],
        'name'     => $admin['name'],
        'avatar'   => $admin['avatar'],
        'role'     => $admin['role'],
        'token'    => $token,
    ], '登录成功');
}

/** GET /admin/dashboard 仪表盘统计 */
function admin_dashboard()
{
    $db = Database::getInstance();
    $data = [
        'software_count'      => $db->value('SELECT COUNT(*) FROM software'),
        'user_count'          => $db->value('SELECT COUNT(*) FROM user'),
        'download_count'      => $db->value('SELECT COALESCE(SUM(download_count),0) FROM software'),
        'category_count'      => $db->value('SELECT COUNT(*) FROM category'),
        'feedback_pending'    => $db->value("SELECT COUNT(*) FROM feedback WHERE status = 0"),
        'today_register'      => $db->value('SELECT COUNT(*) FROM user WHERE DATE(register_time) = CURDATE()'),
        'today_download'      => $db->value('SELECT COUNT(*) FROM download_record WHERE DATE(create_time) = CURDATE()'),
        // 近7天下载趋势
        'download_trend'      => $db->fetchAll(
            "SELECT DATE(create_time) AS date, COUNT(*) AS count FROM download_record
             WHERE create_time >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
             GROUP BY DATE(create_time) ORDER BY date ASC"
        ),
        // 近7天注册趋势
        'register_trend'      => $db->fetchAll(
            "SELECT DATE(register_time) AS date, COUNT(*) AS count FROM user
             WHERE register_time >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
             GROUP BY DATE(register_time) ORDER BY date ASC"
        ),
        // 软件下载排行 Top5
        'download_rank'       => $db->fetchAll(
            'SELECT id, name, icon, download_count FROM software ORDER BY download_count DESC LIMIT 5'
        ),
    ];
    Response::success($data, '获取成功');
}

/** POST /admin/upload 通用文件上传 */
function admin_upload()
{
    if (empty($_FILES)) {
        Response::error('请选择要上传的文件');
    }
    $file = reset($_FILES);
    $type = clean(param('type', 'image'));
    if ($type === 'apk') {
        $result = Uploader::uploadApk($file);
    } elseif ($type === 'file') {
        $result = Uploader::uploadFile($file);
    } else {
        $result = Uploader::uploadImage($file);
    }
    if (!$result['success']) {
        Response::error($result['message']);
    }
    Response::success([
        'url'  => $result['url'],
        'path' => $result['path'],
        'size' => $result['size'],
    ], '上传成功');
}

// ============================================================
// 软件 CRUD
// ============================================================

function software_list()
{
    $db = Database::getInstance();
    $categoryId = (int) param('category_id', 0);
    $keyword    = clean(param('keyword'));
    $status     = param('status', '');
    list($page, $size, $offset) = pagination();

    $where = '1=1';
    $params = [];
    if ($categoryId > 0) {
        $where .= ' AND s.category_id = ?';
        $params[] = $categoryId;
    }
    if ($keyword !== '') {
        $where .= ' AND s.name LIKE ?';
        $params[] = '%' . $keyword . '%';
    }
    if ($status !== '' && $status !== null) {
        $where .= ' AND s.status = ?';
        $params[] = (int) $status;
    }

    $total = $db->value('SELECT COUNT(*) FROM software s WHERE ' . $where, $params);
    $list = $db->fetchAll(
        'SELECT s.id, s.name, s.category_id, s.icon, s.version, s.size, s.download_url, s.download_count, s.sort, s.status, s.is_hot, s.is_recommend, s.create_time, s.update_time, c.name AS category_name'
        . ' FROM software s LEFT JOIN category c ON s.category_id = c.id'
        . ' WHERE ' . $where
        . ' ORDER BY s.sort DESC, s.id DESC LIMIT ' . $offset . ', ' . $size,
        $params
    );
    Response::paginate($list, $total, $page, $size);
}

function software_detail()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $db = Database::getInstance();
    $row = $db->fetch('SELECT * FROM software WHERE id = ?', [$id]);
    if (!$row) {
        Response::notFound('软件不存在');
    }
    $row['screenshots'] = from_json($row['screenshots'], []);
    Response::success($row, '获取成功');
}

function software_create()
{
    $data = software_validate();
    $db = Database::getInstance();
    $id = $db->insert('software', $data);
    Response::success(['id' => $id], '创建成功');
}

function software_update()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $data = software_validate(true);
    $db = Database::getInstance();
    $db->update('software', $data, 'id = ?', [$id]);
    Response::success(null, '更新成功');
}

function software_delete()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $db = Database::getInstance();
    $db->delete('software', 'id = ?', [$id]);
    Response::success(null, '删除成功');
}

/** 校验并组装软件数据 */
function software_validate($isUpdate = false)
{
    $name        = clean(param('name'));
    $categoryId  = (int) param('category_id', 0);
    $version     = clean(param('version'));
    $size        = clean(param('size'));
    $downloadUrl = clean(param('download_url'));
    $icon        = clean(param('icon'));
    $description = param('description', '');
    $sort        = (int) param('sort', 0);
    $status      = (int) param('status', 1);
    $isHot       = (int) param('is_hot', 0);
    $isRecommend = (int) param('is_recommend', 0);
    $screenshots = param('screenshots', []);

    if (empty($name)) {
        Response::error('软件名称不能为空');
    }

    // screenshots 转 JSON
    if (is_array($screenshots)) {
        $screenshots = to_json($screenshots);
    }

    $data = [
        'name'          => $name,
        'category_id'   => $categoryId,
        'icon'          => $icon,
        'description'   => $description,
        'version'       => $version,
        'size'          => $size,
        'download_url'  => $downloadUrl,
        'screenshots'   => $screenshots,
        'sort'          => $sort,
        'status'        => $status ? 1 : 0,
        'is_hot'        => $isHot ? 1 : 0,
        'is_recommend'  => $isRecommend ? 1 : 0,
    ];
    if (!$isUpdate) {
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['download_count'] = 0;
    }
    return $data;
}

// ============================================================
// 分类 CRUD
// ============================================================

function category_list()
{
    $db = Database::getInstance();
    $keyword = clean(param('keyword'));
    $where = '1=1';
    $params = [];
    if ($keyword !== '') {
        $where .= ' AND name LIKE ?';
        $params[] = '%' . $keyword . '%';
    }
    $list = $db->fetchAll(
        'SELECT c.*, (SELECT COUNT(*) FROM software s WHERE s.category_id = c.id) AS software_count'
        . ' FROM category c WHERE ' . $where . ' ORDER BY c.sort DESC, c.id ASC',
        $params
    );
    Response::success($list, '获取成功');
}

function category_create()
{
    $name = clean(param('name'));
    $icon = clean(param('icon'));
    $sort = (int) param('sort', 0);
    $status = (int) param('status', 1);
    if (empty($name)) {
        Response::error('分类名称不能为空');
    }
    $db = Database::getInstance();
    $exists = $db->fetch('SELECT id FROM category WHERE name = ?', [$name]);
    if ($exists) {
        Response::error('分类名称已存在');
    }
    $id = $db->insert('category', [
        'name'   => $name,
        'icon'   => $icon,
        'sort'   => $sort,
        'status' => $status ? 1 : 0,
    ]);
    Response::success(['id' => $id], '创建成功');
}

function category_update()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $name = clean(param('name'));
    $icon = clean(param('icon'));
    $sort = (int) param('sort', 0);
    $status = (int) param('status', 1);
    if (empty($name)) {
        Response::error('分类名称不能为空');
    }
    $db = Database::getInstance();
    $db->update('category', [
        'name'   => $name,
        'icon'   => $icon,
        'sort'   => $sort,
        'status' => $status ? 1 : 0,
    ], 'id = ?', [$id]);
    Response::success(null, '更新成功');
}

function category_delete()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $db = Database::getInstance();
    // 检查是否有软件使用
    $count = $db->value('SELECT COUNT(*) FROM software WHERE category_id = ?', [$id]);
    if ($count > 0) {
        Response::error('该分类下还有 ' . $count . ' 个软件，无法删除');
    }
    $db->delete('category', 'id = ?', [$id]);
    Response::success(null, '删除成功');
}

// ============================================================
// 轮播图 CRUD
// ============================================================

function banner_list()
{
    $db = Database::getInstance();
    $list = $db->fetchAll('SELECT * FROM banner ORDER BY sort DESC, id ASC');
    Response::success($list, '获取成功');
}

function banner_create()
{
    $title  = clean(param('title'));
    $image  = clean(param('image'));
    $link   = clean(param('link'));
    $sort   = (int) param('sort', 0);
    $status = (int) param('status', 1);
    if (empty($title)) {
        Response::error('标题不能为空');
    }
    if (empty($image)) {
        Response::error('图片不能为空');
    }
    $db = Database::getInstance();
    $id = $db->insert('banner', [
        'title'  => $title,
        'image'  => $image,
        'link'   => $link,
        'sort'   => $sort,
        'status' => $status ? 1 : 0,
    ]);
    Response::success(['id' => $id], '创建成功');
}

function banner_update()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $title  = clean(param('title'));
    $image  = clean(param('image'));
    $link   = clean(param('link'));
    $sort   = (int) param('sort', 0);
    $status = (int) param('status', 1);
    if (empty($title)) {
        Response::error('标题不能为空');
    }
    $db = Database::getInstance();
    $db->update('banner', [
        'title'  => $title,
        'image'  => $image,
        'link'   => $link,
        'sort'   => $sort,
        'status' => $status ? 1 : 0,
    ], 'id = ?', [$id]);
    Response::success(null, '更新成功');
}

function banner_delete()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $db = Database::getInstance();
    $db->delete('banner', 'id = ?', [$id]);
    Response::success(null, '删除成功');
}

// ============================================================
// 公告 CRUD
// ============================================================

function notice_list()
{
    $db = Database::getInstance();
    $list = $db->fetchAll('SELECT id, title, type, sort, status, create_time, update_time, LEFT(content,80) AS content_preview FROM notice ORDER BY sort DESC, id ASC');
    Response::success($list, '获取成功');
}

function notice_create()
{
    $title   = clean(param('title'));
    $content = param('content', '');
    $type    = clean(param('type', 'static'));
    $sort    = (int) param('sort', 0);
    $status  = (int) param('status', 1);
    if (empty($title)) {
        Response::error('标题不能为空');
    }
    if (!in_array($type, ['scroll', 'static'], true)) {
        $type = 'static';
    }
    $db = Database::getInstance();
    $id = $db->insert('notice', [
        'title'   => $title,
        'content' => $content,
        'type'    => $type,
        'sort'    => $sort,
        'status'  => $status ? 1 : 0,
    ]);
    Response::success(['id' => $id], '创建成功');
}

function notice_update()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $title   = clean(param('title'));
    $content = param('content', '');
    $type    = clean(param('type', 'static'));
    $sort    = (int) param('sort', 0);
    $status  = (int) param('status', 1);
    if (empty($title)) {
        Response::error('标题不能为空');
    }
    if (!in_array($type, ['scroll', 'static'], true)) {
        $type = 'static';
    }
    $db = Database::getInstance();
    $db->update('notice', [
        'title'   => $title,
        'content' => $content,
        'type'    => $type,
        'sort'    => $sort,
        'status'  => $status ? 1 : 0,
    ], 'id = ?', [$id]);
    Response::success(null, '更新成功');
}

function notice_delete()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $db = Database::getInstance();
    $db->delete('notice', 'id = ?', [$id]);
    Response::success(null, '删除成功');
}

// ============================================================
// 工具栏 CRUD
// ============================================================

function toolbar_list()
{
    $db = Database::getInstance();
    $list = $db->fetchAll('SELECT * FROM toolbar ORDER BY sort DESC, id ASC');
    Response::success($list, '获取成功');
}

function toolbar_create()
{
    $name   = clean(param('name'));
    $icon   = clean(param('icon'));
    $link   = clean(param('link'));
    $sort   = (int) param('sort', 0);
    $status = (int) param('status', 1);
    $isShow = (int) param('is_show', 1);
    if (empty($name)) {
        Response::error('名称不能为空');
    }
    $db = Database::getInstance();
    $id = $db->insert('toolbar', [
        'name'   => $name,
        'icon'   => $icon,
        'link'   => $link,
        'sort'   => $sort,
        'status' => $status ? 1 : 0,
        'is_show'=> $isShow ? 1 : 0,
    ]);
    Response::success(['id' => $id], '创建成功');
}

function toolbar_update()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $name   = clean(param('name'));
    $icon   = clean(param('icon'));
    $link   = clean(param('link'));
    $sort   = (int) param('sort', 0);
    $status = (int) param('status', 1);
    $isShow = (int) param('is_show', 1);
    if (empty($name)) {
        Response::error('名称不能为空');
    }
    $db = Database::getInstance();
    $db->update('toolbar', [
        'name'   => $name,
        'icon'   => $icon,
        'link'   => $link,
        'sort'   => $sort,
        'status' => $status ? 1 : 0,
        'is_show'=> $isShow ? 1 : 0,
    ], 'id = ?', [$id]);
    Response::success(null, '更新成功');
}

function toolbar_delete()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $db = Database::getInstance();
    $db->delete('toolbar', 'id = ?', [$id]);
    Response::success(null, '删除成功');
}

// ============================================================
// 广告 CRUD
// ============================================================

function advertisement_list()
{
    $db = Database::getInstance();
    $position = clean(param('position'));
    $where = '1=1';
    $params = [];
    if ($position !== '') {
        $where .= ' AND position = ?';
        $params[] = $position;
    }
    $list = $db->fetchAll('SELECT * FROM advertisement WHERE ' . $where . ' ORDER BY sort DESC, id ASC', $params);
    Response::success($list, '获取成功');
}

function advertisement_create()
{
    $name     = clean(param('name'));
    $image    = clean(param('image'));
    $link     = clean(param('link'));
    $position = clean(param('position', 'home'));
    $sort     = (int) param('sort', 0);
    $status   = (int) param('status', 1);
    if (empty($name)) {
        Response::error('广告名称不能为空');
    }
    if (empty($image)) {
        Response::error('广告图片不能为空');
    }
    if (!in_array($position, ['splash', 'home', 'detail'], true)) {
        $position = 'home';
    }
    $db = Database::getInstance();
    $id = $db->insert('advertisement', [
        'name'     => $name,
        'image'    => $image,
        'link'     => $link,
        'position' => $position,
        'sort'     => $sort,
        'status'   => $status ? 1 : 0,
    ]);
    Response::success(['id' => $id], '创建成功');
}

function advertisement_update()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $name     = clean(param('name'));
    $image    = clean(param('image'));
    $link     = clean(param('link'));
    $position = clean(param('position', 'home'));
    $sort     = (int) param('sort', 0);
    $status   = (int) param('status', 1);
    if (empty($name)) {
        Response::error('广告名称不能为空');
    }
    if (!in_array($position, ['splash', 'home', 'detail'], true)) {
        $position = 'home';
    }
    $db = Database::getInstance();
    $db->update('advertisement', [
        'name'     => $name,
        'image'    => $image,
        'link'     => $link,
        'position' => $position,
        'sort'     => $sort,
        'status'   => $status ? 1 : 0,
    ], 'id = ?', [$id]);
    Response::success(null, '更新成功');
}

function advertisement_delete()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $db = Database::getInstance();
    $db->delete('advertisement', 'id = ?', [$id]);
    Response::success(null, '删除成功');
}

// ============================================================
// 用户管理
// ============================================================

function user_list()
{
    $db = Database::getInstance();
    $keyword = clean(param('keyword'));
    $status  = param('status', '');
    list($page, $size, $offset) = pagination();
    $where = '1=1';
    $params = [];
    if ($keyword !== '') {
        $where .= ' AND (username LIKE ? OR nickname LIKE ? OR phone LIKE ? OR email LIKE ?)';
        $params[] = '%' . $keyword . '%';
        $params[] = '%' . $keyword . '%';
        $params[] = '%' . $keyword . '%';
        $params[] = '%' . $keyword . '%';
    }
    if ($status !== '' && $status !== null) {
        $where .= ' AND status = ?';
        $params[] = (int) $status;
    }
    $total = $db->value('SELECT COUNT(*) FROM user WHERE ' . $where, $params);
    $list = $db->fetchAll(
        'SELECT id, username, phone, email, avatar, nickname, status, register_time, last_login'
        . ' FROM user WHERE ' . $where . ' ORDER BY id DESC LIMIT ' . $offset . ', ' . $size,
        $params
    );
    Response::paginate($list, $total, $page, $size);
}

function user_detail()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $db = Database::getInstance();
    $row = $db->fetch('SELECT id, username, phone, email, avatar, nickname, status, register_time, last_login FROM user WHERE id = ?', [$id]);
    if (!$row) {
        Response::notFound('用户不存在');
    }
    Response::success($row, '获取成功');
}

function user_update()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $nickname = clean(param('nickname'));
    $phone    = clean(param('phone'));
    $email    = clean(param('email'));
    $avatar   = clean(param('avatar'));
    $status   = (int) param('status', 1);
    $password = param('password');

    $data = [
        'nickname' => $nickname,
        'phone'    => $phone,
        'email'    => $email,
        'avatar'   => $avatar,
        'status'   => $status ? 1 : 0,
    ];
    if (!empty($password)) {
        $data['password'] = Auth::hashPassword($password);
    }
    $db = Database::getInstance();
    $db->update('user', $data, 'id = ?', [$id]);
    Response::success(null, '更新成功');
}

function user_delete()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $db = Database::getInstance();
    $db->delete('user', 'id = ?', [$id]);
    // 清理关联数据
    $db->delete('favorite', 'user_id = ?', [$id]);
    $db->delete('download_record', 'user_id = ?', [$id]);
    Response::success(null, '删除成功');
}

// ============================================================
// 反馈管理
// ============================================================

function feedback_list()
{
    $db = Database::getInstance();
    $status = param('status', '');
    list($page, $size, $offset) = pagination();
    $where = '1=1';
    $params = [];
    if ($status !== '' && $status !== null) {
        $where .= ' AND f.status = ?';
        $params[] = (int) $status;
    }
    $total = $db->value('SELECT COUNT(*) FROM feedback f WHERE ' . $where, $params);
    $list = $db->fetchAll(
        'SELECT f.id, f.user_id, f.content, f.contact, f.status, f.create_time, u.username, u.nickname'
        . ' FROM feedback f LEFT JOIN user u ON f.user_id = u.id'
        . ' WHERE ' . $where . ' ORDER BY f.id DESC LIMIT ' . $offset . ', ' . $size,
        $params
    );
    Response::paginate($list, $total, $page, $size);
}

function feedback_update()
{
    $id = (int) param('id', 0);
    $status = (int) param('status', 1);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $db = Database::getInstance();
    $db->update('feedback', ['status' => $status ? 1 : 0], 'id = ?', [$id]);
    Response::success(null, '更新成功');
}

// ============================================================
// 线报配置
// ============================================================

function report_list()
{
    $db = Database::getInstance();
    $list = $db->fetchAll('SELECT * FROM report_config ORDER BY id DESC');
    Response::success($list, '获取成功');
}

function report_create()
{
    $name      = clean(param('name'));
    $sourceUrl = clean(param('source_url'));
    $status    = (int) param('status', 1);
    if (empty($name)) {
        Response::error('线报名称不能为空');
    }
    $db = Database::getInstance();
    $id = $db->insert('report_config', [
        'name'       => $name,
        'source_url' => $sourceUrl,
        'status'     => $status ? 1 : 0,
    ]);
    Response::success(['id' => $id], '创建成功');
}

function report_update()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $name      = clean(param('name'));
    $sourceUrl = clean(param('source_url'));
    $status    = (int) param('status', 1);
    if (empty($name)) {
        Response::error('线报名称不能为空');
    }
    $db = Database::getInstance();
    $db->update('report_config', [
        'name'       => $name,
        'source_url' => $sourceUrl,
        'status'     => $status ? 1 : 0,
    ], 'id = ?', [$id]);
    Response::success(null, '更新成功');
}

function report_delete()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $db = Database::getInstance();
    $db->delete('report_config', 'id = ?', [$id]);
    Response::success(null, '删除成功');
}

// ============================================================
// 系统配置
// ============================================================

function config_get()
{
    $db = Database::getInstance();
    $list = $db->fetchAll('SELECT id, config_key, config_value, description FROM config ORDER BY id ASC');
    $config = [];
    foreach ($list as $row) {
        $config[$row['config_key']] = $row['config_value'];
    }
    Response::success([
        'list'   => $list,
        'config' => $config,
    ], '获取成功');
}

function config_save()
{
    $data = param('data');
    if (!is_array($data) || empty($data)) {
        Response::error('配置数据不能为空');
    }
    $db = Database::getInstance();
    foreach ($data as $key => $value) {
        $exists = $db->fetch('SELECT id FROM config WHERE config_key = ?', [$key]);
        if ($exists) {
            $db->update('config', ['config_value' => (string) $value], 'config_key = ?', [$key]);
        } else {
            $db->insert('config', [
                'config_key'   => $key,
                'config_value' => (string) $value,
                'description'  => '',
            ]);
        }
    }
    Response::success(null, '保存成功');
}

// ============================================================
// 管理员自身
// ============================================================

function admin_profile()
{
    $id = admin_id();
    $db = Database::getInstance();
    $row = $db->fetch('SELECT id, username, name, avatar, role, status, create_time, last_login FROM admin WHERE id = ?', [$id]);
    Response::success($row, '获取成功');
}

function admin_password()
{
    $id = admin_id();
    $oldPassword = param('old_password');
    $newPassword = param('new_password');
    if (empty($oldPassword) || empty($newPassword)) {
        Response::error('原密码和新密码不能为空');
    }
    if (mb_strlen($newPassword) < 6) {
        Response::error('新密码长度不能少于6位');
    }
    $db = Database::getInstance();
    $admin = $db->fetch('SELECT password FROM admin WHERE id = ?', [$id]);
    if (!Auth::verifyPassword($oldPassword, $admin['password'])) {
        Response::error('原密码错误');
    }
    $db->update('admin', ['password' => Auth::hashPassword($newPassword)], 'id = ?', [$id]);
    Response::success(null, '密码修改成功');
}
