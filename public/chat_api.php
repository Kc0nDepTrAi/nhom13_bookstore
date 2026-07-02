<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include '../db/db_connect.php';

// Lấy user_id từ session
session_start();

// Kiểm tra xác thực - yêu cầu đăng nhập
if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Bạn phải đăng nhập để sử dụng chức năng chat']);
    exit;
}

// Debug session
error_log("Chat Debug - Session data: " . print_r($_SESSION, true));

if (isset($_SESSION['user_id'])) {
    $user_id = 'user_' . $_SESSION['user_id'];
} elseif (isset($_SESSION['username'])) {
    if ($_SESSION['username'] === 'admin') {
        $user_id = 'admin'; // admin chuẩn
    } else {
        $user_id = 'user_' . $_SESSION['username'];
    }
} else {
    // Tạo guest ID từ session
    $user_id = 'guest_' . substr(session_id(), 0, 8);
}

// Debug final user_id
error_log("Chat Debug - Final user_id: $user_id");

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'send':
        $message = trim($_POST['message'] ?? '');
        $receiver = $_POST['receiver'] ?? 'admin';
        
        // Debug log
        error_log("Chat Debug - User ID: $user_id, Message: $message, Receiver: $receiver");
        
        if (!empty($message)) {
            $sql = "INSERT INTO messages (sender, receiver, message) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $user_id, $receiver, $message);
            
            if (mysqli_stmt_execute($stmt)) {
                $message_id = mysqli_insert_id($conn);
                error_log("Chat Debug - Message inserted with ID: $message_id");
                
                echo json_encode([
                    'status' => 'success',
                    'message_id' => $message_id,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'debug_user_id' => $user_id
                ]);
            } else {
                $error = mysqli_error($conn);
                error_log("Chat Debug - Insert failed: $error");
                echo json_encode(['status' => 'error', 'message' => "Không thể gửi tin nhắn: $error"]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Tin nhắn không được rỗng']);
        }
        break;
        
    case 'get':
        $receiver = $_POST['receiver'] ?? 'admin';
        $last_id = $_POST['last_id'] ?? 0;
        
        // Lấy tin nhắn mới hơn last_id
        $sql = "SELECT * FROM messages 
                WHERE ((sender = ? AND receiver = ?) OR (sender = ? AND receiver = ?)) 
                AND id > ?
                ORDER BY created_at ASC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $user_id, $receiver, $receiver, $user_id, $last_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $messages = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $messages[] = [
                'id' => $row['id'],
                'sender' => $row['sender'],
                'receiver' => $row['receiver'],
                'message' => $row['message'],
                'created_at' => $row['created_at'],
                'is_read' => $row['is_read'],
                'is_me' => $row['sender'] === $user_id
            ];
        }
        
        // Đánh dấu đã đọc tin nhắn từ admin
        if (!empty($messages)) {
            $update_sql = "UPDATE messages SET is_read = 1 
                           WHERE receiver = ? AND sender = ? AND is_read = 0";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "ss", $user_id, $receiver);
            mysqli_stmt_execute($update_stmt);
        }
        
        echo json_encode([
            'status' => 'success',
            'messages' => $messages,
            'user_id' => $user_id
        ]);
        break;
        
    case 'get_unread_count':
        $sql = "SELECT COUNT(*) as count FROM messages 
                WHERE receiver = ? AND is_read = 0";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        echo json_encode([
            'status' => 'success',
            'unread_count' => $row['count']
        ]);
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Action không hợp lệ']);
}
?>
