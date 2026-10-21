<?php
include '../db/db_connect.php';

// Xử lý thêm thể loại
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    
    if (!empty($name)) {
        // Kiểm tra thể loại đã tồn tại chưa
        $check_sql = "SELECT id FROM categories WHERE name = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $name);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $error = "Thể loại '$name' đã tồn tại!";
        } else {
            $sql = "INSERT INTO categories (name) VALUES (?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $name);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Thêm thể loại thành công!";
            } else {
                $error = "Lỗi khi thêm thể loại: " . mysqli_error($conn);
            }
        }
    } else {
        $error = "Vui lòng nhập tên thể loại!";
    }
}

// Xử lý sửa thể loại
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    
    if (!empty($name)) {
        // Kiểm tra thể loại đã tồn tại chưa (trừ thể loại hiện tại)
        $check_sql = "SELECT id FROM categories WHERE name = ? AND id != ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "si", $name, $id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $error = "Thể loại '$name' đã tồn tại!";
        } else {
            $sql = "UPDATE categories SET name = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $name, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Cập nhật thể loại thành công!";
            } else {
                $error = "Lỗi khi cập nhật thể loại: " . mysqli_error($conn);
            }
        }
    } else {
        $error = "Vui lòng nhập tên thể loại!";
    }
}

// Xử lý xóa thể loại
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $category_id = $_GET['delete'];
    
    // Kiểm tra có sách nào thuộc thể loại này không
    $check_books = "SELECT COUNT(*) as count FROM books WHERE category_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_books);
    mysqli_stmt_bind_param($check_stmt, "i", $category_id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] > 0) {
        $error = "Không thể xóa thể loại này vì có " . $row['count'] . " sách thuộc thể loại này!";
    } else {
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $category_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Xóa thể loại thành công!";
        } else {
            $error = "Lỗi khi xóa thể loại: " . mysqli_error($conn);
        }
    }
}

// Lấy danh sách thể loại
$categories_sql = "SELECT c.*, COUNT(b.id) as book_count 
                   FROM categories c 
                   LEFT JOIN books b ON c.id = b.category_id 
                   GROUP BY c.id 
                   ORDER BY c.name";
$categories_result = mysqli_query($conn, $categories_sql);
?>

<?php
$stitchPage = 'categories';
require dirname(__DIR__, 1) . '/app/views/stitch/render.php';
?>
