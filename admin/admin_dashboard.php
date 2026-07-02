<?php
session_start();
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
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin - Shop của tôi</title>
    <style>
        /* ... Giữ nguyên phần CSS của bạn ... */
        body { font-family: Arial, sans-serif; margin: 0; background: #fff; }
        .navbar { background-color: #A8E6CF; padding: 10px 50px; display: flex; justify-content: space-between; align-items: center; }
        .nav-links a { text-decoration: none; color: #555; margin-right: 20px; font-size: 14px; }
        .content { padding: 20px 50px; }
        .title-page { color: #4A708B; margin-bottom: 20px; }
        .btn-gray { background: #6c757d; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; margin-right: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #212529; color: white; padding: 12px; border: 1px solid #ddd; }
        td { background-color: #e7f1ff; padding: 15px; border: 1px solid #ddd; text-align: center; vertical-align: middle; }
        .img-book { width: 90px; height: auto; border: 1px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn-edit { background: #17a2b8; color: white; border:none; padding: 6px 15px; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .btn-delete { background: #dc3545; color: white; text-decoration: none; padding: 6px 15px; border-radius: 4px; font-size: 13px; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); }
        .modal-content { background: white; margin: 5% auto; padding: 25px; width: 450px; border-radius: 12px; position: relative; }
        .close-btn { position: absolute; right: 20px; top: 15px; font-size: 25px; cursor: pointer; }
        .modal-content input, .modal-content textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        
        /* Autocomplete styles */
        .autocomplete-container { position: relative; display: inline-block; }
        .autocomplete-list { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-top: none; max-height: 200px; overflow-y: auto; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .autocomplete-item { padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; }
        .autocomplete-item:hover { background-color: #f8f9fa; }
        .autocomplete-item:last-child { border-bottom: none; }
    </style>
</head>
<body>

<div class="navbar">
    <div class="nav-links">
        <a href="admin_dashboard.php" style="text-decoration: none; color: inherit;">
    <strong style="font-size: 20px;">Shop của tôi</strong>
</a>
        
        <a href="../public/logout.php">Đăng xuất</a>
    </div>
    
    <div class="search">
        <form action="admin_dashboard.php" method="GET" style="display: flex; gap: 5px;">
            <div class="autocomplete-container">
                <input type="text" name="search" id="searchInput" placeholder="Search" 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
                       style="padding: 6px; border-radius: 4px; border: 1px solid #ccc; width: 250px;"
                       autocomplete="off">
                <div id="autocompleteList" class="autocomplete-list"></div>
            </div>
            <button type="submit" style="background: #8eddbd; border: 1px solid #ddd; padding: 6px 15px; border-radius: 4px; cursor: pointer;">
                Search
            </button>
        </form>
    </div>
</div>

<div class="content">
    <h2 class="title-page">Danh sách sản phẩm</h2>
    <div style="margin-bottom: 20px;">
        <a href="categories.php"><button class="btn-gray"> Quản lý thể loại</button></a>
        <a href="add_book.php"><button class="btn-gray">Thêm sản phẩm</button></a>
<a href="manage_customers.php"><button class="btn-gray">Quản lý khách hàng</button></a>
<a href="manage_orders.php"><button class="btn-gray">Quản lý hóa đơn</button></a>
<a href="manage_inventory.php"><button class="btn-gray">Quản lý tồn kho</button></a>
<a href="admin_chat.php"><button class="btn-gray"> Chat với khách hàng</button></a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Mã sách</th>
                <th>Tên sách</th>
                <th>Tác giả</th>
                <th>Giá</th>
                <th>Thể loại</th>
                <th>Hình ảnh</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    $data_json = json_encode($row, JSON_HEX_QUOT | JSON_HEX_APOS);
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td style='text-align:left; font-weight:500; padding-left:20px;'>{$row['title']}</td>
                        <td>{$row['author']}</td>
                        <td>".number_format($row['price'], 0, ',', '.')." VNĐ</td>
                        <td>{$row['category']}</td>
                        <td><img src='../images/{$row['image']}' class='img-book'></td>
                        <td>
                            <button type='button' class='btn-edit' onclick='openEditModal($data_json)'>Edit</button><br>
                            <a href='delete_book.php?id={$row['id']}' class='btn-delete' onclick='return confirm(\"Xóa sách này?\")'>Delete</a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='padding:20px;'>Không tìm thấy sản phẩm nào khớp với từ khóa.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2 style="margin-top:0; color: #4A708B;">Sửa sản phẩm</h2>
        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            Tên sách: <input type="text" name="title" id="edit_title" required>
            Tác giả: <input type="text" name="author" id="edit_author" required>
            Giá: <input type="number" name="price" id="edit_price" required>
            Thể loại: <input type="text" name="category" id="edit_category">
            Mô tả: <textarea name="description" id="edit_description" rows="5"></textarea>
            <button type="submit" name="btn_update" style="background: #17a2b8; color: white; border: none; padding: 12px; width: 100%; border-radius: 5px; cursor: pointer; font-weight: bold;">Lưu thay đổi</button>
        </form>
    </div>
</div>

<script>
function openEditModal(book) {
    document.getElementById('edit_id').value = book.id;
    document.getElementById('edit_title').value = book.title;
    document.getElementById('edit_author').value = book.author;
    document.getElementById('edit_price').value = book.price;
    document.getElementById('edit_category').value = book.category;
    document.getElementById('edit_description').value = book.description;
    document.getElementById('editModal').style.display = 'block';
}
function closeModal() { document.getElementById('editModal').style.display = 'none'; }
window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) { closeModal(); }
}

// Autocomplete functionality
const searchInput = document.getElementById('searchInput');
const autocompleteList = document.getElementById('autocompleteList');
let currentTimeout;

searchInput.addEventListener('input', function() {
    const query = this.value.trim();
    
    // Clear previous timeout
    clearTimeout(currentTimeout);
    
    if (query.length < 2) {
        autocompleteList.innerHTML = '';
        autocompleteList.style.display = 'none';
        return;
    }
    
    // Debounce - wait 300ms after user stops typing
    currentTimeout = setTimeout(() => {
        fetch(`admin_dashboard.php?ajax=1&q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(suggestions => {
                if (suggestions.length > 0) {
                    autocompleteList.innerHTML = suggestions
                        .map(title => `<div class="autocomplete-item" onclick="selectSuggestion('${title.replace(/'/g, "\\'")}')">${title}</div>`)
                        .join('');
                    autocompleteList.style.display = 'block';
                } else {
                    autocompleteList.innerHTML = '<div class="autocomplete-item">Không tìm thấy gợi ý</div>';
                    autocompleteList.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                autocompleteList.style.display = 'none';
            });
    }, 300);
});

// Select suggestion
function selectSuggestion(title) {
    searchInput.value = title;
    autocompleteList.innerHTML = '';
    autocompleteList.style.display = 'none';
    // Submit form automatically
    searchInput.form.submit();
}

// Hide autocomplete when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.autocomplete-container')) {
        autocompleteList.style.display = 'none';
    }
});

// Hide autocomplete when pressing Escape
searchInput.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        autocompleteList.style.display = 'none';
    }
});
</script>

</body>
</html>