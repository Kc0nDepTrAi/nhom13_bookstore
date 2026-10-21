<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('bookstore_sess');
    session_start();
}
if (empty($_SESSION['username'])) {
    if (isset($_GET['ajax'])) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thêm sách vào giỏ hàng.'], JSON_UNESCAPED_UNICODE);
        exit();
    }
    header('Location: ../login.php?reason=cart');
    exit();
}

include '../../db/db_connect.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') bookstore_reject('Thêm giỏ hàng cần gửi bằng POST.',405);

if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $sql = "SELECT * FROM books WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    $book = mysqli_fetch_assoc($result);

    if ($book) {
        $currentQty = (int) ($_SESSION['cart'][$id]['quantity'] ?? 0);
        if ($currentQty >= (int) $book['quantity']) {
            if (isset($_GET['ajax'])) {
                http_response_code(400);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Số lượng trong giỏ đã đạt giới hạn tồn kho.'], JSON_UNESCAPED_UNICODE);
                exit();
            }
            header('Location: view_cart.php?cart_error=stock');
            exit();
        }
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] += 1;
        } else {
            $_SESSION['cart'][$id] = [
                'book_id' => $id,
                'author' => $book['author'],
                'title' => $book['title'],
                'price' => $book['price'],
                'image' => $book['image'],
                'quantity' => 1
            ];
        }
    }
}

// Nếu là yêu cầu AJAX thì không chuyển hướng trang
if (isset($_GET['ajax'])) {
    exit; 
}

header("Location: view_cart.php");
exit();