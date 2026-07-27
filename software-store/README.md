# 软件库 APP

## 项目概述

一个完整的软件分发/APP下载平台，包含 Android 客户端（Java）和 PHP 后台管理系统。

## 技术栈

### 后端 (PHP)
- PHP 7.4+ / 8.x
- MySQL 5.7+ / MariaDB
- PDO 数据库连接
- JWT 风格 Token 认证
- 原生轻量级 MVC 架构
- jQuery + Bootstrap (后台前端)

### Android 客户端
- Java (Android)
- minSdkVersion: 24
- targetSdkVersion: 34
- Architecture: MVVM
- Retrofit 2 (网络请求)
- OkHttp (HTTP 客户端)
- Glide (图片加载)
- ViewBinding (视图绑定)
- Material Design Components
- SwipeRefreshLayout (下拉刷新)
- ViewPager2 (轮播图)
- CircleImageView (圆形头像)

## 目录结构

```
software-store/
├── backend/                        # PHP 后台
│   ├── database.sql               # 数据库脚本
│   ├── api/
│   │   ├── app/                   # App 端 API
│   │   │   ├── index.php          #   路由入口
│   │   │   ├── common.php         #   公共函数
│   │   │   ├── home.php           #   首页数据
│   │   │   ├── software.php       #   软件管理
│   │   │   ├── login.php          #   登录
│   │   │   ├── register.php       #   注册
│   │   │   ├── user.php           #   用户信息
│   │   │   ├── favorite.php       #   收藏
│   │   │   ├── download.php       #   下载记录
│   │   │   ├── feedback.php       #   反馈
│   │   │   ├── search.php         #   搜索
│   │   │   └── ...                #   其他 API
│   │   └── admin/                 # 后台管理 API
│   │       ├── index.php          #   路由入口
│   │       ├── common.php         #   公共函数
│   │       ├── login.php          #   管理员登录
│   │       ├── software.php       #   软件 CRUD
│   │       ├── category.php       #   分类 CRUD
│   │       ├── banner.php         #   轮播图 CRUD
│   │       ├── notice.php         #   公告管理
│   │       ├── toolbar.php        #   工具栏管理
│   │       ├── user.php           #   用户管理
│   │       ├── ad.php             #   广告管理
│   │       ├── config.php         #   系统配置
│   │       └── stats.php          #   统计数据
│   ├── include/                   # 公共库
│   │   ├── config.php             #   配置文件
│   │   ├── Database.php           #   数据库类
│   │   ├── Response.php           #   响应类
│   │   ├── Validator.php          #   验证类
│   │   ├── Auth.php               #   认证类
│   │   ├── Uploader.php           #   上传类
│   │   ├── functions.php          #   工具函数
│   │   ├── header.php             #   页面头部
│   │   ├── sidebar.php            #   侧边栏
│   │   └── footer.php             #   页面底部
│   ├── pages/                     # 管理页面
│   │   ├── login.php              #   登录页
│   │   ├── dashboard.php          #   仪表盘
│   │   ├── software_list.php      #   软件列表
│   │   ├── category.php           #   分类管理
│   │   ├── banner.php             #   轮播图管理
│   │   ├── notice.php             #   公告管理
│   │   ├── toolbar.php            #   工具栏管理
│   │   ├── user.php               #   用户管理
│   │   ├── ad.php                 #   广告管理
│   │   └── config.php             #   系统设置
│   ├── assets/                    # 静态资源
│   │   ├── css/admin.css          #   后台样式
│   │   └── js/admin.js            #   后台脚本
│   ├── uploads/                   # 上传文件
│   │   ├── apk/                   #   APK 文件
│   │   ├── images/                #   图片
│   │   └── avatars/               #   头像
│   └── index.php                  #   入口路由
│
└── android-app/                    # Android 工程
    ├── build.gradle               # 根构建配置
    ├── settings.gradle            # 项目设置
    └── app/
        ├── build.gradle           # App 构建配置
        └── src/main/
            ├── AndroidManifest.xml
            ├── java/com/software/store/
            │   ├── App.java                    # Application 类
            │   ├── data/
            │   │   ├── model/                  # 数据模型
            │   │   │   ├── ApiResponse.java
            │   │   │   ├── User.java
            │   │   │   ├── Software.java
            │   │   │   ├── Category.java
            │   │   │   ├── Banner.java
            │   │   │   ├── Toolbar.java
            │   │   │   ├── Notice.java
            │   │   │   ├── Advertisement.java
            │   │   │   ├── Config.java
            │   │   │   ├── DownloadRecord.java
            │   │   │   ├── Feedback.java
            │   │   │   └── PageData.java
            │   │   ├── remote/                 # 网络层
            │   │   │   ├── ApiService.java     #   Retrofit 接口
            │   │   │   └── RetrofitClient.java #   Retrofit 客户端
            │   │   └── repository/             # 数据仓库
            │   │       ├── SoftwareRepository.java
            │   │       ├── UserRepository.java
            │   │       └── HomeRepository.java
            │   ├── adapter/                     # 适配器
            │   │   ├── BannerAdapter.java
            │   │   ├── SoftwareAdapter.java
            │   │   ├── ToolbarAdapter.java
            │   │   ├── CategoryAdapter.java
            │   │   ├── SoftwareRelatedAdapter.java
            │   │   ├── DownloadRecordAdapter.java
            │   │   └── LoadMoreAdapter.java
            │   ├── ui/                          # UI 层
            │   │   ├── splash/SplashActivity.java
            │   │   ├── common/
            │   │   │   ├── MainActivity.java
            │   │   │   ├── WebViewActivity.java
            │   │   │   └── NoticeDetailActivity.java
            │   │   ├── home/HomeFragment.java
            │   │   ├── software/
            │   │   │   ├── SoftwareFragment.java
            │   │   │   └── SoftwareDetailActivity.java
            │   │   ├── discover/DiscoverFragment.java
            │   │   ├── profile/ProfileFragment.java
            │   │   ├── login/
            │   │   │   ├── LoginActivity.java
            │   │   │   └── FeedbackActivity.java
            │   │   └── download/DownloadManagementActivity.java
            │   └── util/                        # 工具类
            │       ├── SharedPrefsManager.java
            │       ├── ToastUtils.java
            │       ├── NetworkUtils.java
            │       ├── FileUtils.java
            │       └── DisplayUtils.java
            └── res/                             # 资源文件
                ├── layout/                      #   布局
                ├── drawable/                    #   图片/图标
                ├── values/                      #   字符串/颜色/主题
                └── menu/                        #   菜单
```

