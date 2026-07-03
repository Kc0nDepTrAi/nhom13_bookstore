<?php
session_start();
include '../../db/db_connect.php';

// Xử lý Xóa
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = (int)$_GET['id'];
    unset($_SESSION['cart'][$id]);
}

// Xử lý Cập nhật số lượng
if (isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $id => $qty) {
        if ($qty <= 0) unset($_SESSION['cart'][$id]);
        else $_SESSION['cart'][$id]['quantity'] = (int)$qty;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng - Thư mục riêng</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f7f6; padding: 20px; }
        .cart-box { max-width: 900px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; }
        .img-cart { width: 60px; height: 80px; object-fit: cover; }
        .btn { padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; cursor: pointer; }
        .btn-update { background: #A8E6CF; border: none; }
        .btn-checkout { background: #4A708B; color: white; float: right; margin-top: 15px; }
    </style>
</head>
<body>

<div class="cart-box">
    <h2>🛒 Giỏ hàng của bạn (Thư mục /cart)</h2>
    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>Tên</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                    <th>Xóa</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total = 0;
                if (!empty($_SESSION['cart'])):
                    foreach ($_SESSION['cart'] as $id => $item): 
                        $sub = $item['price'] * $item['quantity'];
                        $total += $sub;
                ?>
                <tr>
                    <td><img src="../../images/<?php echo $item['image']; ?>" class="img-cart"></td>
                    <td><?php echo $item['title']; ?></td>
                    <td><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</td>
                    <td><input type="number" name="qty[<?php echo $id; ?>]" value="<?php echo $item['quantity']; ?>" min="1" style="width: 40px;"></td>
                    <td><?php echo number_format($sub, 0, ',', '.'); ?>đ</td>
                    <td><a href="view_cart.php?action=delete&id=<?php echo $id; ?>" style="color:red;">✖</a></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">Giỏ hàng trống. <a href="../index.php">Quay lại mua sắm</a></td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (!empty($_SESSION['cart'])): ?>
            <div style="text-align: right; margin-top: 15px;">
                <strong>Tổng cộng: <span style="color:red; font-size: 20px;"><?php echo number_format($total, 0, ',', '.'); ?>đ</span></strong>
            </div>
            <button type="submit" name="update_cart" class="btn btn-update">Cập nhật số lượng</button>
            <a href="checkout.php" class="btn btn-checkout">Thanh toán ngay</a>
        <?php endif; ?>
    </form>
</div>

</body>
</html>