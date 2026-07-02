<?php
// admin/manage_orders.php
include '../db/db_connect.php';

// XỬ LÝ CẬP NHẬT TRẠNG THÁI (Thêm phần này để nút chọn hoạt động)
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $update_sql = "UPDATE orders SET status = '$new_status' WHERE id = $order_id";
    mysqli_query($conn, $update_sql);
    header("Location: manage_orders.php"); // Load lại trang để thấy thay đổi
    exit();
}

$sql = "SELECT o.*, u.fullname 
        FROM orders o 
        LEFT JOIN users u ON o.username = u.username 
        ORDER BY o.created_at DESC";
$result = mysqli_query($conn, $sql);

$grand_total = 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý hóa đơn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-custom { background-color: #a8e6cf; }
        .sub-table { font-size: 0.9rem; background-color: #f8f9fa; width: 100%; }
        .sub-table th { background-color: #e9ecef; }
        .total-footer { font-size: 1.2rem; font-weight: bold; text-align: right; margin-top: 20px; color: #2c3e50; }
        /* Style cho select trạng thái */
        .status-select { font-size: 0.85rem; padding: 2px 5px; border-radius: 4px; }
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

<div class="container-fluid px-4"> 
    <h2 class="text-center text-secondary mb-4">Danh sách hóa đơn</h2>
    
    <table class="table table-bordered align-middle">
        <thead class="table-dark text-center">
            <tr>
                <th width="5%">STT</th>
                <th width="12%">Tên khách hàng</th>
                <th width="10%">Ngày lập</th>
                <th width="48%">Chi tiết hóa đơn</th>
                <th width="10%">Tổng tiền</th>
                <th width="10%">Trạng thái</th> <th width="5%">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $stt = 1;
            while ($row = mysqli_fetch_assoc($result)) { 
                $order_id = $row['id'];
                $current_status = isset($row['status']) ? $row['status'] : 'Chờ xử lý';
                $grand_total += $row['total_price'];
            ?>
            <tr style="background-color: #e6f2ff;"> 
                <td class="text-center"><?php echo $stt++; ?></td>
                <td><b><?php echo $row['fullname'] ? $row['fullname'] : $row['username']; ?></b></td>
                <td class="text-center"><?php echo date("d-m-Y", strtotime($row['created_at'])); ?></td>
                
                <td class="p-0">
                    <?php
                    $sql_detail = "SELECT od.*, b.title FROM order_details od JOIN books b ON od.book_id = b.id WHERE od.order_id = $order_id";
                    $res_detail = mysqli_query($conn, $sql_detail);
                    ?>
                    <table class="table mb-0 sub-table table-borderless">
                        <tr style="border-bottom: 1px solid #ccc;">
                            <th width="10%">Mã SP</th>
                            <th width="40%">Tên SP</th>
                            <th width="15%">Số lượng</th>
                            <th width="15%">Đơn giá</th>
                            <th width="20%">Tổng tiền</th>
                        </tr>
                        <?php while ($item = mysqli_fetch_assoc($res_detail)) { ?>
                        <tr>
                            <td><?php echo $item['book_id']; ?></td>
                            <td><?php echo $item['title']; ?></td>
                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['price'], 0, ',', '.'); ?> đ</td>
                            <td class="fw-bold"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> đ</td>
                        </tr>
                        <?php } ?>
                    </table>
                </td>

                <td class="text-end fw-bold text-danger">
                    <?php echo number_format($row['total_price'], 0, ',', '.'); ?> VNĐ
                </td>

                <td class="text-center">
                    <form method="POST">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <select name="status" class="form-select status-select" onchange="this.form.submit()">
                            <option value="Chờ xử lý" <?php if($current_status == 'Chờ xử lý') echo 'selected'; ?>>Chờ xử lý</option>
                            <option value="Đang giao" <?php if($current_status == 'Đang giao') echo 'selected'; ?>>Đang giao</option>
                            <option value="Đã hoàn thành" <?php if($current_status == 'Đã hoàn thành') echo 'selected'; ?>>Đã hoàn thành</option>
                            <option value="Đã hủy" <?php if($current_status == 'Đã hủy') echo 'selected'; ?>>Đã hủy</option>
                        </select>
                        <input type="hidden" name="update_status" value="1">
                    </form>
                </td>
                
                <td class="text-center">
                    <a href="delete_order.php?id=<?php echo $order_id; ?>" 
                       class="btn btn-danger btn-sm" 
                       onclick="return confirm('Bạn có chắc muốn xóa hóa đơn này không?');">
                       Delete
                    </a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="total-footer">
        Tổng thành tiền: <span class="text-danger"><?php echo number_format($grand_total, 0, ',', '.'); ?> VNĐ</span>
    </div>
    <br><br>
</div>

</body>
</html>