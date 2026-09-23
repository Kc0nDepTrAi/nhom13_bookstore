<?php
session_start();
include '../db/db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$user_session = $_SESSION['username'];

// 1. LẤY THÔNG TIN
$sql = "SELECT * FROM users WHERE username = '$user_session'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// 2. XỬ LÝ CẬP NHẬT THÔNG TIN CƠ BẢN
if (isset($_POST['update_profile'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $update_sql = "UPDATE users SET fullname='$fullname', phone='$phone', address='$address' WHERE username='$user_session'";
    if (mysqli_query($conn, $update_sql)) {
        echo "<script>alert('Cập nhật thông tin thành công!'); window.location='profile.php';</script>";
    }
}

// 3. XỬ LÝ ĐỔI MẬT KHẨU
if (isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($old_pass !== $user['password']) {
        echo "<script>alert('Mật khẩu cũ không chính xác!');</script>";
    } elseif ($new_pass !== $confirm_pass) {
        echo "<script>alert('Mật khẩu mới không khớp nhau!');</script>";
    } else {
        $pwd_sql = "UPDATE users SET password='$new_pass' WHERE username='$user_session'";
        if (mysqli_query($conn, $pwd_sql)) {
            echo "<script>alert('Đổi mật khẩu thành công!'); window.location='profile.php';</script>";
        }
    }
}

// 4. XỬ LÝ XÓA TÀI KHOẢN
if (isset($_POST['delete_account'])) {
    $delete_sql = "DELETE FROM users WHERE username = '$user_session'";
    if (mysqli_query($conn, $delete_sql)) {
        session_destroy();
        echo "<script>alert('Tài khoản đã được xóa vĩnh viễn!'); window.location='index.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ cá nhân</title>
    <style>
        :root { --main-color: #A8E6CF; --text-color: #4A708B; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f6; margin: 0; }
        .navbar { background-color: var(--main-color); padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .logo { font-size: 20px; font-weight: bold; color: #333; text-decoration: none; }
        
        .container { width: 900px; margin: 40px auto; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        h3 { color: var(--text-color); margin-top: 0; border-bottom: 2px solid var(--main-color); padding-bottom: 10px; margin-bottom: 20px; text-align: center;}
        
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-size: 14px; font-weight: 600; color: #666; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; outline: none; transition: 0.3s; }
        input:focus { border-color: var(--text-color); }

        .btn { width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-primary { background-color: var(--text-color); color: white; }
        .btn-primary:hover { background-color: #3a5a70; }
        .btn-outline { background: none; border: 2px solid #ff8b8b; color: #ff8b8b; }
        .btn-outline:hover { background: #ff8b8b; color: white; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">← Shop của tôi</a>
    <div style="color: #555;">Xin chào, <strong><?php echo $user['fullname']; ?></strong></div>
</nav>

<div class="container">
    <div class="card">
        <h3>Thông tin cá nhân</h3>
        <form action="" method="POST">
            <div class="form-group">
                <label>Họ và tên</label>
                <input type="text" name="fullname" value="<?php echo $user['fullname']; ?>" required>
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="text" name="phone" value="<?php echo $user['phone']; ?>" required>
            </div>
            <div class="form-group">
                <label>Địa chỉ</label>
                <input type="text" name="address" value="<?php echo $user['address']; ?>" required>
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary">LƯU THÔNG TIN</button>
        </form>
    </div>

    <div class="card">
        <h3>Đổi mật khẩu</h3>
        <form action="" method="POST">
            <div class="form-group">
                <label>Mật khẩu hiện tại</label>
                <input type="password" name="old_password" placeholder="Nhập mật khẩu cũ" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu mới</label>
                <input type="password" name="new_password" placeholder="Mật khẩu mới" required>
            </div>
            <div class="form-group">
                <label>Xác nhận mật khẩu mới</label>
                <input type="password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required>
            </div>
            <button type="submit" name="change_password" class="btn btn-primary" style="background-color: #8eddbd; color: #333;">ĐỔI MẬT KHẨU</button>
        </form>

        <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">
        
        <form action="" method="POST">
            <button type="submit" name="delete_account" class="btn btn-outline" onclick="return confirm('Hành động này không thể hoàn tác. Bạn có chắc chắn muốn xóa tài khoản?')">XÓA TÀI KHOẢN VĨNH VIỄN</button>
        </form>
    </div>
</div>

</body>
</html>