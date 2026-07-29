<?php
/**
 * App 端 API 路由入口
 * 支持两种访问方式：
 *   1. ?action=software/list  （兼容无重写环境）
 *   2. /api/app/index.php/software/list （PATH_INFO）
 * 用户接口（收藏/资料/下载记录）需携带 token 头
 */

// 引入核心文件
require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/functions.php';

// 处理跨域预检
Response::handleOptions();

// 获取动作路由
$action = isset($_GET['action']) ? $_GET['action'] : '';
if (empty($action) && isset($_SERVER['PATH_INFO'])) {
    $action = trim($_SERVER['PATH_INFO'], '/');
}
if (empty($action)) {
    $action = 'index';
}

api_log('app/' . $action, all_params());

// 路由分发
switch ($action) {
    // ---- 通用 ----
    case '':
    case 'index':
        Response::success(['name' => SITE_NAME, 'version' => SYSTEM_VERSION], '软件库API服务');
        break;

    case 'config':              // GET 获取应用配置
        app_config();
        break;

    case 'register':            // POST 用户注册
        app_register();
        break;

    case 'login':               // POST 用户登录
        app_login();
        break;

    case 'send_code':           // POST 发送验证码
        app_send_code();
        break;

    // ---- 内容展示 ----
    case 'banner':              // GET 轮播图
        app_banner();
        break;

    case 'notice':              // GET 公告列表
        app_notice_list();
        break;

    case 'notice/detail':       // GET 公告详情
        app_notice_detail();
        break;

    case 'category':            // GET 分类列表
        app_category();
        break;

    case 'toolbar':             // GET 工具栏
        app_toolbar();
        break;

    case 'advertisement':       // GET 广告
        app_advertisement();
        break;

    case 'software/list':       // GET 软件列表
        app_software_list();
        break;

    case 'software/detail':     // GET 软件详情
        app_software_detail();
        break;

    case 'software/hot':        // GET 热门软件
        app_software_hot();
        break;

    case 'software/recommend':  // GET 推荐软件
        app_software_recommend();
        break;

    case 'software/download':   // GET 记录下载
        app_software_download();
        break;

    case 'feedback':            // POST 提交反馈
        app_feedback();
        break;

    // ---- 用户中心（需登录） ----
    case 'user/profile':        // GET 用户信息
        app_user_profile();
        break;

    case 'user/update':         // POST 更新用户信息
        app_user_update();
        break;

    case 'user/favorites':      // GET 收藏列表
        app_user_favorites();
        break;

    case 'user/favorite':       // POST 添加/取消收藏
        app_user_favorite();
        break;

    case 'user/downloads':      // GET 下载记录
        app_user_downloads();
        break;

    default:
        Response::notFound('接口不存在：' . $action);
        break;
}

// ============================================================
// 接口实现
// ============================================================

/** GET /app/config 获取应用配置 */
function app_config()
{
    $db = Database::getInstance();
    $rows = $db->fetchAll('SELECT config_key, config_value FROM config');
    $config = [];
    foreach ($rows as $row) {
        $config[$row['config_key']] = $row['config_value'];
    }
    $config['phone_verify'] = (bool) PHONE_VERIFY;
    $config['email_verify'] = (bool) EMAIL_VERIFY;
    $config['app_version']  = SYSTEM_VERSION;
    Response::success($config, '获取成功');
}

