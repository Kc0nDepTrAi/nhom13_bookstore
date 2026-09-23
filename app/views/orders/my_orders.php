<?php
$pageTitle  = 'Đơn hàng của tôi';
$categories = (new \App\Models\CategoryModel())->findAll([], 'id ASC');
require VIEWS_PATH . '/layout/header.php';
?>

<style>
    .container   { width: 92%; max-width: 960px; margin: 30px auto; }
    .page-title  { color: #4A708B; font-size: 22px; border-bottom: 2px solid #A8E6CF; padding-bottom: 10px; margin-bottom: 28px; }
    .order-card  { background: #fff; border-radius: 14px; padding: 22px; margin-bottom: 20px; box-shadow: 0 3px 12px rgba(0,0,0,.07); border-left: 4px solid #A8E6CF; }
    .order-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }
    .order-id    { font-size: 15px; font-weight: 700; color: #333; }
    .order-date  { color: #888; font-size: 13px; }
    .badge {
        padding: 5px 14px; border-radius: 20px; font-size: 13px; font-weight: 700;
    }
    .badge-processing { background: #fff3cd; color: #856404; }
    .badge-shipping   { background: #cfe2ff; color: #0a58ca; }
    .badge-done       { background: #d1e7dd; color: #0f5132; }
    .badge-canceled   { background: #f8d7da; color: #842029; }
    .badge-waiting    { background: #e2e3e5; color: #41464b; }

    .items-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .items-table th { background: #f8f9fa; padding: 8px 12px; text-align: left; color: #555; font-weight: 600; }
    .items-table td { padding: 10px 12px; border-top: 1px solid #f0f0f0; vertical-align: middle; }
    .book-thumb { width: 50px; height: 65px; object-fit: contain; border-radius: 4px; }
    .order-total { text-align: right; margin-top: 14px; font-size: 16px; font-weight: 700; color: #d9534f; }

    .empty-state { text-align: center; padding: 60px; color: #999; }
    .empty-icon  { font-size: 54px; margin-bottom: 14px; }
    .loading-state { text-align: center; padding: 40px; color: #888; }
</style>

<!-- ============================================================
     VIEW: orders/my_orders.php
     MVVM: Vue.js ViewModel gọi GET /api/orders?username=...
     ============================================================ -->
<div id="myOrdersApp" v-cloak>
    <div class="container">
        <h2 class="page-title">📦 Đơn hàng của tôi</h2>

        <div v-if="loading" class="loading-state">Đang tải đơn hàng...</div>

        <div v-else-if="!orders.length" class="empty-state">
            <div class="empty-icon">📭</div>
            <p style="font-size:18px;margin-bottom:14px;">Bạn chưa có đơn hàng nào</p>
            <a href="<?= BASE_URL ?>/" style="color:#4A708B;font-weight:700;">→ Mua sắm ngay</a>
        </div>

        <div v-else>
            <div v-for="order in orders" :key="order.id" class="order-card">
                <div class="order-header">
                    <div>
                        <div class="order-id">Đơn hàng #{{ order.id }}</div>
                        <div class="order-date">{{ fmtDate(order.created_at) }}</div>
                    </div>
                    <span :class="['badge', statusClass(order.status)]">{{ order.status }}</span>
                </div>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Tên sách</th>
                            <th style="text-align:right;">Đơn giá</th>
                            <th style="text-align:center;">SL</th>
                            <th style="text-align:right;">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in order.items" :key="item.id">
                            <td><img :src="imgUrl(item.image)" class="book-thumb" :alt="item.title"></td>
                            <td>{{ item.title }}</td>
                            <td style="text-align:right;">{{ fmt(item.price) }}</td>
                            <td style="text-align:center;">{{ item.quantity }}</td>
                            <td style="text-align:right;color:#d9534f;font-weight:600;">{{ fmt(item.price * item.quantity) }} VNĐ</td>
                        </tr>
                    </tbody>
                </table>

                <div class="order-total">Tổng cộng: {{ fmt(order.total_price) }} VNĐ</div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script>
const { createApp, ref, onMounted } = Vue;

createApp({
    setup() {
        const orders  = ref([]);
        const loading = ref(true);
        const username = <?= json_encode($username) ?>;

        const fetchOrders = async () => {
            try {
                const res  = await fetch(`<?= BASE_URL ?>/api/orders?username=${encodeURIComponent(username)}`);
                const data = await res.json();
                orders.value = data.success ? data.data : [];
            } finally {
                loading.value = false;
            }
        };

        const statusClass = (s) => ({
            'Đang xử lý': 'badge-processing',
            'Chờ xử lý':  'badge-waiting',
            'Đang giao':  'badge-shipping',
            'Đã hoàn thành': 'badge-done',
            'Đã hủy':     'badge-canceled',
        }[s] || 'badge-waiting');

        const fmt     = (n)   => Number(n).toLocaleString('vi-VN');
        const imgUrl  = (img) => img ? '<?= IMAGES_URL ?>/' + img : '<?= BASE_URL ?>/images/no-image.png';
        const fmtDate = (d)   => new Date(d).toLocaleString('vi-VN');

        onMounted(fetchOrders);
        return { orders, loading, statusClass, fmt, imgUrl, fmtDate };
    }
}).mount('#myOrdersApp');
</script>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
