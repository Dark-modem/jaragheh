-- ===========================================================
--  جرقه | اسکیمای دیتابیس و داده‌های اولیه
--  این فایل را در phpMyAdmin هاست (تب Import) اجرا کنید.
--  کدگذاری: utf8mb4 برای پشتیبانی کامل فارسی و اموجی
-- ===========================================================

SET NAMES utf8mb4;
SET time_zone = '+03:30';
SET foreign_key_checks = 0;

-- ---------- کاربران ----------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `phone_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `role` ENUM('user','admin') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`),
  UNIQUE KEY `uq_email` (`email`),
  UNIQUE KEY `uq_phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- ---------- کدهای تایید ----------
CREATE TABLE IF NOT EXISTS `verification_codes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `vtype` ENUM('email','phone') NOT NULL,
  `code` VARCHAR(8) NOT NULL,
  `used` TINYINT(1) NOT NULL DEFAULT 0,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- ---------- بازی‌های پشتیبانی‌شده ----------
CREATE TABLE IF NOT EXISTS `games` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- ---------- بسته‌ها / محصولات ----------
CREATE TABLE IF NOT EXISTS `packages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `volume_gb` INT NOT NULL DEFAULT 0,
  `duration_days` INT NOT NULL DEFAULT 30,
  `price` BIGINT NOT NULL DEFAULT 0,
  `discount_price` BIGINT DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- ---------- سفارش‌ها ----------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(30) NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `package_id` INT UNSIGNED DEFAULT NULL,
  `package_title` VARCHAR(100) DEFAULT NULL,
  `amount` BIGINT NOT NULL DEFAULT 0,
  `gateway` ENUM('card','aqayepardakht') NOT NULL DEFAULT 'card',
  `status` ENUM('pending','awaiting','paid','failed') NOT NULL DEFAULT 'pending',
  `transid` VARCHAR(64) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paid_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_order_number` (`order_number`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- ---------- اشتراک‌های فعال ----------
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `package_id` INT UNSIGNED DEFAULT NULL,
  `order_id` INT UNSIGNED DEFAULT NULL,
  `title` VARCHAR(100) DEFAULT NULL,
  `total_volume_gb` INT NOT NULL DEFAULT 0,
  `used_volume_gb` INT NOT NULL DEFAULT 0,
  `total_days` INT NOT NULL DEFAULT 0,
  `start_at` DATETIME NOT NULL,
  `end_at` DATETIME NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- ---------- تیکت‌های پشتیبانی ----------
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `status` ENUM('open','answered','closed') NOT NULL DEFAULT 'open',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE IF NOT EXISTS `ticket_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` INT UNSIGNED NOT NULL,
  `sender` ENUM('user','admin') NOT NULL,
  `body` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ticket` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- ---------- تنظیمات کلید/مقدار ----------
CREATE TABLE IF NOT EXISTS `settings` (
  `skey` VARCHAR(60) NOT NULL,
  `svalue` TEXT,
  PRIMARY KEY (`skey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- ===========================================================
--  داده‌های اولیه
-- ===========================================================

-- مدیر پیش‌فرض  | نام کاربری: admin  | رمز عبور: admin1234
-- پس از اولین ورود حتماً رمز را از پنل تغییر دهید.
INSERT INTO `users` (`id`,`username`,`email`,`phone`,`password`,`email_verified`,`phone_verified`,`role`)
VALUES (1000,'admin','admin@jaragheh.ir','09120000000',
        '$2b$10$JrPVK5etWRQwkynzh4AZh.bXvHwF/21xvtJSMJ27g9iK.65xU.8j6',1,1,'admin')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- بازی‌های پشتیبانی‌شده (تصاویر را بعداً از پنل ادمین آپلود کنید)
INSERT INTO `games` (`name`,`image`,`sort_order`) VALUES
('کالاف اف دیوتی', NULL, 1),
('فری فایر',       NULL, 2),
('پابجی',          NULL, 3),
('فورت نایت',       NULL, 4);

-- بسته‌های نمونه
INSERT INTO `packages` (`title`,`description`,`volume_gb`,`duration_days`,`price`,`discount_price`,`active`,`sort_order`) VALUES
('بسته برنزی',  'مناسب گیمرهای معمولی، کاهش پینگ پایه روی ۶ سرور.', 50,  30, 120000, 99000,  1, 1),
('بسته نقره‌ای', 'پینگ پایدار برای رنک‌پوش‌ها، اولویت مسیر اختصاصی.', 100, 30, 220000, 179000, 1, 2),
('بسته طلایی',  'حداکثر کارایی، کمترین پینگ ممکن و پشتیبانی ویژه.',   200, 60, 420000, 349000, 1, 3);

-- تنظیمات درگاه‌ها و پشتیبانی
INSERT INTO `settings` (`skey`,`svalue`) VALUES
('gateway_card_enabled','1'),
('gateway_card_text','برای دریافت شماره کارت و تکمیل خرید کارت‌به‌کارت، به پشتیبانی تلگرام پیام دهید.'),
('telegram_id','https://t.me/supjaragheh'),
('gateway_aqaye_enabled','1'),
('aqaye_pin','sandbox')
ON DUPLICATE KEY UPDATE `svalue`=`svalue`;

SET foreign_key_checks = 1;
