<?php
session_start();
include '../db/db_connect.php'; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop của tôi - Trang chủ</title>
    <style>
        :root { --main-color: #A8E6CF; --text-color: #4A708B; }
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f7f6; }
        
        /* Navbar */
        .navbar { background-color: var(--main-color); padding: 10px 50px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000; }
        .nav-left { display: flex; align-items: center; gap: 20px; }
        .logo { font-size: 22px; font-weight: bold; color: #333; text-decoration: none; }
        
        /* Dropdown */
        .dropdown { position: relative; display: inline-block; }
        .dropbtn { background: none; border: none; font-weight: 500; cursor: pointer; font-size: 15px; color: #333; padding: 10px; }
        .dropdown-content { display: none; position: absolute; background-color: white; min-width: 180px; box-shadow: 0 8px 16px rgba(0,0,0,0.2); z-index: 1; border-radius: 8px; right: 0; overflow: hidden; }
        .dropdown-content a { color: #333; padding: 12px 16px; text-decoration: none; display: block; font-size: 14px; transition: 0.2s; border-bottom: 1px solid #f1f1f1; }
        .dropdown-content a:hover { background-color: #f8f9fa; color: var(--text-color); }
        .dropdown:hover .dropdown-content { display: block; }

        .nav-right { display: flex; align-items: center; gap: 20px; }
        .search-box { display: flex; gap: 5px; }
        .search-box input { padding: 6px 15px; border: 1px solid #fff; border-radius: 20px; outline: none; width: 200px; }
        .btn-search { padding: 6px 15px; border-radius: 20px; border: none; background: #fff; cursor: pointer; font-weight: bold; color: #555; }
        
        .cart-btn { background: white; padding: 8px 15px; border-radius: 20px; text-decoration: none; color: #333; font-size: 14px; font-weight: bold; display: flex; align-items: center; gap: 5px; }
        .user-menu-btn { background: #fff; border-radius: 20px; padding: 5px 15px; display: flex; align-items: center; gap: 8px; cursor: pointer; border: 1px solid transparent; }

        /* Container & Grid */
        .container { width: 90%; margin: 30px auto; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 25px; }
        .product-card { background: white; border-radius: 12px; padding: 20px; text-align: center; transition: 0.3s; border: 1px solid #eee; display: flex; flex-direction: column; justify-content: space-between; height: 100%; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .product-card img { width: 100%; height: 220px; object-fit: contain; margin-bottom: 15px; }
        .product-card h3 { font-size: 16px; color: #333; margin: 10px 0; height: 40px; overflow: hidden; }
        .price { color: #d9534f; font-weight: bold; font-size: 18px; display: block; margin-bottom: 15px; }
        .stock { color: #28a745; font-size: 14px; display: block; margin-bottom: 15px; }
        .stock.out-of-stock { color: #dc3545; font-weight: bold; }
        
        .add-to-cart-btn { background: #17a2b8; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; width: 100%; font-weight: bold; transition: 0.3s; }
        .add-to-cart-btn:hover { background: #138496; }

        /* Modal */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); }
        .modal-content { background: white; margin: 8% auto; padding: 30px; width: 50%; border-radius: 15px; position: relative; animation: fadeIn 0.4s; }
        @keyframes fadeIn { from {opacity: 0; transform: translateY(-20px);} to {opacity: 1; transform: translateY(0);} }
        .close { position: absolute; right: 20px; top: 15px; font-size: 28px; font-weight: bold; cursor: pointer; color: #aaa; }
        .modal-category { display: inline-block; background: #e7f1ff; color: #007bff; padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: bold; margin-bottom: 15px; }
        #modalDesc { line-height: 1.6; color: #555; white-space: pre-line; max-height: 300px; overflow-y: auto; }

        /* Chat Button */
        .chat-button { position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; background: linear-gradient(135deg, #4057bd 0%, #28a745 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; cursor: pointer; box-shadow: 0 4px 20px rgba(0,0,0,0.3); transition: 0.3s; z-index: 9999; border: none; text-decoration: none; }
        .chat-button:hover { transform: scale(1.1); box-shadow: 0 6px 25px rgba(0,0,0,0.4); }
        .chat-button:active { transform: scale(0.95); }
        .chat-tooltip { position: absolute; bottom: 70px; right: 0; background: #333; color: white; padding: 8px 12px; border-radius: 8px; font-size: 12px; white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
        .chat-tooltip::after { content: ''; position: absolute; top: 100%; right: 20px; border: 5px solid transparent; border-top-color: #333; }
        .chat-button:hover .chat-tooltip { opacity: 1; }
        .unread-count { position: absolute; top: -5px; right: -5px; background: #f44336; color: white; border-radius: 50%; width: 20px; height: 20px; display: none; align-items: center; justify-content: center; font-size: 11px; font-weight: bold; border: 2px solid white; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-left">
        <a href="index.php" class="logo">Shop của tôi</a>
        <div class="dropdown">
            <button class="dropbtn">Danh mục sách ▼</button>
            <div class="dropdown-content">
                <?php
                $sql_cat = "SELECT * FROM categories";
                $res_cat = mysqli_query($conn, $sql_cat);
                while($cat = mysqli_fetch_assoc($res_cat)) {
                    echo "<a href='index.php?cat={$cat['id']}'>{$cat['name']}</a>";
                }
                ?>
            </div>
        </div>
    </div>

    <div class="nav-right">
        <form class="search-box" action="index.php" method="GET">
            <input type="text" name="search" placeholder="Tìm kiếm sách...">
            <button type="submit" class="btn-search">Search</button>
        </form>
        
        <a href="cart/view_cart.php" class="cart-btn">Giỏ hàng 🛒</a>

        <?php if(isset($_SESSION['username'])): ?>
            <div class="dropdown">
                <div class="user-menu-btn">
                    <span>Chào, <strong><?php echo $_SESSION['username']; ?></strong></span>
                    <small>▼</small>
                </div>
                <div class="dropdown-content">
                    <a href="profile.php">👤 Thông tin cá nhân</a>
                    <a href="my_orders.php">📦 Đơn hàng của tôi</a>
                    <a href="logout.php" style="color: #d9534f;">🚪 Đăng xuất</a>
                </div>
            </div>
        <?php else: ?>
            <a href="login.php" style="text-decoration:none; color:#333; font-size:14px; font-weight:bold;">Đăng nhập</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <h2 style="color: var(--text-color); border-bottom: 2px solid var(--main-color); padding-bottom: 10px; margin-bottom: 30px;">Sách mới nhất</h2>
    <div class="product-grid">
        <?php
        $where = "WHERE 1=1";
        if(isset($_GET['search'])) {
            $s = mysqli_real_escape_string($conn, $_GET['search']);
            $where .= " AND title LIKE '%$s%'";
        }
        if(isset($_GET['cat'])) {
            $c = mysqli_real_escape_string($conn, $_GET['cat']);
            $where .= " AND category_id = '$c'"; 
        }

        $sql = "SELECT * FROM books $where";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $title = htmlspecialchars($row['title'], ENT_QUOTES);
                $category = htmlspecialchars($row['category'] ?? 'Sách', ENT_QUOTES);
                $description = htmlspecialchars($row['description'] ?? 'Chưa có mô tả.', ENT_QUOTES);
                
                echo '
                <div class="product-card">
                    <div onclick="openModal(\''.$title.'\', \''.$category.'\', \''.$description.'\')" style="cursor: pointer;">
                        <img src="../images/'.$row['image'].'" alt="book">
                        <h3>'.$row['title'].'</h3>
                        <span class="price">'.number_format($row['price'], 0, ',', '.').' VNĐ</span>
                        <span class="stock'.($row['quantity'] <= 0 ? ' out-of-stock' : '').'">Còn lại: '.$row['quantity'].' sản phẩm</span>
                    </div>
                    
                    <button class="add-to-cart-btn" data-id="'.$row['id'].'" data-quantity="'.$row['quantity'].'">
    Thêm vào giỏ hàng
</button>
                </div>';
            }
        } else {
            echo "<p style='grid-column: 1/-1; text-align: center;'>Không tìm thấy sản phẩm nào.</p>";
        }
        ?>
    </div>
</div>

<div id="detailModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="modalCategory" class="modal-category"></div>
        <h2 id="modalTitle" style="margin-top: 0; color: #333;"></h2>
        <h4 style="border-bottom: 1px solid #eee; padding-bottom: 10px; color: #888;">Mô tả sản phẩm</h4>
        <p id="modalDesc"></p>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Hàm mở Modal
    function openModal(title, category, description) {
        document.getElementById("modalTitle").innerText = title;
        document.getElementById("modalCategory").innerText = category;
        document.getElementById("modalDesc").innerText = description;
        document.getElementById("detailModal").style.display = "block";
    }

    // Hàm đóng Modal
    function closeModal() {
        document.getElementById("detailModal").style.display = "none";
    }

    // Đóng khi click ra ngoài
    window.onclick = function(event) {
        var modal = document.getElementById("detailModal");
        if (event.target == modal) { modal.style.display = "none"; }
    }

    // AJAX Thêm vào giỏ hàng
    $(document).ready(function() {
        $('.add-to-cart-btn').click(function(e) {
    var bookId = $(this).data('id');
    var quantity = $(this).data('quantity'); // 1. Lấy số lượng tồn kho từ nút bấm

    // 2. Kiểm tra: Nếu hết hàng thì báo lỗi và DỪNG LẠI NGAY
    if (quantity <= 0) {
        alert("Xin lỗi, sản phẩm này đã hết hàng!");
        return; // Lệnh này giúp dừng code lại, không gửi yêu cầu mua hàng nữa
    }

    // Nếu còn hàng thì mới chạy code mua hàng bên dưới
    $.ajax({
        url: 'cart/add_cart.php',
        type: 'GET',
        data: { id: bookId, ajax: 1 },
        success: function(response) {
            alert('Đã thêm sản phẩm vào giỏ hàng thành công!');
        },
        error: function() {
            alert('Có lỗi xảy ra, vui lòng thử lại!');
        }
    });
});
    });

    // Kiểm tra tin nhắn chưa đọc
    function checkUnreadMessages() {
        fetch('chat_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=get_unread_count'
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const unreadCount = data.unread_count;
                const badge = document.getElementById('unreadCount');
                
                if (unreadCount > 0) {
                    badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.log('Chat check failed:', error);
        });
    }

    // Kiểm tra tin nhắn chưa đọc mỗi 5 giây
    setInterval(checkUnreadMessages, 5000);

    // Kiểm tra ngay khi load trang
    checkUnreadMessages();
</script>

<!-- Chat Button -->
<a href="chat.php" target="_blank" class="chat-button" id="chatButton">
    💬
    <span class="chat-tooltip">Chat với Admin</span>
    <span class="unread-count" id="unreadCount" style="display: none;">0</span>
</a>
</body>
</html>