## 快速开始

### 1. 后端部署

```bash
# 1. 创建数据库
mysql -u root -p
CREATE DATABASE software_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE software_store;
SOURCE /path/to/software-store/backend/database.sql;

# 2. 配置 Web 服务器 (Nginx 示例)
# 将 backend/ 目录配置为站点根目录
# 配置 PHP-FPM 处理 .php 文件

# 3. 修改配置
# 编辑 backend/include/config.php
# 配置数据库连接信息
# 配置域名和路径

# 4. 确保上传目录可写
chmod -R 755 backend/uploads/
```

### 2. 后台管理

```
访问地址: http://your-domain/admin/ (或直接 backend/ 目录)
默认账号: admin
默认密码: admin123

⚠️ 首次登录后请立即修改密码！
```

### 3. Android 开发

```bash
# 1. 使用 Android Studio 打开 android-app/ 目录

# 2. 修改 API 地址
#   开发调试: 修改 app/build.gradle 中的 BuildConfig.BASE_URL
#   或修改 RetrofitClient 的 baseUrl

# 3. 编译运行
#   Build → Make Project
#   Run → Run 'app'

# 4. 签名打包 (Release)
#   Build → Generate Signed Bundle / APK
```

## API 文档

### App 端接口

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/api/app/home` | 首页聚合数据 |
| GET | `/api/app/software` | 软件列表（分页） |
| GET | `/api/app/software/{id}` | 软件详情 |
| GET | `/api/app/software/{id}/recommend` | 相关推荐 |
| GET | `/api/app/category` | 分类列表 |
| GET | `/api/app/toolbar` | 工具栏列表 |
| GET | `/api/app/splash` | 开屏广告配置 |
| GET | `/api/app/config` | 系统配置 |
| GET | `/api/app/pages` | 页面列表 |
| GET | `/api/app/search?keyword=` | 搜索软件 |
| POST | `/api/app/login` | 登录 |
| POST | `/api/app/register` | 注册 |
| POST | `/api/app/send_code` | 发送验证码 |
| GET | `/api/app/user/info` | 用户信息 |
| POST | `/api/app/user/update` | 更新资料 |
| POST | `/api/app/favorite` | 收藏/取消 |
| GET | `/api/app/favorites` | 收藏列表 |
| POST | `/api/app/download` | 启动下载 |
| GET | `/api/app/downloads` | 下载历史 |
| POST | `/api/app/download/{id}/progress` | 更新进度 |
| POST | `/api/app/feedback` | 提交反馈 |

### 后台管理接口

| 方法 | 路径 | 说明 |
|------|------|------|
| POST | `/api/admin/login` | 管理员登录 |
| GET | `/api/admin/stats` | 统计数据 |
| GET | `/api/admin/software` | 软件列表 |
| POST | `/api/admin/software` | 新增软件 |
| PUT | `/api/admin/software/{id}` | 更新软件 |
| DELETE | `/api/admin/software/{id}` | 删除软件 |
| GET | `/api/admin/category` | 分类列表 |
| POST | `/api/admin/category` | 新增分类 |
| GET | `/api/admin/banner` | 轮播图列表 |
| POST | `/api/admin/banner` | 新增轮播图 |
| GET | `/api/admin/notice` | 公告列表 |
| POST | `/api/admin/notice` | 更新公告 |
| GET | `/api/admin/toolbar` | 工具栏列表 |
| POST | `/api/admin/toolbar/toggle` | 显示/隐藏 |
| GET | `/api/admin/user` | 用户列表 |
| GET | `/api/admin/ad` | 广告列表 |
| POST | `/api/admin/config` | 更新配置 |
| POST | `/api/admin/verify_toggle` | 验证开关 |

## 数据库表

| 表名 | 说明 |
|------|------|
| `admin` | 管理员表 |
| `user` | 用户表 |
| `category` | 软件分类表 |
| `software` | 软件表 |
| `banner` | 轮播图表 |
| `notice` | 公告表 |
| `toolbar` | 工具栏表 |
| `advertisement` | 广告表 |
| `favorite` | 收藏表 |
| `download_record` | 下载记录表 |
| `feedback` | 反馈表 |
| `report_config` | 举报原因配置 |
| `config` | 系统配置表 |
| `verification_code` | 验证码表 |

## 功能特性

### App 端
- ✅ 开屏广告（后台可配置）
- ✅ 首页轮播图（自动滚动）
- ✅ 滚动公告（后台可编辑）
- ✅ 分类筛选软件
- ✅ 软件详情页
- ✅ 下载管理
- ✅ 收藏功能
- ✅ 用户注册/登录
- ✅ 手机/邮箱验证（后台开关控制）
- ✅ 软件搜索
- ✅ 反馈提交
- ✅ 缓存清理
- ✅ 分享功能

### 后台管理
- ✅ 仪表盘统计
- ✅ 软件 CRUD
- ✅ 分类 CRUD + 排序
- ✅ 轮播图管理
- ✅ 公告管理（滚动公告/声明/FAQ）
- ✅ 工具栏管理（显示/隐藏）
- ✅ 用户管理（启禁用/删除）
- ✅ 广告配置
- ✅ 系统设置
- ✅ 验证开关（手机/邮箱）

## 安全说明

- 🔑 默认密码请务必修改
- 🔑 数据库密码请使用强密码
- 🔑 API Secret Key 请妥善保管
- 🔑 生产环境请启用 HTTPS
- 🔑 定期备份数据库和上传文件
- 🔑 文件上传类型和大小请在 config.php 中限制

## 版本历史

- **v1.0.0** - 初始版本
  - 完整的 App 和后台管理系统
  - 支持软件库核心功能

## 许可证

本项目仅供学习和研究使用。
