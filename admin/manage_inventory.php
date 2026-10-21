<?php
// admin/manage_inventory.php
include '../db/db_connect.php';

// --- PHẦN 1: XỬ LÝ KHI NGƯỜI DÙNG ẤN NÚT LƯU ---
if (isset($_POST['btn_update_stock'])) {
    $data=['quantity'=>$_POST['quantity']??null,'sold'=>$_POST['sold']??null];
    try {bookstore_validate_book($data);} catch (\Throwable $e) {bookstore_reject($e->getMessage(),400);}
    $stmt=$conn->prepare('UPDATE books SET sold=?, quantity=? WHERE id=?');
    $stmt->bind_param('iii',$data['sold'],$data['quantity'],$_POST['book_id']);$stmt->execute();
    header('Location: manage_inventory.php');exit;
}

// --- PHẦN 2: LẤY DANH SÁCH SÁCH ---
$sql = "SELECT * FROM books ORDER BY id ASC";
$result = mysqli_query($conn, $sql);
?>

<?php
$stitchPage = 'categories';
require dirname(__DIR__, 1) . '/app/views/stitch/render.php';
?>
