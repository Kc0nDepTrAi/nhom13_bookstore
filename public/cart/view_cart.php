<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('bookstore_sess');
    session_start();
}
include '../../db/db_connect.php';

// Xử lý Xóa
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = (int)$_GET['id'];
    unset($_SESSION['cart'][$id]);
}

// Xử lý Cập nhật số lượng
if (isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $id => $qty) {
        if ($qty <= 0) unset($_SESSION['cart'][$id]);
        else $_SESSION['cart'][$id]['quantity'] = (int)$qty;
    }
}
?>

<?php
$stitchPage = 'cart';
require dirname(__DIR__, 2) . '/app/views/stitch/render.php';
?>
