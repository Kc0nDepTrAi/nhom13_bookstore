<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include '../db/db_connect.php';

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_REQUEST['action'] ?? ''; // Nhận cả GET và POST

// Debug action
error_log("Admin API Debug - ACTION: " . $action);
error_log("Admin API Debug - REQUEST: " . print_r($_REQUEST, true));

switch ($action) {
    case 'reply':
        $message = trim($_POST['message'] ?? '');
        $user_id = $_POST['user_id'] ?? '';
        
        if (!empty($message) && !empty($user_id)) {
            $sql = "INSERT INTO messages (sender, receiver, message) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            $sender_param = 'admin';
            $receiver_param = $user_id;
            $message_param = $message;
            mysqli_stmt_bind_param($stmt, "sss", $sender_param, $receiver_param, $message_param);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Không thể gửi tin nhắn']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
        }
        break;
        
    case 'get_messages':
        $user_id = $_REQUEST['user_id'] ?? '';
        $last_id = $_REQUEST['last_id'] ?? 0;
        
        // Debug log
        error_log("Admin API Debug - Getting messages for user: $user_id, last_id: $last_id");
        
        if (!empty($user_id)) {
            // Đánh dấu đã đọc
            $mark_read_sql = "UPDATE messages SET is_read = 1 WHERE receiver = 'admin' AND sender = ? AND is_read = 0";
            $mark_stmt = mysqli_prepare($conn, $mark_read_sql);
            $user_param = $user_id;
            mysqli_stmt_bind_param($mark_stmt, "s", $user_param);
            mysqli_stmt_execute($mark_stmt);
            
            // Lấy tin nhắn mới hơn last_id
            $sql = "SELECT * FROM messages 
                     WHERE ((sender = ? AND receiver = 'admin') OR (sender = 'admin' AND receiver = ?))
                     AND id > ?
                     ORDER BY created_at ASC";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssi", $user_id, $user_id, $last_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $messages = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $messages[] = [
                    'id' => $row['id'],
                    'message' => $row['message'],
                    'is_me' => $row['sender'] === 'admin',
                    'created_at' => $row['created_at']
                ];
            }
            
            error_log("Admin API Debug - Found " . count($messages) . " messages");
            
            echo json_encode(['status' => 'success', 'messages' => $messages]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu user_id']);
        }
        break;
        
    case 'get_users':
        $sql = "SELECT DISTINCT sender as user_id, 
                        MAX(created_at) as last_message,
                        (SELECT COUNT(*) FROM messages m2 
                         WHERE m2.sender = m1.sender AND m2.receiver = 'admin' AND m2.is_read = 0) as unread_count
                 FROM messages m1 
                 WHERE sender != 'admin' 
                 GROUP BY sender 
                 ORDER BY last_message DESC";
        $result = mysqli_query($conn, $sql);
        
        $users = array();
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $users[] = $row;
            }
        }
        
        echo json_encode(['status' => 'success', 'users' => $users]);
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Action không hợp lệ']);
}
?>