/** POST /app/register 用户注册 */
function app_register()
{
    $username = clean(param('username'));
    $password = param('password');
    $phone    = clean(param('phone'));
    $email    = clean(param('email'));
    $nickname = clean(param('nickname'));
    $code     = clean(param('code'));

    // 基础校验
    list($pass, $error) = Validator::check(
        ['username' => $username, 'password' => $password],
        [
            'username' => 'required|string|length:3,50',
            'password' => 'required|string|min:6',
        ],
        ['username' => '用户名', 'password' => '密码']
    );
    if (!$pass) {
        Response::error($error);
    }

    // 注册开关
    if (get_config('register_switch', '1') !== '1') {
        Response::error('当前已关闭注册');
    }

    $db = Database::getInstance();

    // 用户名唯一性校验
    $exists = $db->fetch('SELECT id FROM user WHERE username = ?', [$username]);
    if ($exists) {
        Response::error('用户名已存在');
    }

    // 根据配置决定是否需要手机/邮箱验证
    if (PHONE_VERIFY) {
        if (empty($phone)) {
            Response::error('请输入手机号');
        }
        if (!is_phone($phone)) {
            Response::error('手机号格式不正确');
        }
        if (empty($code)) {
            Response::error('请输入手机验证码');
        }
        // 校验验证码
        if (!verify_code($phone, $code, 'register')) {
            Response::error('验证码错误或已过期');
        }
        $phoneExists = $db->fetch('SELECT id FROM user WHERE phone = ?', [$phone]);
        if ($phoneExists) {
            Response::error('手机号已被注册');
        }
    }

    if (EMAIL_VERIFY) {
        if (empty($email)) {
            Response::error('请输入邮箱');
        }
        if (!is_email($email)) {
            Response::error('邮箱格式不正确');
        }
        if (empty($code)) {
            Response::error('请输入邮箱验证码');
        }
        if (!verify_code($email, $code, 'register')) {
            Response::error('验证码错误或已过期');
        }
        $emailExists = $db->fetch('SELECT id FROM user WHERE email = ?', [$email]);
        if ($emailExists) {
            Response::error('邮箱已被注册');
        }
    }

    if (empty($nickname)) {
        $nickname = '用户' . substr($username, 0, 6);
    }

    $hash = Auth::hashPassword($password);
    $userId = $db->insert('user', [
        'username'      => $username,
        'password'      => $hash,
        'phone'         => $phone,
        'email'         => $email,
        'avatar'        => '',
        'nickname'      => $nickname,
        'status'        => 1,
        'register_time' => date('Y-m-d H:i:s'),
        'last_login'    => date('Y-m-d H:i:s'),
    ]);

    if (!$userId) {
        Response::error('注册失败，请重试');
    }

    $token = Auth::generateToken($userId, 'user');
    Response::success([
        'user_id'  => $userId,
        'username' => $username,
        'nickname' => $nickname,
        'token'    => $token,
    ], '注册成功');
}

