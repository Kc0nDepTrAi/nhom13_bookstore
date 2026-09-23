<?php
// cart/checkout.php
session_start();
include '../../db/db_connect.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Vui lòng đăng nhập để thanh toán!'); window.location.href='../login.php';</script>";
    exit();
}

// 2. Kiểm tra giỏ hàng
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script>alert('Giỏ hàng trống!'); window.location.href='../index.php';</script>";
    exit();
}

$username = $_SESSION['username'];
$total_all = 0;

// Tính tổng tiền
foreach ($_SESSION['cart'] as $item) {
    $total_all += $item['price'] * $item['quantity'];
}

// 3. Tạo đơn hàng (INSERT vào bảng orders)
$sql_order = "INSERT INTO orders (username, total_price, status, created_at) 
              VALUES ('$username', '$total_all', 'Đang xử lý', NOW())";

if (mysqli_query($conn, $sql_order)) {
    // Lấy ID đơn hàng vừa tạo
    $order_id = mysqli_insert_id($conn);

    // 4. Lưu chi tiết đơn hàng VÀ Trừ kho
    foreach ($_SESSION['cart'] as $book_id => $item) {
        $price = $item['price'];
        $qty = $item['quantity'];
        
        // a. Lưu vào bảng order_details
        $sql_detail = "INSERT INTO order_details (order_id, book_id, quantity, price) 
                       VALUES ('$order_id', '$book_id', '$qty', '$price')";
        mysqli_query($conn, $sql_detail);

        // --- ĐOẠN CODE MỚI THÊM VÀO ĐÂY ---
        // b. Cập nhật lại kho hàng (Trừ tồn kho - quantity, Cộng đã bán - sold)
        $sql_update_stock = "UPDATE books 
                             SET quantity = quantity - $qty, 
                                 sold = sold + $qty 
                             WHERE id = '$book_id'";
        mysqli_query($conn, $sql_update_stock);
        // ----------------------------------
    }

    // 5. Xóa giỏ hàng và thông báo
    unset($_SESSION['cart']);
    echo "<script>alert('Đặt hàng thành công! Kho hàng đã được cập nhật.'); window.location.href='../index.php';</script>";
} else {
    echo "Lỗi: " . mysqli_error($conn);
}
?>