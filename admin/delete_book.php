<?php
include '../db/db_connect.php';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    mysqli_query($conn, "DELETE FROM books WHERE id = $id");
}
header("Location: admin_dashboard.php");
exit();
?>