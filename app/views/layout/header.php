<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Shop của tôi') ?></title>
    <style>
        :root {
            --primary: #A8E6CF;
            --primary-dark: #78c9a9;
            --accent:  #4A708B;
            --danger:  #dc3545;
            --success: #28a745;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f7f6; min-height: 100vh; }

        /* ---- Navbar ---- */
        .navbar {
            background: var(--primary);
            padding: 10px 50px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 6px rgba(0,0,0,.12);
            position: sticky; top: 0; z-index: 1000;
        }
        .nav-left  { display: flex; align-items: center; gap: 20px; }
        .nav-right { display: flex; align-items: center; gap: 16px; }
        .logo      { font-size: 22px; font-weight: 800; color: #333; text-decoration: none; }

        /* Dropdown */
        .dropdown { position: relative; }
        .dropbtn  { background: none; border: none; font-weight: 600; cursor: pointer; font-size: 14px; color: #333; padding: 8px 12px; border-radius: 6px; }
        .dropbtn:hover { background: rgba(255,255,255,.4); }
        .dropdown-content {
            display: none; position: absolute; top: 100%; left: 0;
            background: #fff; min-width: 200px; border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,.15); overflow: hidden; z-index: 10;
        }
        .dropdown-content a { display: block; padding: 11px 18px; color: #333; text-decoration: none; font-size: 14px; transition: background .15s; border-bottom: 1px solid #f1f1f1; }
        .dropdown-content a:last-child { border-bottom: none; }
        .dropdown-content a:hover { background: #f4f7f6; color: var(--accent); }
        .dropdown:hover .dropdown-content { display: block; }

        /* Search */
        .search-box { display: flex; gap: 6px; }
        .search-box input { padding: 7px 15px; border: 1px solid #ddd; border-radius: 20px; outline: none; width: 200px; font-size: 14px; }
        .btn-search { padding: 7px 15px; border-radius: 20px; border: 1px solid #ddd; background: #fff; cursor: pointer; font-weight: 600; color: #555; }
        .btn-search:hover { background: var(--accent); color: #fff; border-color: var(--accent); }

        /* Buttons */
        .btn { display: inline-block; padding: 8px 16px; border-radius: 20px; text-decoration: none; font-size: 14px; font-weight: 600; cursor: pointer; border: none; transition: .2s; }
        .btn-white  { background: #fff; color: #333; }
        .btn-white:hover { background: #eee; }
        .btn-accent { background: var(--accent); color: #fff; }
        .btn-accent:hover { background: #3a607c; }

        /* Cart */
        .cart-btn { background: #fff; padding: 7px 14px; border-radius: 20px; text-decoration: none; color: #333; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 5px; position: relative; }
        .cart-btn:hover { background: #eee; }
        .cart-badge { background: var(--danger); color: #fff; border-radius: 50%; width: 18px; height: 18px; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; position: absolute; top: -4px; right: -4px; }

        /* Flash messages */
        .flash-msg { padding: 12px 20px; border-radius: 8px; margin: 15px 50px; font-size: 14px; }
        .flash-error   { background: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .flash-success { background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }

        /* Chat float button */
        .chat-float { position: fixed; bottom: 30px; right: 30px; width: 58px; height: 58px; background: linear-gradient(135deg,#4057bd,#28a745); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 22px; cursor: pointer; box-shadow: 0 4px 18px rgba(0,0,0,.3); text-decoration: none; z-index: 9999; transition: .2s; }
        .chat-float:hover { transform: scale(1.1); }
        .chat-badge { position: absolute; top: -3px; right: -3px; background: #f44336; color: #fff; border-radius: 50%; width: 20px; height: 20px; font-size: 11px; font-weight: 700; display: none; align-items: center; justify-content: center; border: 2px solid #fff; }

        [v-cloak] { display: none !important; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-left">
        <a href="<?= BASE_URL ?>/" class="logo">📚 Shop của tôi</a>
        <div class="dropdown">
            <button class="dropbtn">Danh mục ▾</button>
            <div class="dropdown-content">
                <?php foreach ($categories ?? [] as $cat): ?>
                    <a href="<?= BASE_URL ?>/?category_id=<?= $cat['id'] ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="nav-right">
        <form class="search-box" action="<?= BASE_URL ?>/" method="GET">
            <input type="text" name="search" placeholder="Tìm kiếm sách..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit" class="btn-search">🔍</button>
        </form>

        <a href="<?= BASE_URL ?>/cart" class="cart-btn" id="cartNavBtn">
            🛒 Giỏ hàng
            <span class="cart-badge" id="cartCount" style="display:none">0</span>
        </a>

        <?php if (isset($_SESSION['username'])): ?>
            <div class="dropdown">
                <button class="dropbtn">👤 <?= htmlspecialchars($_SESSION['username']) ?> ▾</button>
                <div class="dropdown-content">
                    <a href="<?= BASE_URL ?>/profile">👤 Thông tin cá nhân</a>
                    <a href="<?= BASE_URL ?>/my-orders">📦 Đơn hàng của tôi</a>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/admin" style="color:var(--accent);font-weight:700;">⚙️ Admin Panel</a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/logout" style="color:var(--danger);">🚪 Đăng xuất</a>
                </div>
            </div>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/login"    class="btn btn-white">Đăng nhập</a>
            <a href="<?= BASE_URL ?>/register" class="btn btn-accent">Đăng ký</a>
        <?php endif; ?>
    </div>
</nav>

<?php if (!empty($flashError)): ?>
    <div class="flash-msg flash-error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>
<?php if (!empty($flashSuccess)): ?>
    <div class="flash-msg flash-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>

<script>
// Cập nhật badge giỏ hàng từ API
(function updateCartBadge() {
    fetch('<?= BASE_URL ?>/api/cart')
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const cnt = res.data.count;
                const badge = document.getElementById('cartCount');
                if (badge && cnt > 0) {
                    badge.textContent = cnt > 99 ? '99+' : cnt;
                    badge.style.display = 'flex';
                }
            }
        }).catch(() => {});
})();
</script>
