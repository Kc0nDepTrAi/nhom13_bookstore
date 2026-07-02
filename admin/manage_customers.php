<?php
// admin/manage_customers.php
include '../db/db_connect.php';

// Lấy danh sách user (Không lấy admin)
$sql = "SELECT * FROM users WHERE role = 'user'";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-custom { background-color: #a8e6cf; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom mb-4">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="admin_dashboard.php">Shop của tôi</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Trang chủ</a></li>
        <li class="nav-item"><a class="nav-link" href="add_book.php">Thêm SP</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
    <h2 class="text-center text-secondary mb-4">Danh sách khách hàng</h2>
    
    <a href="add_customer.php" class="btn btn-secondary mb-3">Add new</a>

    <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>STT</th>
                <th>Tên khách hàng</th>
                <th>Số điện thoại</th>
                <th>Địa chỉ</th>
                <th>UserName</th>
                <th>Password</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $stt = 1;
            while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?php echo $stt++; ?></td>
                <td><?php echo $row['fullname']; ?></td>
                <td><?php echo $row['phone']; ?></td>
                <td><?php echo $row['address']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td style="font-size: 0.8em; color: gray;">
                    <?php echo substr($row['password'], 0, 15) . "..."; ?>
                </td>
                <td>
                    <a href="edit_customer.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm text-white">Edit</a>
                    <a href="delete_customer.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa khách hàng này?');">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>