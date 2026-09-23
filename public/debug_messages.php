<?php
include '../db/db_connect.php';

// Kiểm tra tin nhắn mới nhất
echo "<h2>Kiểm tra tin nhắn mới nhất:</h2>";

$sql = "SELECT * FROM messages ORDER BY created_at DESC LIMIT 10";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Sender</th><th>Receiver</th><th>Message</th><th>Time</th><th>Read</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $bgColor = $row['created_at'] > date('Y-m-d H:i:s', strtotime('-5 minutes')) ? '#e8f5e8' : 'white';
        echo "<tr style='background: {$bgColor};'>";
        echo "<td>{$row['id']}</td>";
        echo "<td><strong>{$row['sender']}</strong></td>";
        echo "<td>{$row['receiver']}</td>";
        echo "<td>" . htmlspecialchars($row['message']) . "</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "<td>" . ($row['is_read'] ? '✅' : '🔴') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><h3>Thống kê:</h3>";
    $total = mysqli_query($conn, "SELECT COUNT(*) as total FROM messages");
    $total_row = mysqli_fetch_assoc($total);
    echo "Tổng tin nhắn: " . $total_row['total'] . "<br>";
    
    $unread = mysqli_query($conn, "SELECT COUNT(*) as unread FROM messages WHERE receiver = 'admin' AND is_read = 0");
    $unread_row = mysqli_fetch_assoc($unread);
    echo "Tin nhắn chưa đọc cho admin: " . $unread_row['unread'] . "<br>";
    
    $users = mysqli_query($conn, "SELECT COUNT(DISTINCT sender) as users FROM messages WHERE sender != 'admin'");
    $users_row = mysqli_fetch_assoc($users);
    echo "Số user đã chat: " . $users_row['users'] . "<br>";
    
} else {
    echo "❌ Chưa có tin nhắn nào trong database";
}

echo "<br><a href='javascript:history.back()'>Quay lại</a>";
?>
