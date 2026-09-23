<?php $pageTitle = 'Admin - Đơn hàng'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= $pageTitle ?></title>
<style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Segoe UI',Arial,sans-serif;background:#f4f7f6}
    .navbar{background:#A8E6CF;padding:10px 40px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:100;box-shadow:0 2px 6px rgba(0,0,0,.12)}
    .navbar .brand{font-size:20px;font-weight:800;color:#333;text-decoration:none}
    .nav-links a{text-decoration:none;color:#555;margin-right:16px;font-size:14px;font-weight:600;padding:6px 12px;border-radius:6px}
    .nav-links a.active{background:#fff}
    .btn-logout{background:#dc3545;color:#fff;border:none;padding:7px 16px;border-radius:6px;cursor:pointer;font-size:14px;font-weight:600}
    .content{padding:28px 40px}
    .page-title{color:#4A708B;font-size:22px;margin-bottom:22px;border-bottom:2px solid #A8E6CF;padding-bottom:10px}
    .toolbar{display:flex;gap:12px;margin-bottom:18px;flex-wrap:wrap;align-items:center}
    .toolbar input,.toolbar select{padding:8px 12px;border:1.5px solid #ddd;border-radius:7px;font-size:14px;outline:none}
    .toolbar input:focus,.toolbar select:focus{border-color:#4A708B}
    .btn-filter{background:#4A708B;color:#fff;border:none;padding:9px 18px;border-radius:8px;cursor:pointer;font-weight:700;font-size:14px}

    .order-row{background:#fff;border-radius:12px;margin-bottom:14px;padding:18px 22px;box-shadow:0 2px 8px rgba(0,0,0,.06);border-left:4px solid #A8E6CF}
    .order-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;cursor:pointer}
    .order-info{font-size:14px}
    .order-info strong{font-size:16px;color:#333}
    .order-info small{color:#888;margin-left:10px}
    .badge{padding:5px 14px;border-radius:20px;font-size:13px;font-weight:700}
    .b-p{background:#fff3cd;color:#856404}.b-w{background:#e2e3e5;color:#41464b}
    .b-s{background:#cfe2ff;color:#0a58ca}.b-d{background:#d1e7dd;color:#0f5132}.b-c{background:#f8d7da;color:#842029}
    .status-sel{border:1.5px solid #ddd;border-radius:7px;padding:6px 10px;font-size:13px;cursor:pointer;outline:none;background:#fff}
    .btn-status{background:#28a745;color:#fff;border:none;padding:6px 14px;border-radius:6px;cursor:pointer;font-size:13px;margin-left:8px}
    .btn-del{background:#dc3545;color:#fff;border:none;padding:6px 14px;border-radius:6px;cursor:pointer;font-size:13px;margin-left:8px}

    .items-wrap{overflow:hidden;max-height:0;transition:max-height .3s ease}
    .items-wrap.open{max-height:600px}
    .items-table{width:100%;border-collapse:collapse;margin-top:14px;font-size:13px}
    .items-table th{background:#f0f0f0;padding:8px 12px;text-align:left}
    .items-table td{padding:8px 12px;border-top:1px solid #f0f0f0;vertical-align:middle}
    .book-thumb{width:44px;height:58px;object-fit:contain;border-radius:3px}
    .order-total{text-align:right;font-weight:700;color:#d9534f;font-size:15px;margin-top:10px}

    .toast{position:fixed;bottom:26px;left:50%;transform:translateX(-50%);background:#333;color:#fff;padding:11px 26px;border-radius:22px;z-index:9999;font-size:14px}
    .toast.success{background:#28a745}.toast.error{background:#dc3545}
    [v-cloak]{display:none!important}
</style>
</head>
<body>

<nav class="navbar">
    <a href="<?= BASE_URL ?>/admin" class="brand">📚 Shop Admin</a>
    <div class="nav-links">
        <a href="<?= BASE_URL ?>/admin">Sách</a>
        <a href="<?= BASE_URL ?>/admin/categories">Thể loại</a>
        <a href="<?= BASE_URL ?>/admin/orders" class="active">Đơn hàng</a>
        <a href="<?= BASE_URL ?>/admin/customers">Khách hàng</a>
    </div>
    <a href="<?= BASE_URL ?>/logout"><button class="btn-logout">Đăng xuất</button></a>
</nav>

<!-- ============================================================
     VIEW: admin/orders.php
     MVVM: Vue.js ViewModel gọi /api/orders endpoints
     ============================================================ -->
<div id="ordersApp" v-cloak>
    <div class="content">
        <h2 class="page-title">Quản lý đơn hàng</h2>

        <div class="toolbar">
            <input type="text" v-model="filterUser" placeholder="Lọc theo username...">
            <select v-model="filterStatus">
                <option value="">-- Tất cả trạng thái --</option>
                <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
            </select>
            <button class="btn-filter" @click="fetchOrders">🔍 Lọc</button>
        </div>

        <div v-if="loading" style="text-align:center;padding:40px;color:#888;">Đang tải...</div>
        <div v-else-if="!filteredOrders.length" style="text-align:center;padding:40px;color:#999;">Không có đơn hàng</div>

        <div v-for="order in filteredOrders" :key="order.id" class="order-row">
            <!-- Header (click để mở/đóng chi tiết) -->
            <div class="order-head" @click="toggleOrder(order.id)">
                <div class="order-info">
                    <strong>Đơn #{{ order.id }}</strong>
                    <small>{{ order.username }} — {{ order.fullname }}</small>
                    <small>{{ fmtDate(order.created_at) }}</small>
                </div>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                    <span :class="['badge', badgeClass(order.status)]">{{ order.status }}</span>
                    <span style="font-weight:700;color:#d9534f;">{{ fmt(order.total_price) }} VNĐ</span>

                    <!-- Cập nhật trạng thái -->
                    <select class="status-sel" v-model="statusEdit[order.id]" @click.stop>
                        <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                    </select>
                    <button class="btn-status" @click.stop="updateStatus(order)">💾 Lưu</button>
                    <button class="btn-del"    @click.stop="deleteOrder(order)">🗑️</button>
                </div>
            </div>

            <!-- Chi tiết sản phẩm (collapsible) -->
            <div :class="['items-wrap', openOrders.includes(order.id) ? 'open' : '']">
                <table class="items-table">
                    <thead><tr><th>Ảnh</th><th>Tên sách</th><th>Đơn giá</th><th>SL</th><th>Thành tiền</th></tr></thead>
                    <tbody>
                        <tr v-for="item in order.items" :key="item.id">
                            <td><img :src="imgUrl(item.image)" class="book-thumb"></td>
                            <td>{{ item.title }}</td>
                            <td>{{ fmt(item.price) }}</td>
                            <td>{{ item.quantity }}</td>
                            <td style="font-weight:600;color:#d9534f;">{{ fmt(item.price*item.quantity) }} VNĐ</td>
                        </tr>
                    </tbody>
                </table>
                <div class="order-total">Tổng: {{ fmt(order.total_price) }} VNĐ</div>
            </div>
        </div>
    </div>

    <div v-if="toast.show" :class="['toast', toast.type]">{{ toast.msg }}</div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script>
const { createApp, ref, reactive, computed, onMounted } = Vue;

createApp({
    setup() {
        const orders      = ref([]);
        const loading     = ref(true);
        const filterUser  = ref('');
        const filterStatus = ref('');
        const openOrders  = ref([]);
        const statusEdit  = reactive({});
        const toast       = ref({ show:false, msg:'', type:'success' });
        const statuses    = ['Đang xử lý','Chờ xử lý','Đang giao','Đã hoàn thành','Đã hủy'];

        const filteredOrders = computed(() => {
            let list = orders.value;
            if (filterUser.value)   list = list.filter(o => o.username.includes(filterUser.value));
            if (filterStatus.value) list = list.filter(o => o.status === filterStatus.value);
            return list;
        });

        const fetchOrders = async () => {
            loading.value = true;
            const q = filterUser.value.trim();
            const url = q ? `<?= BASE_URL ?>/api/orders?username=${encodeURIComponent(q)}` : '<?= BASE_URL ?>/api/orders';
            const res  = await fetch(url);
            const data = await res.json();
            orders.value = data.success ? data.data : [];
            // Init statusEdit
            orders.value.forEach(o => { if (!statusEdit[o.id]) statusEdit[o.id] = o.status; });
            loading.value = false;
        };

        const toggleOrder = (id) => {
            const idx = openOrders.value.indexOf(id);
            if (idx >= 0) openOrders.value.splice(idx, 1);
            else openOrders.value.push(id);
        };

        const updateStatus = async (order) => {
            const res  = await fetch(`<?= BASE_URL ?>/api/orders/${order.id}`, {
                method:'PUT', headers:{'Content-Type':'application/json'},
                body: JSON.stringify({ status: statusEdit[order.id] }),
            });
            const data = await res.json();
            if (data.success) { showToast('Cập nhật trạng thái thành công','success'); order.status = statusEdit[order.id]; }
            else showToast(data.message,'error');
        };

        const deleteOrder = async (order) => {
            if (!confirm(`Xóa đơn hàng #${order.id}?`)) return;
            const res  = await fetch(`<?= BASE_URL ?>/api/orders/${order.id}`, { method:'DELETE' });
            const data = await res.json();
            if (data.success) { showToast('Đã xóa đơn hàng','success'); fetchOrders(); }
            else showToast(data.message,'error');
        };

        const badgeClass = (s) => ({ 'Đang xử lý':'b-p','Chờ xử lý':'b-w','Đang giao':'b-s','Đã hoàn thành':'b-d','Đã hủy':'b-c' }[s]||'b-w');
        const showToast  = (msg, type='success') => { toast.value={show:true,msg,type}; setTimeout(()=>{toast.value.show=false;},2800); };
        const fmt        = (n)   => Number(n).toLocaleString('vi-VN');
        const imgUrl     = (img) => img ? '<?= IMAGES_URL ?>/'+img : '<?= BASE_URL ?>/images/no-image.png';
        const fmtDate    = (d)   => new Date(d).toLocaleString('vi-VN');

        onMounted(fetchOrders);
        return { orders, loading, filterUser, filterStatus, filteredOrders, openOrders, statusEdit, toast, statuses, fetchOrders, toggleOrder, updateStatus, deleteOrder, badgeClass, fmt, imgUrl, fmtDate };
    }
}).mount('#ordersApp');
</script>
</body>
</html>
