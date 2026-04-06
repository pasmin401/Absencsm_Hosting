-- ============================================================
-- AttendTrack – MySQL Schema
-- Run this in phpMyAdmin → SQL tab, or via MySQL CLI:
--   mysql -u root -p attendtrack < schema_mysql.sql
--
-- 1. First create the database:
--    CREATE DATABASE IF NOT EXISTS attendtrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- 2. Then run this script.
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+07:00';

-- ── Users table ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`          INT           NOT NULL AUTO_INCREMENT,
  `username`    VARCHAR(30)   NOT NULL,
  `email`       VARCHAR(100)  NOT NULL,
  `password`    VARCHAR(255)  NOT NULL,
  `role`        ENUM('user','admin') NOT NULL DEFAULT 'user',
  `phone`       VARCHAR(20)   DEFAULT NULL,
  `department`  VARCHAR(100)  DEFAULT NULL,
  `work_start`  TIME          DEFAULT NULL,
  `work_end`    TIME          DEFAULT NULL,
  `profile_pic` MEDIUMTEXT    DEFAULT NULL,
  `is_active`   TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Attendance table ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `attendance` (
  `id`                INT           NOT NULL AUTO_INCREMENT,
  `user_id`           INT           NOT NULL,
  `work_date`         DATE          NOT NULL,
  `checkin_time`      TIME          DEFAULT NULL,
  `checkin_lat`       DECIMAL(10,6) DEFAULT NULL,
  `checkin_lng`       DECIMAL(10,6) DEFAULT NULL,
  `checkin_photo`     MEDIUMTEXT    DEFAULT NULL,
  `checkout_time`     TIME          DEFAULT NULL,
  `checkout_lat`      DECIMAL(10,6) DEFAULT NULL,
  `checkout_lng`      DECIMAL(10,6) DEFAULT NULL,
  `checkout_photo`    MEDIUMTEXT    DEFAULT NULL,
  `ot_checkin_time`   TIME          DEFAULT NULL,
  `ot_checkin_lat`    DECIMAL(10,6) DEFAULT NULL,
  `ot_checkin_lng`    DECIMAL(10,6) DEFAULT NULL,
  `ot_checkin_photo`  MEDIUMTEXT    DEFAULT NULL,
  `ot_checkout_time`  TIME          DEFAULT NULL,
  `ot_checkout_lat`   DECIMAL(10,6) DEFAULT NULL,
  `ot_checkout_lng`   DECIMAL(10,6) DEFAULT NULL,
  `ot_checkout_photo` MEDIUMTEXT    DEFAULT NULL,
  `status`            ENUM('present','absent','leave','holiday') NOT NULL DEFAULT 'present',
  `notes`             TEXT          DEFAULT NULL,
  `created_at`        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_date` (`user_id`, `work_date`),
  KEY `idx_work_date` (`work_date`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_attendance_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Password resets table ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `email`      VARCHAR(100) NOT NULL,
  `token`      VARCHAR(64)  NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_token` (`token`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA: Admin account
-- Password: Admin@123
-- ============================================================
INSERT IGNORE INTO `users` (username, email, password, role, department, is_active, created_at)
VALUES (
  'admin',
  'admin@attendtrack.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin',
  'Management',
  1,
  NOW()
);

-- ============================================================
-- SEED DATA: Sample employees (Password: User@123)
-- ============================================================
INSERT IGNORE INTO `users` (username, email, password, role, phone, department, work_start, work_end, is_active, created_at)
VALUES
  ('john_doe',       'john.doe@company.com',       '$2y$10$TKh8H1.PfuA38Xe.aMtGtOQl8g/l4RVNRSO1Z3DBNZ9MUu4GnuUa', 'user', '+62 812-0001-0001', 'Engineering', '08:00:00', '17:00:00', 1, NOW()),
  ('jane_smith',     'jane.smith@company.com',     '$2y$10$TKh8H1.PfuA38Xe.aMtGtOQl8g/l4RVNRSO1Z3DBNZ9MUu4GnuUa', 'user', '+62 812-0001-0002', 'Marketing',   '08:00:00', '17:00:00', 1, NOW()),
  ('ali_rahman',     'ali.rahman@company.com',     '$2y$10$TKh8H1.PfuA38Xe.aMtGtOQl8g/l4RVNRSO1Z3DBNZ9MUu4GnuUa', 'user', '+62 812-0001-0003', 'Finance',     '09:00:00', '18:00:00', 1, NOW()),
  ('siti_nurhaliza', 'siti.nurhaliza@company.com', '$2y$10$TKh8H1.PfuA38Xe.aMtGtOQl8g/l4RVNRSO1Z3DBNZ9MUu4GnuUa', 'user', '+62 812-0001-0004', 'HR',          '08:00:00', '17:00:00', 1, NOW()),
  ('budi_santoso',   'budi.santoso@company.com',   '$2y$10$TKh8H1.PfuA38Xe.aMtGtOQl8g/l4RVNRSO1Z3DBNZ9MUu4GnuUa', 'user', '+62 812-0001-0005', 'Operations',  '07:00:00', '16:00:00', 1, NOW());
