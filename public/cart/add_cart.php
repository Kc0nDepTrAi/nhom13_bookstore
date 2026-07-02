<?php
session_start();
include '../../db/db_connect.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM books WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    $book = mysqli_fetch_assoc($result);

    if ($book) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] += 1;
        } else {
            $_SESSION['cart'][$id] = [
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