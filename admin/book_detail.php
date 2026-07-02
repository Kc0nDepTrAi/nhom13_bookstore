<?php
// admin/book_detail.php
include '../db/db_connect.php';

// Lấy ID sách từ URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM books WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    $book = mysqli_fetch_assoc($result);
} else {
    die("Không tìm thấy sản phẩm");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-custom { background-color: #e6f2ff; } /* Màu xanh nhạt giống ảnh */
        .header-col { background-color: #2c3e50; color: white; width: 200px; } /* Cột tiêu đề màu đen */
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg" style="background-color: #a8e6cf;">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">Shop của tôi</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto">
            <li class="nav-item"><a class="nav-link" href="add_book.php">Thêm SP mới</a></li>
        </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <h2 class="text-center text-secondary mb-4">Thông tin sản phẩm</h2>

    <table class="table table-bordered table-custom align-middle">
        <tr>
            <th class="header-col">Mã sản phẩm</th>
            <td><?php echo $book['id']; ?></td>
        </tr>
        <tr>
            <th class="header-col">Tên sản phẩm</th>
            <td><?php echo $book['title']; ?></td>
        </tr>
        <tr>
            <th class="header-col">Mô tả</th>
            <td><?php echo $book['description']; ?></td>
        </tr>
         <tr>
            <th class="header-col">Tác giả</th>
            <td><?php echo $book['author']; ?></td>
        </tr>
        <tr>
            <th class="header-col">Giá</th>
            <td><?php echo number_format($book['price'], 0, ',', '.'); ?> VNĐ</td>
        </tr>
        <tr>
            <th class="header-col">Thể loại</th>
            <td><?php echo $book['category']; ?></td>
        </tr>
        <tr>
            <th class="header-col">Hình ảnh</th>
            <td>
                <img src="../images/<?php echo $book['image']; ?>" width="100" style="border-radius: 5px;">
            </td>
        </tr>
    </table>

    <div class="mt-3">
                <a href="delete_book.php?id=<?php echo $book['id']; ?>" class="btn btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?');">Delete</a>
        <a href="admin_dashboard.php" class="btn btn-secondary">Quay lại</a>
    </div>

</div>

</body>
</html>