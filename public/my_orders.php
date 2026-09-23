<?php
session_start();
include '../db/db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Xử lý xóa đơn hàng
if (isset($_GET['delete_id'])) {
    $order_id = (int)$_GET['delete_id'];
    
    // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
    mysqli_begin_transaction($conn);
    
    try {
        // Lấy thông tin các sản phẩm trong đơn hàng để hoàn lại số lượng
        $get_details_sql = "SELECT book_id, quantity FROM order_details WHERE order_id = $order_id";
        $details_result = mysqli_query($conn, $get_details_sql);
        
        if ($details_result) {
            // Hoàn lại số lượng cho từng sản phẩm
            while ($detail = mysqli_fetch_assoc($details_result)) {
                $book_id = $detail['book_id'];
                $order_quantity = $detail['quantity'];
                
                // Tăng số lượng tồn kho
                $update_stock_sql = "UPDATE books SET quantity = quantity + $order_quantity WHERE id = $book_id";
                mysqli_query($conn, $update_stock_sql);
            }
        }
        
        // Xóa chi tiết đơn hàng
        $delete_details_sql = "DELETE FROM order_details WHERE order_id = $order_id";
        mysqli_query($conn, $delete_details_sql);
        
        // Xóa đơn hàng
        $del_sql = "DELETE FROM orders WHERE id = $order_id AND username = '$username'";
        if (mysqli_query($conn, $del_sql)) {
            mysqli_commit($conn);
            echo "<script>alert('Đã xóa đơn hàng và hoàn lại số lượng sản phẩm!'); window.location='my_orders.php';</script>";
        } else {
            throw new Exception("Không thể xóa đơn hàng");
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Có lỗi xảy ra: " . $e->getMessage() . "'); window.location='my_orders.php';</script>";
    }
}

// Truy vấn lấy đơn hàng kèm danh sách tên sách đã đặt
$sql = "SELECT o.*, 
        (SELECT GROUP_CONCAT(b.title SEPARATOR ', ') 
         FROM order_details od 
         JOIN books b ON od.book_id = b.id 
         WHERE od.order_id = o.id) as list_books
        FROM orders o 
        WHERE o.username = '$username' 
        ORDER BY o.id ASC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đơn hàng của tôi</title>
    <style>
        :root { --main-color: #A8E6CF; --text-color: #4A708B; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        .navbar { background-color: var(--main-color); padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 20px; font-weight: bold; color: #333; text-decoration: none; }
        
        .container { width: 90%; margin: 30px auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        h2 { color: var(--text-color); border-bottom: 2px solid var(--main-color); padding-bottom: 10px; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f1f1f1; padding: 12px; text-align: left; border-bottom: 2px solid #ddd; }
        td { padding: 12px; border-bottom: 1px solid #eee; }

        .status-badge { padding: 4px 10px; border-radius: 10px; font-size: 12px; font-weight: bold; background: #eee; }
        .btn-delete { color: #ff5c5c; text-decoration: none; font-weight: bold; border: 1px solid #ff5c5c; padding: 5px 10px; border-radius: 5px; transition: 0.3s; }
        .btn-delete:hover { background: #ff5c5c; color: white; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">← Quay lại Shop</a>
    <div>Xin chào, <strong><?php echo $username; ?></strong></div>
</nav>

<div class="container">
    <h2>📦 Đơn hàng của tôi</h2>
    <table>
        <thead>
            <tr>
                <th width="10%">Mã đơn</th>
                <th width="45%">Sách đã đặt</th>
                <th width="15%">Tổng tiền</th>
                <th width="15%">Trạng thái</th>
                <th width="15%">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td style="font-size: 14px; color: #555;">
                        <?php echo $row['list_books'] ? $row['list_books'] : 'Không rõ sản phẩm'; ?>
                    </td>
                    <td style="font-weight: bold; color: #d9534f;">
                        <?php echo number_format($row['total_price'], 0, ',', '.'); ?>đ
                    </td>
                    <td>
                        <span class="status-badge"><?php echo $row['status']; ?></span>
                    </td>
                    <td>
                        <a href="my_orders.php?delete_id=<?php echo $row['id']; ?>" 
                           class="btn-delete" 
                           onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này?')">
                           Xoá đơn
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center; padding: 30px;">Bạn chưa có đơn hàng nào.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>