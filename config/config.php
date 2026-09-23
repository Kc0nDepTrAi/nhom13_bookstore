<?php
// ============================================================
//  config/config.php — Cấu hình toàn bộ ứng dụng
// ============================================================

// Base URL của ứng dụng (không có dấu / cuối)
define('BASE_URL', '/bookstore');

// Đường dẫn tuyệt đối đến thư mục gốc
define('ROOT_PATH',  dirname(__DIR__));
define('APP_PATH',   ROOT_PATH . '/app');
define('VIEWS_PATH', APP_PATH  . '/views');

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'bookstore_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Ảnh
define('IMAGES_DIR', ROOT_PATH . '/images');
define('IMAGES_URL', BASE_URL  . '/images');

// Session
define('SESSION_NAME', 'bookstore_sess');

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