/** POST /app/login 用户登录 */
function app_login()
{
    $username = clean(param('username'));
    $password = param('password');

    if (empty($username) || empty($password)) {
        Response::error('用户名和密码不能为空');
    }

    $db = Database::getInstance();
    $user = $db->fetch('SELECT * FROM user WHERE username = ? LIMIT 1', [$username]);
    if (!$user) {
        Response::error('用户不存在');
    }
    if ($user['status'] != 1) {
        Response::error('账号已被禁用，请联系管理员');
    }
    if (!Auth::verifyPassword($password, $user['password'])) {
        Response::error('密码错误');
    }

    $db->update('user', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
    $token = Auth::generateToken($user['id'], 'user');

    unset($user['password']);
    Response::success([
        'user_id'  => (int) $user['id'],
        'username' => $user['username'],
        'nickname' => $user['nickname'],
        'avatar'   => $user['avatar'],
        'token'    => $token,
    ], '登录成功');
}

/** POST /app/send_code 发送验证码 */
function app_send_code()
{
    $account = clean(param('account'));
    $type    = clean(param('type', 'register'));

    if (empty($account)) {
        Response::error('账号不能为空');
    }
    if (is_phone($account)) {
        $accountType = 'phone';
    } elseif (is_email($account)) {
        $accountType = 'email';
    } else {
        Response::error('账号格式不正确（需为手机号或邮箱）');
    }

    // 手机/邮箱验证开关
    if ($accountType === 'phone' && !PHONE_VERIFY) {
        Response::error('手机验证未开启');
    }
    if ($accountType === 'email' && !EMAIL_VERIFY) {
        Response::error('邮箱验证未开启');
    }

    // 发送频率限制
    $db = Database::getInstance();
    $recent = $db->fetch(
        'SELECT id FROM verification_code WHERE account = ? AND create_time >= ? ORDER BY id DESC LIMIT 1',
        [$account, date('Y-m-d H:i:s', time() - CODE_SEND_INTERVAL)]
    );
    if ($recent) {
        Response::error('发送过于频繁，请稍后再试');
    }

    $code = generate_code();
    $expireTime = date('Y-m-d H:i:s', time() + CODE_EXPIRE);

    $db->insert('verification_code', [
        'account'     => $account,
        'code'        => $code,
        'type'        => $type,
        'expire_time' => $expireTime,
        'status'      => 0,
    ]);

    // 实际项目中此处应调用短信/邮件服务发送验证码
    // 演示环境直接返回验证码
    Response::success([
        'account' => $account,
        'code'    => $code, // 生产环境应移除
    ], '验证码已发送');
}

/** GET /app/banner 获取轮播图 */
function app_banner()
{
    $db = Database::getInstance();
    $list = $db->fetchAll(
        'SELECT id, title, image, link, sort FROM banner WHERE status = 1 ORDER BY sort DESC, id ASC'
    );
    Response::success($list, '获取成功');
}

/** GET /app/notice 获取公告列表 */
function app_notice_list()
{
    $type = clean(param('type'));
    $db = Database::getInstance();
    $sql = 'SELECT id, title, content, type, sort, create_time FROM notice WHERE status = 1';
    $params = [];
    if ($type !== '') {
        $sql .= ' AND type = ?';
        $params[] = $type;
    }
    $sql .= ' ORDER BY sort DESC, id ASC';
    $list = $db->fetchAll($sql, $params);
    Response::success($list, '获取成功');
}

/** GET /app/notice/detail 获取公告详情 */
function app_notice_detail()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $db = Database::getInstance();
    $row = $db->fetch('SELECT id, title, content, type, create_time, update_time FROM notice WHERE id = ? AND status = 1', [$id]);
    if (!$row) {
        Response::notFound('公告不存在');
    }
    Response::success($row, '获取成功');
}

/** GET /app/category 获取分类列表 */
function app_category()
{
    $db = Database::getInstance();
    $list = $db->fetchAll(
        'SELECT id, name, icon, sort FROM category WHERE status = 1 ORDER BY sort DESC, id ASC'
    );
    Response::success($list, '获取成功');
}

/** GET /app/toolbar 获取工具栏 */
function app_toolbar()
{
    $db = Database::getInstance();
    $list = $db->fetchAll(
        'SELECT id, name, icon, link, sort FROM toolbar WHERE status = 1 AND is_show = 1 ORDER BY sort DESC, id ASC'
    );
    Response::success($list, '获取成功');
}

/** GET /app/advertisement 获取广告 */
function app_advertisement()
{
    $position = clean(param('position', 'home'));
    $db = Database::getInstance();
    $list = $db->fetchAll(
        'SELECT id, name, image, link, position, sort FROM advertisement WHERE status = 1 AND position = ? ORDER BY sort DESC, id ASC',
        [$position]
    );
    Response::success($list, '获取成功');
}

/** GET /app/software/list 获取软件列表 */
function app_software_list()
{
    $categoryId = (int) param('category_id', 0);
    $keyword    = clean(param('keyword'));
    list($page, $size, $offset) = pagination();

    $db = Database::getInstance();
    $where = 's.status = 1';
    $params = [];
    if ($categoryId > 0) {
        $where .= ' AND s.category_id = ?';
        $params[] = $categoryId;
    }
    if ($keyword !== '') {
        $where .= ' AND s.name LIKE ?';
        $params[] = '%' . $keyword . '%';
    }

    $total = $db->value('SELECT COUNT(*) FROM software s WHERE ' . $where, $params);

    $sql = 'SELECT s.id, s.name, s.category_id, s.icon, s.description, s.version, s.size, s.download_url, s.download_count, s.is_hot, s.is_recommend, s.create_time, c.name AS category_name'
         . ' FROM software s LEFT JOIN category c ON s.category_id = c.id'
         . ' WHERE ' . $where
         . ' ORDER BY s.sort DESC, s.id DESC LIMIT ' . $offset . ', ' . $size;
    $list = $db->fetchAll($sql, $params);

    Response::paginate($list, $total, $page, $size);
}

