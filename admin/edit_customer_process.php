<?php
include '../db/db_connect.php';

if (isset($_POST['update_user'])) {
    $id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $sql = "UPDATE users SET fullname='$fullname', phone='$phone', address='$address' WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        header("Location: manage_customers.php");
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
}
?>