<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('bookstore_sess');
    session_start();
}
// Kiểm tra đăng nhập admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Thử kiểm tra bằng username admin
    if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
        echo "<h2>Bạn cần đăng nhập với tài khoản admin</h2>";
        echo "<a href='login.php'>Đăng nhập</a>";
        exit;
    }
}

include '../db/db_connect.php';

// Xử lý trả lời tin nhắn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'reply') {
        $message = trim($_POST['message'] ?? '');
        $user_id = $_POST['user_id'] ?? '';
        
        if (!empty($message) && !empty($user_id)) {
            $sql = "INSERT INTO messages (sender, receiver, message) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sss", 'admin', $user_id, $message);
            
            if (mysqli_stmt_execute($stmt)) {
                // ✅ Trả về ID của message vừa insert
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
        $user_id = $_POST['user_id'] ?? '';
        if (!empty($user_id)) {
            $sql = "UPDATE messages SET is_read = 1 
                   WHERE sender = ? AND receiver = 'admin' AND is_read = 0";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $user_id);
            mysqli_stmt_execute($stmt);
        }
        echo json_encode(['status' => 'success']);
        exit;
    }
}

// Lấy danh sách user đã chat
$users_sql = "SELECT DISTINCT sender as user_id, 
                     MAX(created_at) as last_message,
                     (SELECT COUNT(*) FROM messages m2 
                      WHERE m2.sender = m1.sender AND m2.receiver = 'admin' AND m2.is_read = 0) as unread_count
              FROM messages m1 
              WHERE sender != 'admin' 
              GROUP BY sender 
              ORDER BY last_message DESC";
$users_result = mysqli_query($conn, $users_sql);

$users = array();
if ($users_result && mysqli_num_rows($users_result) > 0) {
    while ($row = mysqli_fetch_assoc($users_result)) {
        $users[] = $row;
    }
}
?>

<?php
$stitchPage = 'chat';
require dirname(__DIR__, 1) . '/app/views/stitch/render.php';
?>
