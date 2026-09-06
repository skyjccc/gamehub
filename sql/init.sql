-- GameHub 数据库初始化脚本（备用）
-- 程序首次访问会自动建库建表并写入种子数据，此文件仅供手动导入参考。
-- mysql -uroot -p < sql/init.sql

CREATE DATABASE IF NOT EXISTS `gamehub` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gamehub`;

CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login_at` DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `games` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `category` VARCHAR(20) NOT NULL DEFAULT '其他',
  `play_mode` ENUM('new_tab','iframe') NOT NULL DEFAULT 'new_tab',
  `entry_url` VARCHAR(500) NOT NULL DEFAULT '',
  `platforms` VARCHAR(200) NOT NULL DEFAULT '',
  `miniapp_platform` VARCHAR(20) NOT NULL DEFAULT '',
  `miniapp_appid` VARCHAR(64) NOT NULL DEFAULT '',
  `miniapp_qr` VARCHAR(255) NOT NULL DEFAULT '',
  `cover` VARCHAR(255) NOT NULL DEFAULT '',
  `short_desc` VARCHAR(200) NOT NULL DEFAULT '',
  `description` TEXT NULL,
  `tags` VARCHAR(100) NOT NULL DEFAULT '',
  `status` ENUM('online','offline') NOT NULL DEFAULT 'offline',
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `play_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `play_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `game_id` INT UNSIGNED NOT NULL,
  `ip` VARCHAR(45) NOT NULL DEFAULT '',
  `user_agent` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_game` (`game_id`),
  KEY `idx_time` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 管理员密码在程序首次自动安装时以 password_hash 生成；
-- 手动导入时可执行下面这行（密码为 gamehub123，登录后请修改）：
-- INSERT INTO admins (username, password_hash)
-- VALUES ('admin', '$2y$10$e0NRzQ3rkhBQyP1aV0n1f.Vz4wuHHHKuUx9GRSM5Dzq1S1E8z1WjG');
