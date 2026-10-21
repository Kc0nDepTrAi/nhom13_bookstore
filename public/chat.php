<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('bookstore_sess');
    session_start();
}
include '../db/db_connect.php';

// Kiểm tra xác thực - yêu cầu đăng nhập
if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Xác định user_id - Luôn là user khi vào chat.php
// Dù admin đăng nhập vẫn chat với tư cách user

// Debug session
error_log("Chat.php Debug - Session data: " . print_r($_SESSION, true));

if (isset($_SESSION['user_id'])) {
    $user_id = 'user_' . $_SESSION['user_id'];
} elseif (isset($_SESSION['username'])) {
    // ĐỒNG BỘ 100% với chat_api.php
    if ($_SESSION['username'] === 'admin') {
        $user_id = 'admin'; // admin chuẩn - ĐỒNG BỘ
    } else {
        $user_id = 'user_' . $_SESSION['username'];
    }
} else {
    // Guest user - tạo ID từ session
    $user_id = 'guest_' . substr(session_id(), 0, 8);
}

// Debug final user_id (tắt trong production)
// error_log("Chat.php Debug - Final user_id: $user_id");

// Nếu là admin, chuyển đến admin chat
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: ../admin/admin_chat.php');
    exit;
}

if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin') {
    header('Location: ../admin/admin_chat.php');
    exit;
}
?>

<?php
$stitchPage = 'catalogMobile';
require dirname(__DIR__, 1) . '/app/views/stitch/render.php';
?>
