<?php
session_start();
include '../db/db_connect.php'; 

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Truy vấn kiểm tra thông tin tài khoản
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Lưu thông tin vào phiên làm việc (Session)
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Kiểm tra nếu là admin thì chuyển vào thư mục admin
        if ($user['role'] == 'admin') {
            header("Location: ../admin/admin_dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        echo "<script>alert('Sai tài khoản hoặc mật khẩu!'); window.history.back();</script>";
    }
}
?>