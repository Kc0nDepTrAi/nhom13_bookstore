<?php
// admin/manage_customers.php
include '../db/db_connect.php';

// Lấy danh sách user (Không lấy admin)
$sql = "SELECT * FROM users WHERE role = 'user'";
$result = mysqli_query($conn, $sql);
?>

<?php
$stitchPage = 'customers';
require dirname(__DIR__, 1) . '/app/views/stitch/render.php';
?>
