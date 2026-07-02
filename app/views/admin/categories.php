<<<<<<< HEAD
=======
// Thanh vien 2: Chinh sua giao dien views
>>>>>>> ffa11817ea7a2017892626d2af89a75d153c614b
<?php $pageTitle = 'Admin - Thể loại'; ?>
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
    .layout{display:grid;grid-template-columns:360px 1fr;gap:28px;align-items:start}
    .card{background:#fff;border-radius:14px;padding:26px;box-shadow:0 3px 12px rgba(0,0,0,.07)}
    .card-title{font-size:17px;font-weight:700;color:#333;margin-bottom:18px}
    .form-group{margin-bottom:16px}
    .form-group label{display:block;font-weight:600;font-size:13px;color:#444;margin-bottom:5px}
    .form-group input{width:100%;padding:9px 12px;border:1.5px solid #ddd;border-radius:7px;font-size:14px;outline:none}
    .form-group input:focus{border-color:#4A708B}
    .btn{padding:10px 20px;border:none;border-radius:8px;cursor:pointer;font-weight:700;font-size:14px;transition:.2s;width:100%}
    .btn-primary{background:#4A708B;color:#fff}.btn-primary:hover{background:#3a607c}
    .btn-primary:disabled{background:#aaa;cursor:not-allowed}

    table{width:100%;border-collapse:collapse}
    th{background:#212529;color:#fff;padding:12px 16px;text-align:center;font-size:14px}
    td{background:#e7f1ff;padding:12px 16px;border-bottom:1px solid #d4e3f5;text-align:center;font-size:14px}
    tr:last-child td{border-bottom:none}
    .btn-edit{background:#17a2b8;color:#fff;padding:5px 12px;border-radius:5px;border:none;cursor:pointer;font-size:13px;margin-right:6px}
    .btn-delete{background:#dc3545;color:#fff;padding:5px 12px;border-radius:5px;border:none;cursor:pointer;font-size:13px}

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
        <a href="<?= BASE_URL ?>/admin/categories" class="active">Thể loại</a>
        <a href="<?= BASE_URL ?>/admin/orders">Đơn hàng</a>
        <a href="<?= BASE_URL ?>/admin/customers">Khách hàng</a>
    </div>
    <a href="<?= BASE_URL ?>/logout"><button class="btn-logout">Đăng xuất</button></a>
</nav>

<!-- ============================================================
     VIEW: admin/categories.php
     MVVM: Vue.js ViewModel gọi /api/categories endpoints
     ============================================================ -->
<div id="catApp" v-cloak>
    <div class="content">
        <h2 class="page-title">Quản lý thể loại</h2>

        <div class="layout">
            <!-- Form thêm/sửa -->
            <div class="card">
                <div class="card-title">{{ form.editId ? '✏️ Sửa thể loại' : '➕ Thêm thể loại mới' }}</div>
                <div v-if="form.error" class="msg-error">{{ form.error }}</div>
                <form @submit.prevent="saveCategory">
                    <div class="form-group">
                        <label>Tên thể loại *</label>
                        <input type="text" v-model="form.name" placeholder="VD: Văn học, Khoa học..." required>
                    </div>
                    <button type="submit" class="btn btn-primary" :disabled="form.saving">
                        {{ form.saving ? 'Đang lưu...' : (form.editId ? '💾 Cập nhật' : '➕ Thêm') }}
                    </button>
                    <div v-if="form.editId" style="margin-top:10px;">
                        <button type="button" class="btn" style="background:#6c757d;color:#fff" @click="cancelEdit">Hủy sửa</button>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div>
                <div v-if="loading" style="text-align:center;padding:40px;color:#888;">Đang tải...</div>
                <table v-else style="border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.06)">
                    <thead>
                        <tr><th>ID</th><th>Tên thể loại</th><th>Số sách</th><th>Thao tác</th></tr>
                    </thead>
                    <tbody>
                        <tr v-if="!categories.length">
                            <td colspan="4" style="padding:30px;background:#fff;">Chưa có thể loại</td>
                        </tr>
                        <tr v-for="c in categories" :key="c.id">
                            <td>{{ c.id }}</td>
                            <td style="text-align:left;font-weight:600;">{{ c.name }}</td>
                            <td>{{ c.book_count }} sách</td>
                            <td>
                                <button class="btn-edit" @click="editCategory(c)">✏️ Sửa</button>
                                <button class="btn-delete" @click="deleteCategory(c)">🗑️ Xóa</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div v-if="toast.show" :class="['toast', toast.type]">{{ toast.msg }}</div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script>
const { createApp, ref, reactive, onMounted } = Vue;

createApp({
    setup() {
        const categories = ref([]);
        const loading    = ref(true);
        const toast      = ref({ show: false, msg: '', type: 'success' });
        const form       = reactive({ name:'', editId:null, error:'', saving:false });

        const fetchCategories = async () => {
            loading.value = true;
            const res  = await fetch('<?= BASE_URL ?>/api/categories');
            const data = await res.json();
            categories.value = data.success ? data.data : [];
            loading.value = false;
        };

        const saveCategory = async () => {
            form.saving = true; form.error = '';
            const isEdit = !!form.editId;
            const url    = isEdit ? `<?= BASE_URL ?>/api/categories/${form.editId}` : '<?= BASE_URL ?>/api/categories';
            const method = isEdit ? 'PUT' : 'POST';
            try {
                const res  = await fetch(url, {
                    method, headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({ name: form.name }),
                });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message, 'success');
                    form.name = ''; form.editId = null;
                    fetchCategories();
                } else {
                    form.error = data.message;
                }
            } catch (e) { form.error = 'Lỗi kết nối'; }
            finally { form.saving = false; }
        };

        const editCategory = (c) => { form.editId = c.id; form.name = c.name; form.error = ''; };
        const cancelEdit   = () => { form.editId = null; form.name = ''; form.error = ''; };

        const deleteCategory = async (c) => {
            if (!confirm(`Xóa thể loại "${c.name}"?`)) return;
            const res  = await fetch(`<?= BASE_URL ?>/api/categories/${c.id}`, { method:'DELETE' });
            const data = await res.json();
            if (data.success) { showToast('Đã xóa thể loại','success'); fetchCategories(); }
            else showToast(data.message,'error');
        };

        const showToast = (msg, type='success') => {
            toast.value = { show:true, msg, type };
            setTimeout(() => { toast.value.show = false; }, 2800);
        };

        onMounted(fetchCategories);
        return { categories, loading, toast, form, saveCategory, editCategory, cancelEdit, deleteCategory };
    }
}).mount('#catApp');
</script>
</body>
</html>
