<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('bookstore_sess');
    session_start();
}
// Kiểm tra đăng nhập admin
if ((!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') && (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin')) {
    echo "<h2>Bạn cần đăng nhập với tài khoản admin</h2>";
    echo "<a href='login.php'>Đăng nhập</a>";
    exit;
}

require_once dirname(__DIR__) . '/db/db_connect.php';

// Xử lý trả lời tin nhắn nếu POST trực tiếp đến admin_chat.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'reply') {
        $message = trim($_POST['message'] ?? '');
        $user_id = trim($_POST['user_id'] ?? '');
        
        if (!empty($message) && !empty($user_id)) {
            $sql = "INSERT INTO messages (sender, receiver, message) VALUES ('admin', ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $user_id, $message);
            
            if (mysqli_stmt_execute($stmt)) {
                $message_id = mysqli_insert_id($conn);
                echo json_encode(['status' => 'success', 'message_id' => $message_id]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Không thể gửi tin nhắn']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'mark_read') {
        $user_id = trim($_POST['user_id'] ?? '');
        if (!empty($user_id)) {
            $aliases = [$user_id, 'user_' . $user_id];
            $escaped = array_map(function($a) use ($conn) { return "'" . mysqli_real_escape_string($conn, $a) . "'"; }, $aliases);
            $in_clause = implode(',', $escaped);
            $sql = "UPDATE messages SET is_read = 1 
                   WHERE sender IN ($in_clause) AND receiver = 'admin' AND is_read = 0";
            mysqli_query($conn, $sql);
        }
        echo json_encode(['status' => 'success']);
        exit;
    }
}

$stitchPage = 'chat';
require dirname(__DIR__, 1) . '/app/views/stitch/render.php';
?>