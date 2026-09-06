# GameHub · 游戏大厅

个人游戏发布平台：**管理端**上架 / 维护你开发的游戏，**C端**让玩家浏览并一键开始。
架构设计见 [ARCHITECTURE.md](ARCHITECTURE.md)。

## 访问

phpStudy 启动 **Apache + MySQL** 后（首次访问自动建库建表，库名 `gamehub`）：

| 地址 | 说明 |
|---|---|
| http://localhost/gamehub/ | C端 游戏大厅 |
| http://localhost/gamehub/index.php?r=admin/login | 管理端 |

**管理员初始账号：`admin` / `gamehub123`（登录后请到「修改密码」改掉）**

> 无 Apache 时可临时用 PHP 内置服务器测试：在 `WWW` 目录执行
> `php -S 127.0.0.1:8890`，然后访问 `http://127.0.0.1:8890/gamehub/`。

## 已上架的游戏

| 游戏 | 玩法入口 | 说明 |
|---|---|---|
| 德州扑克 | 大厅点「开始游戏」→ **新窗口直开** `http://localhost/poker/` | H5 网游，需 poker 的 Apache/MySQL 环境 |
| 叠了个叠 | 大厅点「开始游戏」→ **站内播放器** 内嵌 `/game/`；详情页有**抖音小游戏**扫码引导 | 双端：网页版即点即玩 + 抖音小游戏 |

两款游戏共用 phpStudy 的 WWW 目录，入口都写成相对路径，本地和线上都成立。

## 怎么上架新游戏（零代码）

1. 管理端 → 新增游戏，填：名称、分类、封面、简介、**游戏入口地址**、打开方式
2. 入口地址：站内游戏填相对路径（`/你的目录/`），外部游戏填完整 URL；**留空则只展示小程序引导**
3. 打开方式：**新窗口直开**（网游推荐）/ **站内播放器**（H5 休闲推荐）
4. 有小程序就填小程序平台 / AppID / 小程序码（开放平台下载）
5. 状态切「上架」，可勾选首页推荐位、设置排序权重 —— 保存即上架

## 目录速览

```
gamehub/
├── index.php          唯一入口（?r=xxx 路由，不依赖 rewrite）
├── app/               应用代码（Web 不可直接访问）
│   ├── config.php     ★ 数据库账号、分类/标签选项在这里改
│   ├── Database.php   自动建库建表 + 种子数据
│   ├── Game.php       游戏模型
│   ├── controllers/   Site（C端）/ Admin（管理端）
│   └── views/         页面模板
├── assets/            css / js
├── uploads/           封面图、小程序码（管理端上传，已禁脚本执行）
└── sql/init.sql       建表脚本备份
```

## 安全与维护

- 管理端：Session 登录 + CSRF 校验；密码 bcrypt 存储
- 上传：图片类型白名单 + 真实 mime 校验，uploads 目录禁止执行脚本
- `app/.htaccess` 拒绝 Web 访问，所有输出经 `htmlspecialchars` 转义
- 上线前把 `app/config.php` 的 `debug` 改为 `false`，数据库换成专用账号
