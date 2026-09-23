<?php
include '../db/db_connect.php';

if (isset($_POST['register'])) {
    // Lấy dữ liệu từ form
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
    $address  = mysqli_real_escape_string($conn, $_POST['address']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role     = 'user'; // Mặc định tài khoản mới luôn là user

    // Kiểm tra tài khoản đã tồn tại chưa
    $check_query = "SELECT * FROM users WHERE username = '$username'";
    $check_res = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_res) > 0) {
        echo "<script>alert('Tên tài khoản này đã có người sử dụng!');</script>";
    } else {
        // Chèn dữ liệu vào bảng users
        $sql = "INSERT INTO users (username, password, fullname, phone, address, role) 
                VALUES ('$username', '$password', '$fullname', '$phone', '$address', '$role')";

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Đăng ký thành công!'); window.location='login.php';</script>";
        } else {
            echo "Lỗi: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký thành viên</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .reg-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 350px; }
        h2 { text-align: center; color: #4A708B; margin-bottom: 20px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-submit { background-color: #A8E6CF; border: none; padding: 12px; width: 100%; border-radius: 5px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background-color: #8eddbd; }
        .back-link { text-align: center; margin-top: 15px; display: block; text-decoration: none; color: #666; font-size: 14px; }
    </style>
</head>
<body>

<div class="reg-box">
    <h2>ĐĂNG KÝ</h2>
    <form action="" method="POST">
        <input type="text" name="fullname" placeholder="Họ và tên" required>
        <input type="text" name="phone" placeholder="Số điện thoại" required>
        <input type="text" name="address" placeholder="Địa chỉ" required>
        <input type="text" name="username" placeholder="Tên đăng nhập" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <button type="submit" name="register" class="btn-submit">ĐĂNG KÝ NGAY</button>
    </form>
    <a href="login.php" class="back-link">Quay lại Đăng nhập</a>
</div>

</body>
</html>