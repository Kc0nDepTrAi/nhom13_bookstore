<?php
// admin/manage_inventory.php
include '../db/db_connect.php';

// --- PHẦN 1: XỬ LÝ KHI NGƯỜI DÙNG ẤN NÚT LƯU ---
if (isset($_POST['btn_update_stock'])) {
    $id = $_POST['book_id'];
    $sold = $_POST['sold'];
    $quantity = $_POST['quantity'];

    $sql_update = "UPDATE books SET sold = '$sold', quantity = '$quantity' WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql_update)) {
        echo "<script>alert('Cập nhật kho thành công!'); window.location.href='manage_inventory.php';</script>";
    } else {
        echo "<script>alert('Lỗi: " . mysqli_error($conn) . "');</script>";
    }
}

// --- PHẦN 2: LẤY DANH SÁCH SÁCH ---
$sql = "SELECT * FROM books ORDER BY id ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý tồn kho</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-custom { background-color: #a8e6cf; }
        /* CSS cho Modal */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 40%; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close:hover { color: black; }
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
    <h2 class="text-center text-secondary mb-4">Quản lý tồn kho</h2>
    
    <table class="table table-striped table-bordered align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th width="10%">Mã sách</th>
                <th width="35%">Tên sách</th>
                <th width="15%">Hình ảnh</th>
                <th width="15%">Đã bán</th>
                <th width="15%">Còn lại</th>
                <th width="10%">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php 
$stt = 1; // Khởi tạo biến đếm bắt đầu từ 1
while ($row = mysqli_fetch_assoc($result)) { ?>
<tr>
    <td><?php echo $stt++; ?></td>
                <td class="text-start fw-bold"><?php echo $row['title']; ?></td>
                <td>
                    <img src="../images/<?php echo $row['image']; ?>" width="50" style="border-radius:5px;">
                </td>
                
                <td class="text-success fw-bold"><?php echo $row['sold']; ?></td>
                <td class="text-danger fw-bold"><?php echo $row['quantity']; ?></td>
                
                <td>
                    <button class="btn btn-info btn-sm text-white" 
                            onclick="openEditModal('<?php echo $row['id']; ?>', '<?php echo $row['sold']; ?>', '<?php echo $row['quantity']; ?>')">
                        Edit
                    </button>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h4 class="mb-4 text-center text-primary">Cập nhật kho hàng</h4>
        
        <form method="POST" action="">
            <input type="hidden" id="modal_book_id" name="book_id">

            <div class="mb-3">
                <label class="form-label fw-bold">Đã bán:</label>
                <input type="number" class="form-control" id="modal_sold" name="sold" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Còn lại (Tồn kho):</label>
                <input type="number" class="form-control" id="modal_quantity" name="quantity" required>
            </div>

            <div class="d-grid">
                <button type="submit" name="btn_update_stock" class="btn btn-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
    var modal = document.getElementById("editModal");

    // Hàm mở Modal và điền dữ liệu cũ vào ô input
    function openEditModal(id, sold, quantity) {
        document.getElementById("modal_book_id").value = id;
        document.getElementById("modal_sold").value = sold;
        document.getElementById("modal_quantity").value = quantity;
        
        modal.style.display = "block";
    }

    // Hàm đóng Modal
    function closeModal() {
        modal.style.display = "none";
    }

    // Click ra ngoài thì đóng modal
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

</body>
</html>