<?php
$pageTitle  = 'Giỏ hàng';
$categories = (new \App\Models\CategoryModel())->findAll([], 'id ASC');
require VIEWS_PATH . '/layout/header.php';
?>

<style>
    .container   { width: 92%; max-width: 960px; margin: 30px auto; }
    .page-title  { color: #4A708B; border-bottom: 2px solid #A8E6CF; padding-bottom: 10px; margin-bottom: 28px; }
    .cart-table  { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
    .cart-table th { background: #212529; color: #fff; padding: 14px 16px; text-align: center; font-size: 14px; }
    .cart-table td { padding: 14px 16px; border-bottom: 1px solid #eee; text-align: center; font-size: 14px; vertical-align: middle; }
    .cart-table tr:last-child td { border-bottom: none; }
    .book-img { width: 70px; height: auto; border-radius: 4px; }
    .book-name { text-align: left; font-weight: 600; color: #333; }
    .qty-input { width: 60px; padding: 6px; border: 1.5px solid #ddd; border-radius: 6px; text-align: center; font-size: 14px; }
    .btn-remove { background: #dc3545; color: #fff; border: none; padding: 7px 14px; border-radius: 6px; cursor: pointer; font-size: 13px; }
    .btn-remove:hover { background: #bb2d3b; }
    .total-row { background: #f8f9fa; font-weight: 700; font-size: 16px; }
    .checkout-wrap { text-align: right; margin-top: 20px; }
    .btn-checkout { background: #4A708B; color: #fff; border: none; padding: 14px 36px; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; transition: background .2s; }
    .btn-checkout:hover    { background: #3a607c; }
    .btn-checkout:disabled { background: #aaa; cursor: not-allowed; }
    .empty-cart { text-align: center; padding: 60px; color: #999; }
    .empty-cart .icon { font-size: 56px; margin-bottom: 16px; }
    .toast { position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%); background: #333; color: #fff; padding: 12px 28px; border-radius: 24px; z-index: 9999; }
    .toast.success { background: #28a745; }
    .toast.error   { background: #dc3545; }
</style>

<!-- ============================================================
     VIEW: cart/index.php
     MVVM: Vue.js ViewModel gọi /api/cart endpoints
     ============================================================ -->
<div id="cartApp" v-cloak>
    <div class="container">
        <h2 class="page-title">🛒 Giỏ hàng của bạn</h2>

        <!-- Loading -->
        <div v-if="loading" style="text-align:center;padding:40px;color:#888;">Đang tải giỏ hàng...</div>

        <!-- Giỏ hàng trống -->
        <div v-else-if="!cart.length" class="empty-cart">
            <div class="icon">🛒</div>
            <p style="font-size:18px;margin-bottom:16px;">Giỏ hàng trống</p>
            <a href="<?= BASE_URL ?>/" style="color:#4A708B;font-weight:700;font-size:15px;">← Tiếp tục mua sắm</a>
        </div>

        <!-- Danh sách sản phẩm -->
        <div v-else>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Tên sách</th>
                        <th>Đơn giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                        <th>Xóa</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in cart" :key="item.book_id">
                        <td><img :src="imgUrl(item.image)" class="book-img" :alt="item.title"></td>
                        <td class="book-name">{{ item.title }}<br><small style="color:#888;font-weight:400;">{{ item.author }}</small></td>
                        <td>{{ fmt(item.price) }} VNĐ</td>
                        <td>
                            <input
                                type="number" min="1" max="99"
                                class="qty-input"
                                :value="item.quantity"
                                @change="updateQty(item.book_id, $event.target.value)"
                            >
                        </td>
                        <td style="color:#d9534f;font-weight:700;">{{ fmt(item.price * item.quantity) }} VNĐ</td>
                        <td>
                            <button class="btn-remove" @click="removeItem(item.book_id)">🗑️</button>
                        </td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="4" style="text-align:right;padding-right:20px;">Tổng cộng:</td>
                        <td colspan="2" style="color:#d9534f;font-size:20px;">{{ fmt(total) }} VNĐ</td>
                    </tr>
                </tbody>
            </table>

            <div class="checkout-wrap">
                <a href="<?= BASE_URL ?>/" style="color:#4A708B;font-size:14px;margin-right:20px;">← Tiếp tục mua sắm</a>
                <button class="btn-checkout" :disabled="checkingOut" @click="checkout">
                    <span v-if="checkingOut">Đang xử lý...</span>
                    <span v-else">✅ Đặt hàng ngay</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div v-if="toast.show" :class="['toast', toast.type]">{{ toast.msg }}</div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script>
const { createApp, ref, computed, onMounted } = Vue;

createApp({
    setup() {
        const cart        = ref([]);
        const total       = ref(0);
        const loading     = ref(true);
        const checkingOut = ref(false);
        const toast       = ref({ show: false, msg: '', type: 'success' });

        const isLoggedIn = <?= isset($_SESSION['username']) ? 'true' : 'false' ?>;

        // ─── Fetch cart from API ───
        const fetchCart = async () => {
            try {
                const res  = await fetch('<?= BASE_URL ?>/api/cart');
                const data = await res.json();
                if (data.success) {
                    cart.value  = data.data.items;
                    total.value = data.data.total;
                }
            } finally {
                loading.value = false;
            }
        };

        // ─── Update quantity ───
        const updateQty = async (bookId, qty) => {
            qty = parseInt(qty);
            if (isNaN(qty) || qty < 0) return;

            const res  = await fetch(`<?= BASE_URL ?>/api/cart/${bookId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ quantity: qty }),
            });
            const data = await res.json();
            if (data.success) {
                cart.value  = data.data.items;
                total.value = data.data.total;
            } else {
                showToast(data.message, 'error');
                await fetchCart(); // revert
            }
        };

        // ─── Remove item ───
        const removeItem = async (bookId) => {
            const res  = await fetch(`<?= BASE_URL ?>/api/cart/${bookId}`, { method: 'DELETE' });
            const data = await res.json();
            if (data.success) {
                cart.value  = data.data.items;
                total.value = data.data.total;
                showToast('Đã xóa sản phẩm', 'success');
            }
        };

        // ─── Checkout ───
        const checkout = async () => {
            if (!isLoggedIn) {
                window.location.href = '<?= BASE_URL ?>/login';
                return;
            }
            checkingOut.value = true;
            try {
                const res  = await fetch('<?= BASE_URL ?>/api/cart/checkout', { method: 'POST' });
                const data = await res.json();
                if (data.success) {
                    showToast('Đặt hàng thành công! 🎉', 'success');
                    cart.value  = [];
                    total.value = 0;
                    // Cập nhật badge
                    const badge = document.getElementById('cartCount');
                    if (badge) badge.style.display = 'none';
                    setTimeout(() => { window.location.href = '<?= BASE_URL ?>/my-orders'; }, 1800);
                } else {
                    showToast(data.message || 'Đặt hàng thất bại', 'error');
                }
            } catch (e) {
                showToast('Lỗi kết nối', 'error');
            } finally {
                checkingOut.value = false;
            }
        };

        const showToast = (msg, type = 'success') => {
            toast.value = { show: true, msg, type };
            setTimeout(() => { toast.value.show = false; }, 2800);
        };

        const fmt    = (n) => Number(n).toLocaleString('vi-VN');
        const imgUrl = (img) => img ? '<?= IMAGES_URL ?>/' + img : '<?= BASE_URL ?>/images/no-image.png';

        onMounted(fetchCart);

        return { cart, total, loading, checkingOut, toast, updateQty, removeItem, checkout, fmt, imgUrl };
    }
}).mount('#cartApp');
</script>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
