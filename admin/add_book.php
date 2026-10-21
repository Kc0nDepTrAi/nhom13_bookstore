<?php
// admin/add_book.php
include '../db/db_connect.php';

// Lấy danh sách thể loại để hiển thị vào thẻ select
$sql_cate = "SELECT * FROM categories";
$result_cate = mysqli_query($conn, $sql_cate);
?>

<?php
$stitchPage = 'editor';
require dirname(__DIR__, 1) . '/app/views/stitch/render.php';
?>
