# GameHub 架构设计

> 个人游戏发布平台：**管理端**维护你开发的游戏，**C端**让玩家浏览并一键开始游戏。
> 以后每开发一款新游戏，在管理端填一张表单即可上架，**零代码改动**。

---

## 一、总体架构

```
                         ┌─────────────────────────────────────────┐
                         │              浏览器（玩家 / 管理员）        │
                         └───────────────┬─────────────────────────┘
                                         │ HTTP
              ┌──────────────────────────▼───────────────────────────┐
              │                 phpStudy Apache (:80)                │
              │              D:\phpstudy_pro\WWW\gamehub             │
              │  ┌────────────────────────────────────────────────┐  │
              │  │  index.php（唯一入口 / 前端控制器 + 路由）         │  │
              │  ├────────────┬───────────────────────────────────┤  │
              │  │  C端模块    │  Site.php   首页 / 详情 / 启动游戏    │  │
              │  │  管理端模块 │  Admin.php  登录 / 游戏CRUD / 统计   │  │
              │  ├────────────┴───────────────────────────────────┤  │
              │  │  Game.php 模型 · Auth.php 管理员登录态 · Database │  │
              │  └───────────────────────┬────────────────────────┘  │
              └──────────────────────────┼──────────────────────────┘
                                         │ PDO
                                  ┌──────▼──────┐
                                  │   MySQL     │  gamehub 库（本机 3306：WSL MySQL 8.4）
                                  └──────┬──────┘   （phpStudy 自带的 5.7 在 3308，未使用）
   游戏本体（独立部署，各玩各的）             │
   ┌──────────────────────┐               │
   │ /poker/   德州扑克 H5网游（PHP+MySQL）  │◄── 网游：新窗口/内嵌 直接打开
   │ /game/    叠了个叠 H5 + 抖音小游戏工程  │◄── H5：内嵌播放器；抖音端：扫码引导
   │ 未来的游戏 …（任意域名/端口）            │◄── 外链：管理端填 URL 即可
   └──────────────────────┘
```

**关键思想：GameHub 是"游戏库 + 启动器"，不是游戏运行时。**
每款游戏本体独立存在（自己的一套代码/后端/数据库），GameHub 只登记它的**元信息**和**入口地址**，玩家点"开始游戏"时把浏览器带到游戏那里。

---

## 二、核心抽象：游戏接入模型

每款游戏在 `games` 表里是一条记录，**怎么"打开"由两个字段决定**：

| 字段 | 作用 |
|---|---|
| `entry_url` | 游戏入口地址。站内游戏可填相对路径（`/poker/`），外部游戏填完整 URL（`https://xxx.com/`）。**留空 = 网页上玩不了**（纯小程序游戏） |
| `play_mode` | 网页版怎么打开：`new_tab` 新窗口直开（网游推荐） / `iframe` 站内播放器内嵌（休闲/竖屏游戏推荐） |
| `miniapp_platform` / `miniapp_appid` / `miniapp_qr` | 小程序信息（抖音/微信）。填了就在详情页展示"扫码/搜索直达"板块 |

**C端按钮逻辑**：

```
点「开始游戏」
 ├─ entry_url 非空 → 按 play_mode 打开（new_tab=跳转 / iframe=进入全屏播放页），并记一次开玩日志
 └─ entry_url 为空 → 不显示开始按钮，展示小程序二维码 + 抖音搜索引导
```

### 为什么抖音小程序不能"直接打开"？

抖音小游戏运行在**抖音 App 的 runtime 里**（`tt.*` API），浏览器无法加载它。
所以网页端能做的是：展示 H5 版（`game/` 工程自带 H5 调试入口，直接内嵌玩），同时在详情页挂**抖音小游戏二维码**（抖音开放平台可下载小程序码）+ 引导文案，玩家扫码在手机抖音里玩。
`叠了个叠` 属于"双端游戏"：网页内嵌可玩 + 抖音小程序引导，一条记录同时承载。

### 新游戏上架 SOP（零代码）

1. 管理端登录 → 游戏管理 → 新增游戏
2. 填：名称 / 分类 / 封面图 / 简介 / `entry_url` / 打开方式 / 平台标签 / 小程序信息（如有）
3. 状态切"上架"（可勾选首页推荐位、设置排序权重）
4. 完成，C端立即可见

---

## 三、数据模型（MySQL `gamehub` 库）

```sql
games        -- 游戏登记表（核心）
  id, name, slug(唯一,用于URL), category(分类),
  play_mode ENUM('new_tab','iframe'),
  entry_url, platforms(标签: H5网页,抖音小游戏,多人联机…),
  miniapp_platform, miniapp_appid, miniapp_qr,   -- 小程序引导
  cover, short_desc, description, tags(热门/新游),
  status ENUM('online','offline'), featured(推荐位), sort_order,
  play_count, created_at, updated_at

play_logs    -- 开玩日志（启动游戏时记一条，供统计/排行）
  id, game_id, ip, user_agent, created_at

admins       -- 管理端账号
  id, username, password_hash(bcrypt), created_at, last_login_at
```

