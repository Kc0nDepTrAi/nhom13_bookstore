<?php
// admin/delete_order.php
include '../db/db_connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Câu lệnh xóa đơn hàng
    $sql = "DELETE FROM orders WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        // Xóa xong quay lại trang quản lý
        header("Location: manage_orders.php");
    } else {
        echo "Lỗi xóa đơn hàng: " . mysqli_error($conn);
    }
}
?>