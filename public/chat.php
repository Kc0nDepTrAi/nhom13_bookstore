<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('bookstore_sess');
    session_start();
}
require_once dirname(__DIR__) . '/config/config.php';

// Kiểm tra xác thực - yêu cầu đăng nhập
if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// Nếu là admin, luôn chuyển đến trang quản trị chat tổng
if ((isset($_SESSION['role']) && $_SESSION['role'] === 'admin') || (isset($_SESSION['username']) && $_SESSION['username'] === 'admin')) {
    header('Location: ' . BASE_URL . '/admin/admin_chat.php');
    exit;
}

// Nếu là khách hàng, chuyển về trang chủ và mở chat popup góc dưới màn hình
header('Location: ' . BASE_URL . '/?chat=open');
exit;
