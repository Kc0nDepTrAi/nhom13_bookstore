<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('bookstore_sess');
    session_start();
}
// Chặn nếu không phải admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}
include '../db/db_connect.php'; 

// 1. XỬ LÝ CẬP NHẬT
if (isset($_POST['btn_update'])) {
    $id = $_POST['id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $price = $_POST['price'];
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $sql_update = "UPDATE books SET title='$title', author='$author', price='$price', category='$category', description='$description' WHERE id=$id";
    mysqli_query($conn, $sql_update);
    header("Location: admin_dashboard.php");
    exit();
}

// 2. XỬ LÝ LOGIC TÌM KIẾM
$where = "WHERE 1=1"; // Điều kiện mặc định
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND title LIKE '%$search%'"; // Tìm kiếm theo tên sách
}

// Xử lý AJAX autocomplete
if (isset($_GET['ajax']) && $_GET['ajax'] == '1' && isset($_GET['q'])) {
    $query = mysqli_real_escape_string($conn, $_GET['q']);
    $autocomplete_sql = "SELECT title FROM books WHERE title LIKE '%$query%' LIMIT 10";
    $autocomplete_result = mysqli_query($conn, $autocomplete_sql);
    
    $suggestions = array();
    while ($row = mysqli_fetch_assoc($autocomplete_result)) {
        $suggestions[] = $row['title'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($suggestions);
    exit();
}

$sql = "SELECT * FROM books $where ORDER BY id ASC";
$result = mysqli_query($conn, $sql);
?>
<?php
$stitchPage = 'books';
require dirname(__DIR__, 1) . '/app/views/stitch/render.php';
?>
