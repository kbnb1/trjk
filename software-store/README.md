# 软件库 App

一个完整的软件库应用系统，包含 Android 客户端（Java）和 PHP 后台管理系统。

## 技术栈

### 后端
- **语言**：PHP 7.4+ / 8.x
- **数据库**：MySQL 5.7+ / MariaDB
- **架构**：原生轻量级 MVC
- **前端**：Bootstrap 5 + jQuery 3
- **认证**：HMAC-SHA256 Token

### Android 客户端
- **语言**：Java
- **架构**：MVVM
- **最低 SDK**：24（Android 7.0）
- **目标 SDK**：34（Android 14）
- **依赖库**：Retrofit 2、OkHttp、Glide、Material Design、ViewPager2、CircleImageView

## 功能特性

### App 端
- 启动页（开屏广告位预留）
- 首页（轮播图 + 滚动小公告 + 公告专区 + 推荐软件）
- 软件页（分类筛选 + 排行榜）
- 发现页（实用工具宫格）
- 个人中心（用户信息 + 快捷入口 + 设置）
- 软件详情页（下载 + 介绍 + 推荐）
- 登录注册（用户名密码 + 可选手机/邮箱验证）
- 收藏、下载记录管理

### 后台管理
- 仪表盘（数据统计 + 趋势图 + 系统信息）
- 软件管理（CRUD + 图标上传）
- 分类管理（CRUD + 排序）
- 轮播图管理（CRUD + 排序）
- 公告管理（滚动公告 + 静态页面）
- 工具栏管理（CRUD + 显示控制）
- 用户管理（查看 + 启用/禁用）
- 广告管理（开屏/首页/详情广告）
- 系统设置（站点配置 + 验证开关 + 密码修改）

## 目录结构

```
software-store/
├── backend/                 # PHP 后台
│   ├── database.sql         # 数据库脚本（14张表）
│   ├── include/             # 核心类库
│   │   ├── config.php       # 配置文件
│   │   ├── Database.php     # 数据库操作类
│   │   ├── Response.php     # 统一响应类
│   │   ├── Validator.php    # 输入验证类
│   │   ├── Auth.php         # 认证类
│   │   ├── Uploader.php     # 文件上传类
│   │   └── functions.php    # 公共函数
│   ├── api/                 # API 接口
│   │   ├── app/             # App 端 API
│   │   └── admin/           # 后台管理 API
│   ├── pages/               # 后台页面
│   ├── assets/              # 静态资源
│   │   ├── css/admin.css
│   │   └── js/admin.js
│   └── uploads/             # 上传目录
├── android-app/             # Android 客户端
│   └── app/src/main/
│       ├── java/com/software/store/
│       │   ├── App.java
│       │   ├── data/        # 数据层
│       │   ├── adapter/     # 适配器
│       │   └── ui/          # UI 层
│       └── res/             # 资源文件
├── preview.html             # App 高保真预览
├── screenshots/             # 预览截图
└── README.md
```

## 快速部署

### 后端部署
1. 导入数据库：`mysql -u root -p < backend/database.sql`
2. 修改配置：编辑 `backend/include/config.php` 中的数据库连接信息
3. 启动服务：`php -S 0.0.0.0:8080 -t backend/`
4. 访问后台：`http://localhost:8080/pages/login.php`
5. 默认账号：`admin` / `admin123`

### 预览
- App 预览：`http://localhost:8080/preview.html`
- 后台预览（免登录）：`http://localhost:8080/pages/preview-init.php`

### Android 编译
1. 用 Android Studio 打开 `android-app/` 目录
2. 同步 Gradle 依赖
3. 修改 `ApiService.java` 中的 BASE_URL 为后端地址
4. 编译运行

## API 列表

### App 端 API（/api/app/）
| 接口 | 方法 | 说明 |
|------|------|------|
| /config | GET | 获取应用配置 |
| /register | POST | 用户注册 |
| /login | POST | 用户登录 |
| /send_code | POST | 发送验证码 |
| /banner | GET | 获取轮播图 |
| /notice | GET | 获取公告列表 |
| /software/list | GET | 获取软件列表 |
| /software/detail | GET | 获取软件详情 |
| /software/hot | GET | 获取热门软件 |
| /software/recommend | GET | 获取推荐软件 |
| /category | GET | 获取分类列表 |
| /toolbar | GET | 获取工具栏 |
| /advertisement | GET | 获取广告 |
| /feedback | POST | 提交反馈 |
| /user/profile | GET | 获取用户信息 |
| /user/favorite | POST | 收藏/取消收藏 |

### 后台管理 API（/api/admin/）
软件、分类、轮播图、公告、工具栏、广告、用户、反馈、系统配置等全套 CRUD 接口。

## 数据库表（14张）
admin、user、category、software、banner、notice、toolbar、advertisement、favorite、download_record、feedback、report_config、config、verification_code
