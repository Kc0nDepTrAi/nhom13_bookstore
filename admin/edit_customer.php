<?php
include '../db/db_connect.php';
$id = $_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
$row = mysqli_fetch_assoc($query);
?>

<?php
$stitchPage = 'customers';
require dirname(__DIR__, 1) . '/app/views/stitch/render.php';
?>
