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

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý thể loại - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #292929;
            color: white;
            padding: 30px 0;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .nav-links {
            position: fixed;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            padding: 8px 16px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            transition: background 0.3s;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .actions-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #484848;
            color: white;
        }


        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            font-size: 14px;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .close {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 28px;
            cursor: pointer;
            color: #aaa;
        }

        .close:hover {
            color: #000;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-group textarea {
            height: 80px;
            resize: vertical;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #000;
        }

        .stat-label {
            color: #666;
            margin-top: 5px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state img {
            width: 100px;
            opacity: 0.3;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1> Quản lý thể loại sách</h1>
            <div class="nav-links">
                <a href="admin_dashboard.php">Quay lại</a>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                 <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                 <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Thống kê -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo mysqli_num_rows($categories_result); ?></div>
                <div class="stat-label">Tổng thể loại</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php 
                    $total_books = 0;
                    while ($row = mysqli_fetch_assoc($categories_result)) {
                        $total_books += $row['book_count'];
                    }
                    mysqli_data_seek($categories_result, 0);
                    echo $total_books;
                    ?>
                </div>
                <div class="stat-label">Tổng sách</div>
            </div>
        </div>

        <div class="actions-bar">
            <h2>Danh sách thể loại</h2>
            <button class="btn btn-primary" onclick="openModal()"> Thêm thể loại mới</button>
        </div>

        <div class="table-container">
            <?php if (mysqli_num_rows($categories_result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên thể loại</th>
                            <th>Số lượng sách</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($categories_result)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                <td>
                                    <span style="background: #007bff; color: white; padding: 3px 8px; border-radius: 12px; font-size: 12px;">
                                        <?php echo $row['book_count']; ?> sách
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-primary" onclick="editCategory(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')" style="padding: 5px 10px; font-size: 14px; margin-right: 5px;">
                                         Sửa
                                    </button>
                                    <?php if ($row['book_count'] == 0): ?>
                                        <a href="?delete=<?php echo $row['id']; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('Bạn có chắc muốn xóa thể loại này?')"
                                           title="Xóa thể loại">
                                             Xóa
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 12px;">Không thể xóa</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h3> Chưa có thể loại nào</h3>
                    <p>Nhấn nút "Thêm thể loại mới" để bắt đầu!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal thêm thể loại -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h2> Thêm thể loại mới</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="name">Tên thể loại *</label>
                    <input type="text" id="name" name="name" required 
                           placeholder="Ví dụ: Văn học, Kinh tế, Kỹ năng sống...">
                </div>
                
                <div style="text-align: right; margin-top: 30px;">
                    <button type="button" class="btn" onclick="closeModal('addModal')" 
                            style="background: #6c757d; color: white; margin-right: 10px;">
                        Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                         Thêm thể loại
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal sửa thể loại -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h2> Sửa thể loại</h2>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="form-group">
                    <label for="edit_name">Tên thể loại *</label>
                    <input type="text" id="edit_name" name="name" required 
                           placeholder="Ví dụ: Văn học, Kinh tế, Kỹ năng sống...">
                </div>
                
                <div style="text-align: right; margin-top: 30px;">
                    <button type="button" class="btn" onclick="closeModal('editModal')" 
                            style="background: #6c757d; color: white; margin-right: 10px;">
                        Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                         Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.querySelector('#' + modalId + ' form').reset();
        }

        function editCategory(id, name) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('editModal').style.display = 'block';
        }

        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Auto đóng alert sau 5 giây
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>
