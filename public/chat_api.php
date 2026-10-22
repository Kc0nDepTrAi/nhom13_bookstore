<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once dirname(__DIR__) . '/db/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name('bookstore_sess');
    session_start();
}

// Kiểm tra xác thực - yêu cầu đăng nhập
if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['status' => 'error', 'message' => 'Bạn phải đăng nhập để sử dụng chức năng chat']);
    exit;
}

$user_id = $_SESSION['username'] ?? '';
if (empty($user_id) && !empty($_SESSION['user_id'])) {
    $u_stmt = mysqli_prepare($conn, "SELECT username FROM users WHERE id = ?");
    mysqli_stmt_bind_param($u_stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($u_stmt);
    $u_res = mysqli_stmt_get_result($u_stmt);
    if ($u_row = mysqli_fetch_assoc($u_res)) {
        $user_id = $u_row['username'];
        $_SESSION['username'] = $user_id;
    }
}

if (empty($user_id)) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy tài khoản người dùng']);
    exit;
}

// Danh sách các alias nhận diện (bao gồm username và các định danh cũ để không sót tin)
$aliases = [$user_id, 'user_' . $user_id];
if (!empty($_SESSION['user_id'])) {
    $aliases[] = 'user_' . $_SESSION['user_id'];
}
$aliases = array_values(array_unique(array_filter($aliases)));
$escaped_aliases = array_map(function($a) use ($conn) {
    return "'" . mysqli_real_escape_string($conn, $a) . "'";
}, $aliases);
$in_clause = implode(',', $escaped_aliases);

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'send':
        $message = trim($_POST['message'] ?? '');
        $receiver = 'admin';
        
        if (!empty($message)) {
            $sql = "INSERT INTO messages (sender, receiver, message) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $user_id, $receiver, $message);
            
            if (mysqli_stmt_execute($stmt)) {
                $message_id = mysqli_insert_id($conn);
                echo json_encode([
                    'status' => 'success',
                    'message_id' => $message_id,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'user_id' => $user_id
                ]);
            } else {
                $error = mysqli_error($conn);
                echo json_encode(['status' => 'error', 'message' => "Không thể gửi tin nhắn: $error"]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Tin nhắn không được rỗng']);
        }
        break;
        
    case 'get':
        $receiver = 'admin';
        $last_id = (int)($_POST['last_id'] ?? 0);
        
        // Lấy tin nhắn giữa user và admin (khớp mọi alias của user)
        $sql = "SELECT * FROM messages 
                WHERE ((sender IN ($in_clause) AND receiver = 'admin') OR (sender = 'admin' AND receiver IN ($in_clause))) 
                AND id > ?
                ORDER BY created_at ASC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $last_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $messages = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $messages[] = [
                'id' => (int)$row['id'],
                'sender' => $row['sender'],
                'receiver' => $row['receiver'],
                'message' => $row['message'],
                'created_at' => $row['created_at'],
                'is_read' => (int)$row['is_read'],
                'is_me' => in_array($row['sender'], $aliases, true)
            ];
        }
        
        // Đánh dấu đã đọc tin nhắn từ admin gửi tới user
        if (!empty($messages)) {
            $update_sql = "UPDATE messages SET is_read = 1 
                           WHERE receiver IN ($in_clause) AND sender = 'admin' AND is_read = 0";
            mysqli_query($conn, $update_sql);
        }
        
        echo json_encode([
            'status' => 'success',
            'messages' => $messages,
            'user_id' => $user_id
        ]);
        break;
        
    case 'get_unread_count':
        $sql = "SELECT COUNT(*) as count FROM messages 
                WHERE receiver IN ($in_clause) AND is_read = 0";
        $result = mysqli_query($conn, $sql);
        $row = $result ? mysqli_fetch_assoc($result) : ['count' => 0];
        
        echo json_encode([
            'status' => 'success',
            'unread_count' => (int)($row['count'] ?? 0)
        ]);
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Action không hợp lệ']);
}
?>