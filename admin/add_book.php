<?php
// admin/add_book.php
include '../db/db_connect.php';

// Lấy danh sách thể loại để hiển thị vào thẻ select
$sql_cate = "SELECT * FROM categories";
$result_cate = mysqli_query($conn, $sql_cate);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm sản phẩm mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CSS tùy chỉnh cho giống ảnh mẫu */
        .navbar-custom { background-color: #a8e6cf; color: #333; }
        .btn-custom { background-color: #0d6efd; color: white; }
        .form-label { font-weight: bold; color: #555; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom mb-4">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="admin_dashboard.php">Shop của tôi</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Trang chủ</a></li>
        <li class="nav-item"><a class="nav-link active" href="add_book.php">Thêm SP mới</a></li>
        <li class="nav-item"><a class="nav-link" href="public/logout.php">Đăng xuất</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
    <h2 class="text-center text-secondary mb-4">Thêm sản phẩm mới</h2>
    
    <form action="add_book_process.php" method="POST" enctype="multipart/form-data">
        
        <div class="mb-3">
            <label class="form-label">Tên sách (*)</label>
            <input type="text" name="title" class="form-control" required placeholder="Nhập tên sách...">
        </div>

        <div class="mb-3">
            <label class="form-label">Mô tả (*)</label>
            <textarea name="description" class="form-control" rows="3" required placeholder="Nhập mô tả..."></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Tác giả (*)</label>
            <input type="text" name="author" class="form-control" required placeholder="Nhập tên tác giả...">
        </div>

        <div class="mb-3">
            <label class="form-label">Giá (*)</label>
            <input type="number" name="price" class="form-control" required placeholder="Nhập giá tiền...">
        </div>

        <div class="mb-3">
            <label class="form-label">Thể loại sách</label>
            <select name="category_id" class="form-control">
                <?php while ($row = mysqli_fetch_assoc($result_cate)) { ?>
                    <option value="<?php echo $row['id']; ?>">
                        <?php echo $row['name']; ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">File hình ảnh</label>
            <input type="file" name="image" class="form-control" required>
        </div>

        <button type="submit" name="add_book" class="btn btn-primary w-100">Add new</button>
    </form>
    <br><br>
</div>

</body>
</html>