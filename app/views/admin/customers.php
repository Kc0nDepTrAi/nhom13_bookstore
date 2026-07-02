<?php $pageTitle = 'Admin - Khách hàng'; ?>
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
    .toolbar input{padding:8px 12px;border:1.5px solid #ddd;border-radius:7px;font-size:14px;outline:none;min-width:240px}
    .btn-add{background:#28a745;color:#fff;border:none;padding:9px 18px;border-radius:8px;cursor:pointer;font-weight:700;font-size:14px}

    table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.06)}
    th{background:#212529;color:#fff;padding:12px 14px;text-align:center;font-size:14px}
    td{background:#e7f1ff;padding:11px 14px;border-bottom:1px solid #d4e3f5;text-align:center;font-size:14px}
    tr:last-child td{border-bottom:none}
    .btn-edit{background:#17a2b8;color:#fff;padding:5px 12px;border-radius:5px;border:none;cursor:pointer;font-size:13px;margin-right:5px}
    .btn-delete{background:#dc3545;color:#fff;padding:5px 12px;border-radius:5px;border:none;cursor:pointer;font-size:13px}

    /* Modal */
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;display:flex;align-items:center;justify-content:center}
    .modal-box{background:#fff;width:460px;max-width:95%;border-radius:16px;padding:30px;position:relative;max-height:90vh;overflow-y:auto}
    .modal-title{font-size:19px;font-weight:800;color:#4A708B;margin-bottom:18px}
    .modal-close{position:absolute;right:16px;top:12px;font-size:26px;cursor:pointer;background:none;border:none;color:#999}
    .form-group{margin-bottom:15px}
    .form-group label{display:block;font-weight:600;font-size:13px;color:#444;margin-bottom:5px}
    .form-group input{width:100%;padding:9px 12px;border:1.5px solid #ddd;border-radius:7px;font-size:14px;outline:none}
    .form-group input:focus{border-color:#4A708B}
    .btn-save{width:100%;padding:12px;background:#4A708B;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer}
    .btn-save:hover{background:#3a607c}.btn-save:disabled{background:#aaa;cursor:not-allowed}
    .msg-error{background:#f8d7da;color:#842029;padding:10px;border-radius:8px;margin-bottom:12px;font-size:13px}

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
        <a href="<?= BASE_URL ?>/admin/orders">Đơn hàng</a>
        <a href="<?= BASE_URL ?>/admin/customers" class="active">Khách hàng</a>
    </div>
    <a href="<?= BASE_URL ?>/logout"><button class="btn-logout">Đăng xuất</button></a>
</nav>

<!-- ============================================================
     VIEW: admin/customers.php
     MVVM: Vue.js ViewModel gọi /api/users endpoints
     ============================================================ -->
<div id="customersApp" v-cloak>
    <div class="content">
        <h2 class="page-title">Quản lý khách hàng</h2>

        <div class="toolbar">
            <input type="text" v-model="searchQ" placeholder="Tìm theo tên, username, SĐT...">
            <button class="btn-add" @click="openAddModal">➕ Thêm khách hàng</button>
        </div>

        <div v-if="loading" style="text-align:center;padding:40px;color:#888;">Đang tải...</div>
        <table v-else>
            <thead>
                <tr><th>ID</th><th>Username</th><th>Họ tên</th><th>SĐT</th><th>Địa chỉ</th><th>Email</th><th>Thao tác</th></tr>
            </thead>
            <tbody>
                <tr v-if="!filteredCustomers.length">
                    <td colspan="7" style="padding:30px;background:#fff;">Không có khách hàng</td>
                </tr>
                <tr v-for="c in filteredCustomers" :key="c.id">
                    <td>{{ c.id }}</td>
                    <td style="font-weight:600;">{{ c.username }}</td>
                    <td style="text-align:left;">{{ c.fullname }}</td>
                    <td>{{ c.phone }}</td>
                    <td style="text-align:left;max-width:180px;overflow:hidden;text-overflow:ellipsis;">{{ c.address }}</td>
                    <td>{{ c.email || '—' }}</td>
                    <td>
                        <button class="btn-edit"   @click="openEditModal(c)">✏️</button>
                        <button class="btn-delete" @click="deleteCustomer(c)">🗑️</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div v-if="modal.show" class="modal-overlay" @click.self="modal.show = false">
        <div class="modal-box">
            <button class="modal-close" @click="modal.show = false">&times;</button>
            <div class="modal-title">{{ modal.mode === 'add' ? '➕ Thêm khách hàng' : '✏️ Sửa khách hàng' }}</div>
            <div v-if="modal.error" class="msg-error">{{ modal.error }}</div>
            <form @submit.prevent="saveCustomer">
                <div class="form-group"><label>Username *</label>
                    <input type="text" v-model="modal.form.username" :readonly="modal.mode==='edit'" required></div>
                <div class="form-group"><label>{{ modal.mode==='add' ? 'Mật khẩu *' : 'Mật khẩu mới (để trống = không đổi)' }}</label>
                    <input type="password" v-model="modal.form.password" :required="modal.mode==='add'"></div>
                <div class="form-group"><label>Họ và tên *</label>
                    <input type="text" v-model="modal.form.fullname" required></div>
                <div class="form-group"><label>Số điện thoại</label>
                    <input type="tel" v-model="modal.form.phone"></div>
                <div class="form-group"><label>Địa chỉ</label>
                    <input type="text" v-model="modal.form.address"></div>
                <div class="form-group"><label>Email</label>
                    <input type="email" v-model="modal.form.email"></div>
                <button type="submit" class="btn-save" :disabled="modal.saving">
                    {{ modal.saving ? 'Đang lưu...' : '💾 Lưu' }}
                </button>
            </form>
        </div>
    </div>

    <div v-if="toast.show" :class="['toast', toast.type]">{{ toast.msg }}</div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script>
const { createApp, ref, reactive, computed, onMounted } = Vue;

createApp({
    setup() {
        const customers = ref([]);
        const loading   = ref(true);
        const searchQ   = ref('');
        const toast     = ref({ show:false, msg:'', type:'success' });
        const modal     = reactive({
            show:false, mode:'add', saving:false, error:'', userId:null,
            form:{ username:'', password:'', fullname:'', phone:'', address:'', email:'' }
        });

        const filteredCustomers = computed(() => {
            const q = searchQ.value.toLowerCase();
            if (!q) return customers.value;
            return customers.value.filter(c =>
                (c.username||'').toLowerCase().includes(q) ||
                (c.fullname||'').toLowerCase().includes(q) ||
                (c.phone||'').includes(q)
            );
        });

        const fetchCustomers = async () => {
            loading.value = true;
            const res  = await fetch('<?= BASE_URL ?>/api/users');
            const data = await res.json();
            customers.value = data.success ? data.data : [];
            loading.value   = false;
        };

        const resetForm = () => {
            Object.assign(modal.form, { username:'', password:'', fullname:'', phone:'', address:'', email:'' });
            modal.error = ''; modal.userId = null;
        };

        const openAddModal = () => { resetForm(); modal.mode='add'; modal.show=true; };
        const openEditModal = (c) => {
            resetForm(); modal.mode='edit'; modal.userId=c.id;
            Object.assign(modal.form, { username:c.username, fullname:c.fullname, phone:c.phone||'', address:c.address||'', email:c.email||'' });
            modal.show=true;
        };

        const saveCustomer = async () => {
            modal.saving=true; modal.error='';
            const isAdd = modal.mode==='add';
            const url    = isAdd ? '<?= BASE_URL ?>/api/users' : `<?= BASE_URL ?>/api/users/${modal.userId}`;
            const method = isAdd ? 'POST' : 'PUT';
            const body   = { ...modal.form };
            if (!body.password) delete body.password;
            try {
                const res  = await fetch(url, { method, headers:{'Content-Type':'application/json'}, body:JSON.stringify(body) });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message,'success'); modal.show=false; fetchCustomers();
                } else { modal.error = data.message; }
            } catch(e) { modal.error='Lỗi kết nối'; }
            finally { modal.saving=false; }
        };

        const deleteCustomer = async (c) => {
            if (!confirm(`Xóa khách hàng "${c.username}"?`)) return;
            const res  = await fetch(`<?= BASE_URL ?>/api/users/${c.id}`, { method:'DELETE' });
            const data = await res.json();
            if (data.success) { showToast('Đã xóa khách hàng','success'); fetchCustomers(); }
            else showToast(data.message,'error');
        };

        const showToast = (msg, type='success') => {
            toast.value={show:true,msg,type}; setTimeout(()=>{toast.value.show=false;},2800);
        };

        onMounted(fetchCustomers);
        return { customers, loading, searchQ, filteredCustomers, modal, toast, fetchCustomers, openAddModal, openEditModal, saveCustomer, deleteCustomer };
    }
}).mount('#customersApp');
</script>
</body>
</html>
