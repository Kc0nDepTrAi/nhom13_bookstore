<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('bookstore_sess');
    session_start();
}
include '../db/db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Verify ownership before any changes, using the existing model.
if (isset($_GET['delete_id'])) bookstore_reject('Xóa đơn cần gửi bằng POST.',405);
if (isset($_POST['delete_id'])) {
    bookstore_models();
    try {(new \App\Models\OrderModel())->deleteWithDetails((int)$_POST['delete_id'],$username);}
    catch (\Throwable $e) {bookstore_reject($e->getMessage(),403);}
    header('Location: '.BASE_URL.'/my-orders');exit;
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

<?php
$stitchPage = 'account';
require dirname(__DIR__, 1) . '/app/views/stitch/render.php';
?>
