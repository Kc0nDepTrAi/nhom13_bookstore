<?php
// admin/book_detail.php
include '../db/db_connect.php';

// Lấy ID sách từ URL
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM books WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    $book = mysqli_fetch_assoc($result);
} else {
    die("Không tìm thấy sản phẩm");
}
?>

<?php
$stitchPage = 'editor';
require dirname(__DIR__, 1) . '/app/views/stitch/render.php';
?>