/** GET /app/software/detail 获取软件详情 */
function app_software_detail()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $db = Database::getInstance();
    $row = $db->fetch(
        'SELECT s.*, c.name AS category_name FROM software s LEFT JOIN category c ON s.category_id = c.id WHERE s.id = ? AND s.status = 1',
        [$id]
    );
    if (!$row) {
        Response::notFound('软件不存在');
    }
    $row['screenshots'] = from_json($row['screenshots'], []);
    $row['is_favorited'] = false;
    $userId = Auth::getUserId();
    if ($userId) {
        $fav = $db->fetch('SELECT id FROM favorite WHERE user_id = ? AND software_id = ?', [$userId, $id]);
        $row['is_favorited'] = $fav ? true : false;
    }
    Response::success($row, '获取成功');
}

/** GET /app/software/hot 获取热门软件 */
function app_software_hot()
{
    $limit = (int) param('limit', 10);
    $limit = min(max(1, $limit), 50);
    $db = Database::getInstance();
    $list = $db->fetchAll(
        'SELECT id, name, category_id, icon, version, size, download_count FROM software WHERE status = 1 AND is_hot = 1 ORDER BY download_count DESC, sort DESC LIMIT ' . $limit
    );
    Response::success($list, '获取成功');
}

/** GET /app/software/recommend 获取推荐软件 */
function app_software_recommend()
{
    $limit = (int) param('limit', 10);
    $limit = min(max(1, $limit), 50);
    $db = Database::getInstance();
    $list = $db->fetchAll(
        'SELECT id, name, category_id, icon, version, size, download_count FROM software WHERE status = 1 AND is_recommend = 1 ORDER BY sort DESC, id DESC LIMIT ' . $limit
    );
    Response::success($list, '获取成功');
}

/** GET /app/software/download 记录下载 */
function app_software_download()
{
    $id = (int) param('id', 0);
    if ($id <= 0) {
        Response::error('参数错误');
    }
    $db = Database::getInstance();
    $row = $db->fetch('SELECT id, download_url FROM software WHERE id = ? AND status = 1', [$id]);
    if (!$row) {
        Response::notFound('软件不存在');
    }
    // 下载次数 +1
    $db->query('UPDATE software SET download_count = download_count + 1 WHERE id = ?', [$id]);
    // 记录下载历史
    $userId = Auth::getUserId();
    if ($userId) {
        $db->insert('download_record', [
            'user_id'     => $userId,
            'software_id' => $id,
            'create_time' => date('Y-m-d H:i:s'),
        ]);
    }
    Response::success(['download_url' => $row['download_url']], '获取成功');
}

/** POST /app/feedback 提交反馈 */
function app_feedback()
{
    $content = clean(param('content'));
    $contact = clean(param('contact'));
    $userId  = Auth::getUserId();

    if (empty($content)) {
        Response::error('反馈内容不能为空');
    }

    $db = Database::getInstance();
    $db->insert('feedback', [
        'user_id'     => $userId ? $userId : 0,
        'content'     => $content,
        'contact'     => $contact,
        'status'      => 0,
        'create_time' => date('Y-m-d H:i:s'),
    ]);
    Response::success(null, '反馈成功，感谢您的支持');
}

/** GET /app/user/profile 获取用户信息 */
function app_user_profile()
{
    $payload = Auth::requireUser();
    $userId = (int) $payload['user_id'];
    $db = Database::getInstance();
    $user = $db->fetch('SELECT id, username, phone, email, avatar, nickname, status, register_time, last_login FROM user WHERE id = ?', [$userId]);
    if (!$user) {
        Response::notFound('用户不存在');
    }
    Response::success($user, '获取成功');
}

