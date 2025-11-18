-- ============================================
-- NguliKuy Database Indexes (compatible)
-- Run ini SETELAH import ngulikuy_db.sql
-- ============================================

USE `ngulikuy_db`;

-- Helper: fungsi singkat tidak tersedia, maka kita akan
-- menggunakan pattern prepared-statement untuk setiap index:
-- jika index belum ada di information_schema, maka jalankan ALTER TABLE.

-- 1. JOBS TABLE INDEXES

-- idx_customer_email
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='jobs' AND INDEX_NAME='idx_customer_email') = 0,
    'ALTER TABLE `jobs` ADD INDEX `idx_customer_email` (`customerEmail`)',
    'SELECT \"idx_customer_email already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_worker_id
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='jobs' AND INDEX_NAME='idx_worker_id') = 0,
    'ALTER TABLE `jobs` ADD INDEX `idx_worker_id` (`workerId`)',
    'SELECT \"idx_worker_id already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_status (jobs)
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='jobs' AND INDEX_NAME='idx_status') = 0,
    'ALTER TABLE `jobs` ADD INDEX `idx_status` (`status`)',
    'SELECT \"jobs idx_status already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_created_at (jobs)
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='jobs' AND INDEX_NAME='idx_created_at') = 0,
    'ALTER TABLE `jobs` ADD INDEX `idx_created_at` (`createdAt`)',
    'SELECT \"idx_created_at already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_customer_status (composite)
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='jobs' AND INDEX_NAME='idx_customer_status') = 0,
    'ALTER TABLE `jobs` ADD INDEX `idx_customer_status` (`customerEmail`, `status`)',
    'SELECT \"idx_customer_status already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_worker_status (composite)
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='jobs' AND INDEX_NAME='idx_worker_status') = 0,
    'ALTER TABLE `jobs` ADD INDEX `idx_worker_status` (`workerId`, `status`)',
    'SELECT \"idx_worker_status already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- 2. REVIEWS TABLE INDEXES

-- idx_worker_id (reviews)
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='reviews' AND INDEX_NAME='idx_worker_id') = 0,
    'ALTER TABLE `reviews` ADD INDEX `idx_worker_id` (`workerId`)',
    'SELECT \"reviews idx_worker_id already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_job_id (reviews)
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='reviews' AND INDEX_NAME='idx_job_id') = 0,
    'ALTER TABLE `reviews` ADD INDEX `idx_job_id` (`jobId`)',
    'SELECT \"idx_job_id already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_customer_id (reviews)
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='reviews' AND INDEX_NAME='idx_customer_id') = 0,
    'ALTER TABLE `reviews` ADD INDEX `idx_customer_id` (`customerId`)',
    'SELECT \"idx_customer_id already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_created_at (reviews)
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='reviews' AND INDEX_NAME='idx_created_at') = 0,
    'ALTER TABLE `reviews` ADD INDEX `idx_created_at` (`createdAt`)',
    'SELECT \"reviews idx_created_at already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- 3. WORKERS TABLE INDEXES

-- idx_status (workers)
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='workers' AND INDEX_NAME='idx_status') = 0,
    'ALTER TABLE `workers` ADD INDEX `idx_status` (`status`)',
    'SELECT \"workers idx_status already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_rating (workers)
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='workers' AND INDEX_NAME='idx_rating') = 0,
    'ALTER TABLE `workers` ADD INDEX `idx_rating` (`rating`)',
    'SELECT \"workers idx_rating already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_location (workers)
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='workers' AND INDEX_NAME='idx_location') = 0,
    'ALTER TABLE `workers` ADD INDEX `idx_location` (`location`)',
    'SELECT \"workers idx_location already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_status_rating (composite)
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='workers' AND INDEX_NAME='idx_status_rating') = 0,
    'ALTER TABLE `workers` ADD INDEX `idx_status_rating` (`status`, `rating`)',
    'SELECT \"workers idx_status_rating already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- 4. USERS TABLE INDEXES

-- idx_username (users)
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='users' AND INDEX_NAME='idx_username') = 0,
    'ALTER TABLE `users` ADD INDEX `idx_username` (`username`)',
    'SELECT \"users idx_username already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_role (users)
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='users' AND INDEX_NAME='idx_role') = 0,
    'ALTER TABLE `users` ADD INDEX `idx_role` (`role`)',
    'SELECT \"users idx_role already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_worker_profile (users)
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='users' AND INDEX_NAME='idx_worker_profile') = 0,
    'ALTER TABLE `users` ADD INDEX `idx_worker_profile` (`worker_profile_id`)',
    'SELECT \"users idx_worker_profile already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- 5. RATE LIMITS TABLE
-- Jika tabel belum ada, buat. Jika sudah ada, buat index bila perlu.

CREATE TABLE IF NOT EXISTS `rate_limits` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `identifier` VARCHAR(255) NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `attempts` INT DEFAULT 1,
    `last_attempt` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `blocked_until` DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes for rate_limits (conditional)
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='rate_limits' AND INDEX_NAME='idx_identifier_action') = 0,
    'ALTER TABLE `rate_limits` ADD INDEX `idx_identifier_action` (`identifier`, `action`)',
    'SELECT \"rate_limits idx_identifier_action already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='rate_limits' AND INDEX_NAME='idx_blocked_until') = 0,
    'ALTER TABLE `rate_limits` ADD INDEX `idx_blocked_until` (`blocked_until`)',
    'SELECT \"rate_limits idx_blocked_until already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA='ngulikuy_db' AND TABLE_NAME='rate_limits' AND INDEX_NAME='idx_last_attempt') = 0,
    'ALTER TABLE `rate_limits` ADD INDEX `idx_last_attempt` (`last_attempt`)',
    'SELECT \"rate_limits idx_last_attempt already exists\"'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- 6. VERIFY INDEXES (opsional, akan menampilkan index yang ada)
SELECT 'JOBS INDEXES' as `TABLE`;
SHOW INDEX FROM `jobs`;

SELECT 'REVIEWS INDEXES' as `TABLE`;
SHOW INDEX FROM `reviews`;

SELECT 'WORKERS INDEXES' as `TABLE`;
SHOW INDEX FROM `workers`;

SELECT 'USERS INDEXES' as `TABLE`;
SHOW INDEX FROM `users`;

SELECT 'RATE_LIMITS INDEXES' as `TABLE`;
SHOW INDEX FROM `rate_limits`;

-- 7. ANALYZE TABLES (Optional - untuk optimize)
ANALYZE TABLE `jobs`;
ANALYZE TABLE `reviews`;
ANALYZE TABLE `workers`;
ANALYZE TABLE `users`;
ANALYZE TABLE `rate_limits`;

-- 8. TABLE STATISTICS
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    ROUND(DATA_LENGTH / 1024 / 1024, 2) AS `Data Size (MB)`,
    ROUND(INDEX_LENGTH / 1024 / 1024, 2) AS `Index Size (MB)`,
    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS `Total Size (MB)`
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'ngulikuy_db'
ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC;

SELECT 'Database indexes berhasil ditambahkan! ✅' as 'STATUS';
