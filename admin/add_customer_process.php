<?php
include '../db/db_connect.php';

if (isset($_POST['add_user'])) {
    $fullname = $_POST['fullname'];
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $re_pass = $_POST['re_password'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    // 1. Kiểm tra mật khẩu nhập lại
    if ($pass != $re_pass) {
        echo "<script>alert('Mật khẩu nhập lại không khớp!'); window.history.back();</script>";
        exit();
    }

    // 2. Kiểm tra trùng username
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$user'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('Username này đã tồn tại!'); window.history.back();</script>";
        exit();
    }

    
    // 4. Thêm vào CSDL
    $sql = "INSERT INTO users (username, password, fullname, phone, address, role) 
            VALUES ('$user', '$pass', '$fullname', '$phone', '$address', 'user')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Thêm khách hàng thành công!'); window.location='manage_customers.php';</script>";
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
}
?>