/** POST /app/user/update 更新用户信息 */
function app_user_update()
{
    $payload = Auth::requireUser();
    $userId = (int) $payload['user_id'];
    $data = [];
    foreach (['nickname', 'avatar', 'phone', 'email'] as $field) {
        $val = param($field);
        if ($val !== null) {
            $data[$field] = clean($val);
        }
    }
    if (isset($data['phone']) && $data['phone'] !== '' && !is_phone($data['phone'])) {
        Response::error('手机号格式不正确');
    }
    if (isset($data['email']) && $data['email'] !== '' && !is_email($data['email'])) {
        Response::error('邮箱格式不正确');
    }
    if (empty($data)) {
        Response::error('没有需要更新的字段');
    }
    $db = Database::getInstance();
    $db->update('user', $data, 'id = ?', [$userId]);
    Response::success(null, '更新成功');
}

/** GET /app/user/favorites 获取收藏列表 */
function app_user_favorites()
{
    $payload = Auth::requireUser();
    $userId = (int) $payload['user_id'];
    list($page, $size, $offset) = pagination();
    $db = Database::getInstance();
    $total = $db->value('SELECT COUNT(*) FROM favorite WHERE user_id = ?', [$userId]);
    $list = $db->fetchAll(
        'SELECT f.id AS favorite_id, f.create_time AS favorite_time, s.id, s.name, s.icon, s.version, s.size, s.download_count'
        . ' FROM favorite f LEFT JOIN software s ON f.software_id = s.id'
        . ' WHERE f.user_id = ? ORDER BY f.id DESC LIMIT ' . $offset . ', ' . $size,
        [$userId]
    );
    Response::paginate($list, $total, $page, $size);
}

/** POST /app/user/favorite 添加/取消收藏 */
function app_user_favorite()
{
    $payload = Auth::requireUser();
    $userId = (int) $payload['user_id'];
    $softwareId = (int) param('software_id', 0);
    if ($softwareId <= 0) {
        Response::error('参数错误');
    }
    $db = Database::getInstance();
    $exists = $db->fetch('SELECT id FROM favorite WHERE user_id = ? AND software_id = ?', [$userId, $softwareId]);
    if ($exists) {
        $db->delete('favorite', 'id = ?', [$exists['id']]);
        Response::success(['is_favorited' => false], '已取消收藏');
    } else {
        $db->insert('favorite', [
            'user_id'     => $userId,
            'software_id' => $softwareId,
            'create_time' => date('Y-m-d H:i:s'),
        ]);
        Response::success(['is_favorited' => true], '收藏成功');
    }
}

/** GET /app/user/downloads 获取下载记录 */
function app_user_downloads()
{
    $payload = Auth::requireUser();
    $userId = (int) $payload['user_id'];
    list($page, $size, $offset) = pagination();
    $db = Database::getInstance();
    $total = $db->value('SELECT COUNT(*) FROM download_record WHERE user_id = ?', [$userId]);
    $list = $db->fetchAll(
        'SELECT d.id AS record_id, d.create_time AS download_time, s.id, s.name, s.icon, s.version, s.size'
        . ' FROM download_record d LEFT JOIN software s ON d.software_id = s.id'
        . ' WHERE d.user_id = ? ORDER BY d.id DESC LIMIT ' . $offset . ', ' . $size,
        [$userId]
    );
    Response::paginate($list, $total, $page, $size);
}

// ============================================================
// 辅助函数
// ============================================================

/**
 * 校验验证码
 * @param string $account 账号
 * @param string $code    验证码
 * @param string $type    类型
 * @return bool
 */
function verify_code($account, $code, $type)
{
    $db = Database::getInstance();
    $row = $db->fetch(
        'SELECT id, expire_time, status FROM verification_code WHERE account = ? AND code = ? AND type = ? ORDER BY id DESC LIMIT 1',
        [$account, $code, $type]
    );
    if (!$row) {
        return false;
    }
    if ($row['status'] == 1) {
        return false;
    }
    if (strtotime($row['expire_time']) < time()) {
        return false;
    }
    // 标记为已使用
    $db->update('verification_code', ['status' => 1], 'id = ?', [$row['id']]);
    return true;
}
