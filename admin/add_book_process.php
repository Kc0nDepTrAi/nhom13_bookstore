<?php
// admin/add_book_process.php
include '../db/db_connect.php';

if (isset($_POST['add_book'])) {
    // 1. Nhận dữ liệu từ form
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $author = $_POST['author'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];

    // Lấy tên thể loại từ ID (Vì bảng books của bạn lưu cả ID lẫn Tên thể loại)
    $sql_get_cat_name = "SELECT name FROM categories WHERE id = '$category_id'";
    $res_cat = mysqli_query($conn, $sql_get_cat_name);
    $row_cat = mysqli_fetch_assoc($res_cat);
    $category_name = $row_cat['name'];

    // 2. Xử lý Upload ảnh
    $target_dir = "../images/"; // Thư mục chứa ảnh
    // Tạo tên file ngẫu nhiên để tránh trùng (thêm thời gian hiện tại vào trước tên file)
    $image_name = time() . "_" . basename($_FILES["image"]["name"]); 
    $target_file = $target_dir . $image_name;
    
    // Di chuyển file từ bộ nhớ tạm vào thư mục images
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        
        // 3. Chèn vào CSDL (Lưu ý: Đã có cột author)
        $sql = "INSERT INTO books (title, price, category, image, category_id, description, author) 
                VALUES ('$title', '$price', '$category_name', '$image_name', '$category_id', '$desc', '$author')";

        if (mysqli_query($conn, $sql)) {
            // Lấy ID vừa tạo để chuyển hướng sang trang chi tiết
            $new_id = mysqli_insert_id($conn);
            // Thành công -> Chuyển sang trang xem chi tiết (Giống ảnh 3)
            header("Location: book_detail.php?id=$new_id"); 
            exit();
        } else {
            echo "Lỗi SQL: " . mysqli_error($conn);
        }

    } else {
        echo "Xin lỗi, đã có lỗi khi upload file ảnh.";
    }
}
?>