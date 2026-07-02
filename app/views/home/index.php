<?php
$pageTitle = 'Shop của tôi - Trang chủ';
require VIEWS_PATH . '/layout/header.php';
?>

<!-- ============================================================
     VIEW: home/index.php
     MVVM: Vue.js app (id="shopApp")
     ViewModel: gọi GET /api/books và POST /api/cart
     ============================================================ -->

<style>
    .container   { width: 92%; max-width: 1400px; margin: 30px auto; }
    .page-title  { color: var(--accent); border-bottom: 2px solid var(--primary); padding-bottom: 10px; margin-bottom: 28px; font-size: 22px; }
    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 22px; }

    .book-card {
        background: #fff; border-radius: 14px; padding: 18px;
        text-align: center; border: 1px solid #eee;
        display: flex; flex-direction: column; justify-content: space-between;
        transition: transform .25s, box-shadow .25s; cursor: pointer;
    }
    .book-card:hover { transform: translateY(-5px); box-shadow: 0 10px 24px rgba(0,0,0,.1); }
    .book-card img   { width: 100%; height: 210px; object-fit: contain; margin-bottom: 12px; border-radius: 6px; }
    .book-title  { font-size: 15px; font-weight: 700; color: #333; height: 44px; overflow: hidden; margin-bottom: 6px; }
    .book-author { font-size: 13px; color: #888; margin-bottom: 8px; }
    .book-price  { color: #d9534f; font-weight: 700; font-size: 18px; margin-bottom: 6px; }
    .book-stock  { font-size: 13px; color: var(--success); margin-bottom: 14px; }
    .book-stock.out { color: var(--danger); font-weight: 600; }

    .btn-add-cart {
        background: var(--accent); color: #fff; border: none; padding: 10px;
        border-radius: 8px; cursor: pointer; width: 100%; font-weight: 700; font-size: 14px; transition: .2s;
    }
    .btn-add-cart:hover    { background: #3a607c; }
    .btn-add-cart:disabled { background: #aaa; cursor: not-allowed; }

    /* Modal chi tiết sách */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.6); z-index: 2000; display: flex; align-items: center; justify-content: center; }
    .modal-box { background: #fff; width: 520px; max-width: 92%; border-radius: 16px; padding: 30px; position: relative; animation: fadeSlide .3s ease; max-height: 90vh; overflow-y: auto; }
    @keyframes fadeSlide { from { opacity:0; transform:translateY(-20px); } to { opacity:1; transform:translateY(0); } }
    .modal-close { position: absolute; right: 18px; top: 14px; font-size: 26px; cursor: pointer; color: #999; background: none; border: none; }
    .modal-badge { display: inline-block; background: #e7f1ff; color: #007bff; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 700; margin-bottom: 12px; }
    .modal-title { font-size: 22px; font-weight: 800; color: #333; margin-bottom: 8px; }
    .modal-author { color: #666; margin-bottom: 12px; font-size: 15px; }
    .modal-desc  { color: #555; line-height: 1.7; white-space: pre-line; font-size: 14px; max-height: 220px; overflow-y: auto; margin-bottom: 16px; }
    .modal-price { color: #d9534f; font-size: 22px; font-weight: 800; }

    /* Toast */
    .toast { position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%); background: #333; color: #fff; padding: 12px 28px; border-radius: 24px; font-size: 15px; z-index: 9999; box-shadow: 0 4px 16px rgba(0,0,0,.3); transition: opacity .4s; }
    .toast.success { background: var(--success); }
    .toast.error   { background: var(--danger); }

    /* Loading skeleton */
    .skeleton { background: linear-gradient(90deg, #eee 25%, #f5f5f5 50%, #eee 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 8px; }
    @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
</style>

<!-- Vue app mount point -->
<div id="shopApp" v-cloak>

    <div class="container">
        <h2 class="page-title">
            {{ filterTitle }}
            <small v-if="books.length" style="font-size:14px;color:#888;font-weight:400;margin-left:10px;">({{ books.length }} sách)</small>
        </h2>

        <!-- Loading state -->
        <div v-if="loading" class="product-grid">
            <div v-for="n in 8" :key="n" class="book-card">
                <div class="skeleton" style="height:210px;margin-bottom:12px;"></div>
                <div class="skeleton" style="height:16px;width:80%;margin:0 auto 8px;"></div>
                <div class="skeleton" style="height:14px;width:60%;margin:0 auto 8px;"></div>
                <div class="skeleton" style="height:38px;margin-top:12px;"></div>
            </div>
        </div>

        <!-- Danh sách sách -->
        <div v-else-if="books.length" class="product-grid">
            <div
                v-for="book in books"
                :key="book.id"
                class="book-card"
            >
                <div @click="openModal(book)">
                    <img :src="imgUrl(book.image)" :alt="book.title" loading="lazy">
                    <div class="book-title">{{ book.title }}</div>
                    <div class="book-author">{{ book.author }}</div>
                    <div class="book-price">{{ formatPrice(book.price) }} VNĐ</div>
                    <div :class="['book-stock', book.quantity <= 0 ? 'out' : '']">
                        {{ book.quantity > 0 ? 'Còn lại: ' + book.quantity + ' cuốn' : 'Hết hàng' }}
                    </div>
                </div>
                <button
                    class="btn-add-cart"
                    :disabled="book.quantity <= 0 || addingId === book.id"
                    @click.stop="addToCart(book)"
                >
                    <span v-if="addingId === book.id">Đang thêm...</span>
                    <span v-else>🛒 Thêm vào giỏ</span>
                </button>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else style="text-align:center;padding:60px;color:#999;font-size:18px;">
            <div style="font-size:48px;margin-bottom:16px;">📭</div>
            Không tìm thấy sản phẩm nào.
        </div>
    </div>

    <!-- Modal chi tiết sách -->
    <div v-if="modal.show" class="modal-overlay" @click.self="modal.show = false">
        <div class="modal-box">
            <button class="modal-close" @click="modal.show = false">&times;</button>
            <div class="modal-badge">{{ modal.book.category_name || modal.book.category }}</div>
            <div class="modal-title">{{ modal.book.title }}</div>
            <div class="modal-author">✍️ {{ modal.book.author }}</div>
            <div class="modal-desc">{{ modal.book.description || 'Chưa có mô tả.' }}</div>
            <div class="modal-price">{{ formatPrice(modal.book.price) }} VNĐ</div>
            <button
                class="btn-add-cart"
                style="margin-top:16px;"
                :disabled="modal.book.quantity <= 0"
                @click="addToCart(modal.book); modal.show = false"
            >
                {{ modal.book.quantity > 0 ? '🛒 Thêm vào giỏ hàng' : 'Hết hàng' }}
            </button>
        </div>
    </div>

    <!-- Toast notification -->
    <div v-if="toast.show" :class="['toast', toast.type]">{{ toast.msg }}</div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script>
const { createApp, ref, computed, onMounted } = Vue;

createApp({
    setup() {
        // ─── ViewModel State ───
        const books    = ref([]);
        const loading  = ref(true);
        const addingId = ref(null);
        const modal    = ref({ show: false, book: {} });
        const toast    = ref({ show: false, msg: '', type: 'success' });

        // Lấy params từ URL (được PHP truyền vào)
        const urlParams = new URLSearchParams(window.location.search);
        const search     = urlParams.get('search')      || '';
        const categoryId = urlParams.get('category_id') || '';

        // ─── Computed ───
        const filterTitle = computed(() => {
            if (search)     return `Kết quả tìm kiếm: "${search}"`;
            if (categoryId) return `Sách theo thể loại`;
            return 'Sách mới nhất';
        });

        // ─── Methods ───
        const fetchBooks = async () => {
            loading.value = true;
            try {
                let url = '<?= BASE_URL ?>/api/books';
                const params = new URLSearchParams();
                if (search)     params.set('search',      search);
                if (categoryId) params.set('category_id', categoryId);
                if (params.toString()) url += '?' + params.toString();

                const res  = await fetch(url);
                const data = await res.json();
                books.value = data.success ? data.data : [];
            } catch (e) {
                showToast('Lỗi kết nối máy chủ', 'error');
            } finally {
                loading.value = false;
            }
        };

        const addToCart = async (book) => {
            addingId.value = book.id;
            try {
                const res  = await fetch('<?= BASE_URL ?>/api/cart', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ book_id: book.id, quantity: 1 }),
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Đã thêm vào giỏ hàng! 🛒', 'success');
                    // Cập nhật badge giỏ hàng
                    const cnt = data.data.count;
                    const badge = document.getElementById('cartCount');
                    if (badge) {
                        badge.textContent = cnt > 99 ? '99+' : cnt;
                        badge.style.display = 'flex';
                    }
                } else {
                    showToast(data.message || 'Thêm thất bại', 'error');
                }
            } catch (e) {
                showToast('Lỗi kết nối', 'error');
            } finally {
                addingId.value = null;
            }
        };

        const openModal = (book) => {
            modal.value = { show: true, book };
        };

        const showToast = (msg, type = 'success', duration = 2800) => {
            toast.value = { show: true, msg, type };
            setTimeout(() => { toast.value.show = false; }, duration);
        };

        const formatPrice = (n) =>
            Number(n).toLocaleString('vi-VN');

        const imgUrl = (img) =>
            img ? '<?= IMAGES_URL ?>/' + img : '<?= BASE_URL ?>/images/no-image.png';

        onMounted(fetchBooks);

        return { books, loading, addingId, modal, toast, filterTitle, fetchBooks, addToCart, openModal, formatPrice, imgUrl };
    }
}).mount('#shopApp');
</script>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