有意**不做**的东西（保持简单，需要时再加）：分类独立表（分类就是 games.category 的去重集合）、C端用户表（见下文演进路线）、评分评论。

---

## 四、路由与目录

查询串路由（`?r=xxx`），**不依赖 Apache rewrite**，任何 phpStudy/Nginx 配置都能跑：

| 路由 | 说明 |
|---|---|
| `/?r=home` | C端大厅：推荐位 + 分类筛选 + 搜索 + 游戏卡片 |
| `/?r=game&id=N` | 游戏详情：介绍 + 开始按钮 + 小程序二维码板块 |
| `/?r=play&id=N` | 站内播放页：顶栏 + 全屏 iframe（iframe 模式） |
| `/?r=go&id=N` | 启动跳板：记开玩日志 → 302 跳到 entry_url（new_tab 模式） |
| `/?r=admin/*` | 管理端：login / dashboard(游戏列表+统计) / game_form / game_save / game_toggle / game_delete / password |

```
gamehub/
├── index.php              唯一入口
├── app/
│   ├── bootstrap.php      会话/助手函数/加载
│   ├── config.php         数据库与站点配置
│   ├── Database.php       PDO 封装 + 自动建库建表 + 种子数据
│   ├── Auth.php           管理员登录态 / CSRF
│   ├── Game.php           游戏模型
│   ├── controllers/{Site.php, Admin.php}
│   └── views/             layout + C端页 + admin/ 管理页
├── assets/                css / js
├── uploads/               封面图、小程序二维码（管理端上传）
├── sql/init.sql           建表+种子备份（程序首次访问也会自动初始化）
└── ARCHITECTURE.md / README.md
```

安全基线：PDO 预处理（无拼接）、`password_hash` 存密码、Session + CSRF 校验管理端写操作、上传白名单校验（图片类型+真实 mime）、全部输出 `htmlspecialchars`、`app/` 目录双重防护（`.htaccess` 拒绝 + `defined('GAMEHUB')` 守卫）。

---

## 五、首发两款游戏的接入方案

### 1. 德州扑克（`/poker/`，H5 网游 → 新窗口直开）

- `entry_url = /poker/`（相对路径：跟 GameHub 同域部署，本地/线上都成立；以后搬到独立域名/端口，管理端改成完整 URL 即可）
- `play_mode = new_tab`：德州是横屏重交互网游，给玩家一个完整的浏览器窗口体验最好，也天然避开将来游戏侧若加 `X-Frame-Options` 的嵌入限制
- 游戏自带账号系统（token 存 localStorage），现阶段"直接打开即玩"，不做强制互通

**演进：统一账号 + 免登透传（SSO）**
GameHub 未来加 `users` 表后，启动网游时带签名令牌：

```
go → https://.../poker/?sso={uid}.{ts}.{hmac_sha256(uid|ts, SECRET)}
```

`poker/api.php` 加一个 `sso_login` 接口校验 HMAC（时间戳 ±300s 防重放），校验通过即换取自家 token —— 玩家在大厅登录一次，进任何网游都不用再登录。`entry_url` 里可写 `{sso}` 占位符由 GameHub 替换，对旧游戏零侵入。

### 2. 叠了个叠（`/game/`，H5 内嵌 + 抖音小游戏引导）

- `entry_url = /game/`，`play_mode = iframe`：竖屏休闲游戏放进 GameHub 的"手机框"播放器里玩最合适（游戏本身 375×667 逻辑分辨率自适应）
- `miniapp_platform = douyin`：详情页展示抖音小游戏板块 —— 二维码（开放平台下载小程序码后上传）+ AppID + 搜索引导
- 以后若有纯小程序游戏（无 H5），`entry_url` 留空即可，C端自动只展示扫码引导

---

## 六、演进路线（按需加）

| 阶段 | 内容 |
|---|---|
| 近期 | 管理端游戏排序拖拽、分类管理、开玩趋势图 |
| 中期 | C端用户系统（手机号/微信登录）+ 收藏/评论/评分；网游 SSO 免登透传（见上） |
| 中期 | 游戏版本管理：一款游戏多个版本（如"网页版/正式版"）多入口 |
| 远期 | 前后端分离：GameHub 出 OpenAPI + Vue/React 大厅 App；游戏数据上报（时长、留存）；CDN 加速封面与游戏静态资源；Nginx 独立部署 + HTTPS |

> 部署提示：上线时把 phpStudy 的站点根指到 `WWW`（现状即可，访问 `http://localhost/gamehub/`）；正式对外建议 Nginx + 域名 + HTTPS，并把 `app/config.php` 里的数据库密码改成专用账号。
