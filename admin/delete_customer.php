<?php
include '../db/db_connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Xóa user
    $sql = "DELETE FROM users WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        header("Location: manage_customers.php");
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
}
?>