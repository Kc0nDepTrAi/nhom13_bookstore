<?php
// admin/manage_orders.php
include '../db/db_connect.php';

// XỬ LÝ CẬP NHẬT TRẠNG THÁI (Thêm phần này để nút chọn hoạt động)
if (isset($_POST['update_status'])) {
    bookstore_models();
    try {(new \App\Models\OrderModel())->updateStatus((int)$_POST['order_id'],(string)$_POST['status']);}
    catch (\Throwable $e) {bookstore_reject($e->getMessage(),400);}
    header('Location: manage_orders.php');exit();
}

$sql = "SELECT o.*, u.fullname 
        FROM orders o 
        LEFT JOIN users u ON o.username = u.username 
        ORDER BY o.created_at DESC";
$result = mysqli_query($conn, $sql);

$grand_total = 0;
?>

<?php
$stitchPage = 'orders';
require dirname(__DIR__, 1) . '/app/views/stitch/render.php';
?>
