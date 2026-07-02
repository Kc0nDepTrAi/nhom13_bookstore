<?php
include '../db/db_connect.php';
$id = $_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
$row = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>.navbar-custom { background-color: #a8e6cf; }</style>
</head>
<body>
<nav class="navbar navbar-custom mb-4"><div class="container-fluid"><span class="navbar-brand fw-bold">Shop của tôi</span></div></nav>

<div class="container" style="max-width: 600px;">
    <h3 class="text-center text-secondary mb-4">
        Chỉnh sửa khách hàng có UserName là <span class="text-dark"><?php echo $row['username']; ?></span>
    </h3>
    
    <form action="edit_customer_process.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        
        <div class="mb-3">
            <label class="form-label fw-bold">Họ tên</label>
            <input type="text" name="fullname" class="form-control" value="<?php echo $row['fullname']; ?>">
        </div>
        
        <div class="mb-3">
            <label class="form-label fw-bold">Số điện thoại</label>
            <textarea name="phone" class="form-control" rows="2"><?php echo $row['phone']; ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Địa chỉ</label>
            <input type="text" name="address" class="form-control" value="<?php echo $row['address']; ?>">
        </div>

        <div class="text-center">
            <button type="submit" name="update_user" class="btn btn-primary px-4">Submit</button>
        </div>
    </form>
</div>
</body>
</html>