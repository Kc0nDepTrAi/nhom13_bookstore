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
if ((!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') && (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin')) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'reply':
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
        break;
        
    case 'get_messages':
        $user_id = trim($_REQUEST['user_id'] ?? '');
        $last_id = (int)($_REQUEST['last_id'] ?? 0);
        
        if (!empty($user_id)) {
            // Chuẩn bị các alias của user để không sót tin
            $aliases = [$user_id, 'user_' . $user_id];
            $u_stmt = mysqli_prepare($conn, "SELECT id, username FROM users WHERE username = ?");
            mysqli_stmt_bind_param($u_stmt, "s", $user_id);
            mysqli_stmt_execute($u_stmt);
            $u_res = mysqli_stmt_get_result($u_stmt);
            if ($u_row = mysqli_fetch_assoc($u_res)) {
                $aliases[] = 'user_' . $u_row['id'];
            }
            $aliases = array_values(array_unique(array_filter($aliases)));
            $escaped = array_map(function($a) use ($conn) {
                return "'" . mysqli_real_escape_string($conn, $a) . "'";
            }, $aliases);
            $in_clause = implode(',', $escaped);
            
            // Đánh dấu đã đọc
            $mark_read_sql = "UPDATE messages SET is_read = 1 WHERE receiver = 'admin' AND sender IN ($in_clause) AND is_read = 0";
            mysqli_query($conn, $mark_read_sql);
            
            // Lấy tin nhắn
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
                    'message' => $row['message'],
                    'is_me' => $row['sender'] === 'admin',
                    'created_at' => $row['created_at']
                ];
            }
            
            echo json_encode(['status' => 'success', 'messages' => $messages]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu user_id']);
        }
        break;
        
    case 'get_users':
        $sql = "SELECT 
                    CASE 
                        WHEN sender = 'admin' THEN receiver 
                        ELSE sender 
                    END as raw_user,
                    MAX(created_at) as last_message_time,
                    MAX(id) as last_message_id
                FROM messages 
                WHERE sender != 'admin' OR receiver != 'admin'
                GROUP BY raw_user
                ORDER BY last_message_time DESC";
        $result = mysqli_query($conn, $sql);
        
        $users_map = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $raw = $row['raw_user'];
                if ($raw === 'admin' || empty($raw)) continue;
                
                $cleaned = $raw;
                if (str_starts_with($raw, 'user_')) {
                    $sub = substr($raw, 5);
                    $check_stmt = mysqli_prepare($conn, "SELECT username FROM users WHERE username = ? OR id = ?");
                    mysqli_stmt_bind_param($check_stmt, "ss", $sub, $sub);
                    mysqli_stmt_execute($check_stmt);
                    $check_res = mysqli_stmt_get_result($check_stmt);
                    if ($c_row = mysqli_fetch_assoc($check_res)) {
                        $cleaned = $c_row['username'];
                    }
                }
                if ($cleaned === 'admin') continue;
                
                if (!isset($users_map[$cleaned])) {
                    $users_map[$cleaned] = [
                        'username' => $cleaned,
                        'raw_aliases' => [$raw, $cleaned],
                        'last_message_time' => $row['last_message_time'],
                        'last_message_id' => (int)$row['last_message_id']
                    ];
                } else {
                    $users_map[$cleaned]['raw_aliases'][] = $raw;
                    if ((int)$row['last_message_id'] > $users_map[$cleaned]['last_message_id']) {
                        $users_map[$cleaned]['last_message_id'] = (int)$row['last_message_id'];
                        $users_map[$cleaned]['last_message_time'] = $row['last_message_time'];
                    }
                }
            }
        }
        
        $users = [];
        foreach ($users_map as $uname => $meta) {
            $u_stmt = mysqli_prepare($conn, "SELECT id, username, fullname, email, phone, address FROM users WHERE username = ?");
            mysqli_stmt_bind_param($u_stmt, "s", $uname);
            mysqli_stmt_execute($u_stmt);
            $u_res = mysqli_stmt_get_result($u_stmt);
            $u_data = $u_res ? mysqli_fetch_assoc($u_res) : null;
            
            $fullname = !empty($u_data['fullname']) ? $u_data['fullname'] : $uname;
            $email = !empty($u_data['email']) ? $u_data['email'] : 'Chưa cập nhật email';
            $phone = !empty($u_data['phone']) ? $u_data['phone'] : 'Chưa cập nhật SĐT';
            $address = !empty($u_data['address']) ? $u_data['address'] : 'Chưa cập nhật địa chỉ';
            
            // Avatar
            $parts = preg_split('/\s+/u', trim($fullname));
            if (count($parts) >= 2) {
                $initials = mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1);
            } else {
                $initials = mb_substr($fullname, 0, 2);
            }
            $avatar = mb_strtoupper($initials);
            
            // Aliases count unread
            $aliases = array_values(array_unique(array_filter(array_merge($meta['raw_aliases'], [$uname, 'user_' . $uname, 'user_' . ($u_data['id'] ?? '')]))));
            $escaped = array_map(function($a) use ($conn) {
                return "'" . mysqli_real_escape_string($conn, $a) . "'";
            }, $aliases);
            $in_clause = implode(',', $escaped);
            
            $unread_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM messages WHERE sender IN ($in_clause) AND receiver = 'admin' AND is_read = 0");
            $unread_row = $unread_res ? mysqli_fetch_assoc($unread_res) : ['c' => 0];
            $unread_count = (int)($unread_row['c'] ?? 0);
            
            // Last message snippet
            $last_msg_res = mysqli_query($conn, "SELECT message FROM messages WHERE id = " . $meta['last_message_id']);
            $last_msg_row = $last_msg_res ? mysqli_fetch_assoc($last_msg_res) : null;
            $last_message_text = $last_msg_row['message'] ?? '';
            
            // Latest order
            $order_res = mysqli_query($conn, "SELECT id, status FROM orders WHERE username = '" . mysqli_real_escape_string($conn, $uname) . "' ORDER BY id DESC LIMIT 1");
            $order_row = $order_res ? mysqli_fetch_assoc($order_res) : null;
            $order_id = $order_row ? (int)$order_row['id'] : null;
            
            $users[] = [
                'user_id' => $uname,
                'username' => $uname,
                'fullname' => $fullname,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'avatar' => $avatar,
                'last_message' => $meta['last_message_time'],
                'last_message_text' => $last_message_text,
                'unread_count' => $unread_count,
                'order_id' => $order_id
            ];
        }
        
        // Sắp xếp theo tin nhắn mới nhất
        usort($users, function($a, $b) {
            return strcmp($b['last_message'], $a['last_message']);
        });
        
        echo json_encode(['status' => 'success', 'users' => $users]);
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Action không hợp lệ']);
}
?>