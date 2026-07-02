<?php include '../db/db_connect.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký khách hàng mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>.navbar-custom { background-color: #a8e6cf; }</style>
</head>
<body>

<nav class="navbar navbar-custom mb-4"><div class="container-fluid"><span class="navbar-brand fw-bold">Shop của tôi</span></div></nav>

<div class="container" style="max-width: 600px;">
    <h2 class="text-center text-uppercase text-secondary mb-4">ĐĂNG KÝ</h2>
    
    <form action="add_customer_process.php" method="POST">
        <div class="mb-3">
            <label class="form-label fw-bold">Họ tên</label>
            <input type="text" name="fullname" class="form-control" placeholder="Họ tên khách hàng" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">UserName</label>
            <input type="text" name="username" class="form-control" placeholder="UserName" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Confirm Password</label>
            <input type="password" name="re_password" class="form-control" placeholder="Confirm Password" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Địa chỉ</label>
            <input type="text" name="address" class="form-control" placeholder="Địa chỉ">
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Số điện thoại</label>
            <input type="text" name="phone" class="form-control" placeholder="Số điện thoại">
        </div>

        <button type="submit" name="add_user" class="btn btn-primary w-100">Sign in</button>
        </form>
    <br>
    <a href="manage_customers.php" class="text-decoration-none">< Quay lại danh sách</a>
</div>
</body>
</html>