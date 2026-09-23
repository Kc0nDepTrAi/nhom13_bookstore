<?php $pageTitle = 'Admin - Quản lý sách'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?></title>
<style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Segoe UI',Arial,sans-serif;background:#f4f7f6}
    .navbar{background:#A8E6CF;padding:10px 40px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:100;box-shadow:0 2px 6px rgba(0,0,0,.12)}
    .navbar .brand{font-size:20px;font-weight:800;color:#333;text-decoration:none}
    .nav-links a{text-decoration:none;color:#555;margin-right:16px;font-size:14px;font-weight:600;padding:6px 12px;border-radius:6px}
    .nav-links a:hover{background:rgba(255,255,255,.5)}
    .nav-links a.active{background:#fff}
    .btn-logout{background:#dc3545;color:#fff;border:none;padding:7px 16px;border-radius:6px;cursor:pointer;font-size:14px;font-weight:600}

    .content{padding:28px 40px}
    .page-title{color:#4A708B;font-size:22px;margin-bottom:22px;border-bottom:2px solid #A8E6CF;padding-bottom:10px}

    .toolbar{display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center}
    .search-wrap{position:relative;flex:1;min-width:220px}
    .search-wrap input{width:100%;padding:9px 14px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;outline:none}
    .search-wrap input:focus{border-color:#4A708B}
    .autocomplete-list{position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #ddd;border-radius:0 0 8px 8px;max-height:200px;overflow-y:auto;z-index:20;box-shadow:0 4px 12px rgba(0,0,0,.1)}
    .autocomplete-item{padding:10px 14px;cursor:pointer;font-size:14px;border-bottom:1px solid #f1f1f1}
    .autocomplete-item:hover{background:#f4f7f6}
    .btn{padding:9px 18px;border:none;border-radius:8px;cursor:pointer;font-weight:700;font-size:14px;transition:.2s}
    .btn-primary{background:#4A708B;color:#fff}.btn-primary:hover{background:#3a607c}
    .btn-success{background:#28a745;color:#fff}.btn-success:hover{background:#1e7e34}
    .btn-edit{background:#17a2b8;color:#fff;padding:5px 12px;border-radius:5px;border:none;cursor:pointer;font-size:13px}
    .btn-delete{background:#dc3545;color:#fff;padding:5px 12px;border-radius:5px;border:none;cursor:pointer;font-size:13px}

    table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.06)}
    th{background:#212529;color:#fff;padding:13px 14px;text-align:center;font-size:14px}
    td{background:#e7f1ff;padding:12px 14px;border-bottom:1px solid #d4e3f5;text-align:center;vertical-align:middle;font-size:14px}
    tr:last-child td{border-bottom:none}
    .book-img{width:70px;height:auto;border-radius:4px}

    /* Modal */
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;display:flex;align-items:center;justify-content:center}
    .modal-box{background:#fff;width:500px;max-width:95%;border-radius:16px;padding:30px;position:relative;max-height:90vh;overflow-y:auto}
    .modal-title{font-size:20px;font-weight:800;color:#4A708B;margin-bottom:20px}
    .modal-close{position:absolute;right:16px;top:12px;font-size:26px;cursor:pointer;background:none;border:none;color:#999}
    .form-group{margin-bottom:16px}
    .form-group label{display:block;font-weight:600;font-size:13px;color:#444;margin-bottom:5px}
    .form-group input,.form-group select,.form-group textarea{width:100%;padding:9px 12px;border:1.5px solid #ddd;border-radius:7px;font-size:14px;outline:none}
    .form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:#4A708B}
    .btn-save{width:100%;padding:12px;background:#4A708B;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer}
    .btn-save:hover{background:#3a607c}
    .btn-save:disabled{background:#aaa;cursor:not-allowed}

    .toast{position:fixed;bottom:26px;left:50%;transform:translateX(-50%);background:#333;color:#fff;padding:11px 26px;border-radius:22px;z-index:9999;font-size:14px}
    .toast.success{background:#28a745}
    .toast.error{background:#dc3545}
    [v-cloak]{display:none!important}
</style>
</head>
<body>

<nav class="navbar">
    <a href="<?= BASE_URL ?>/admin" class="brand">📚 Shop Admin</a>
    <div class="nav-links">
        <a href="<?= BASE_URL ?>/admin" class="active">Sách</a>
        <a href="<?= BASE_URL ?>/admin/categories">Thể loại</a>
        <a href="<?= BASE_URL ?>/admin/orders">Đơn hàng</a>
        <a href="<?= BASE_URL ?>/admin/customers">Khách hàng</a>
        <a href="<?= BASE_URL ?>/public/admin_chat.php">Chat</a>
    </div>
    <a href="<?= BASE_URL ?>/logout"><button class="btn-logout">Đăng xuất</button></a>
</nav>

<!-- ============================================================
     VIEW: admin/dashboard.php
     MVVM: Vue.js ViewModel gọi /api/books + /api/categories
     ============================================================ -->
<div id="adminApp" v-cloak>
    <div class="content">
        <h2 class="page-title">Danh sách sách</h2>

        <div class="toolbar">
            <!-- Autocomplete search -->
            <div class="search-wrap">
                <input
                    type="text" v-model="searchQ"
                    placeholder="Tìm kiếm sách..."
                    @input="onSearchInput" @keydown.esc="suggestions = []"
                >
                <div v-if="suggestions.length" class="autocomplete-list">
                    <div
                        v-for="s in suggestions" :key="s"
                        class="autocomplete-item"
                        @click="selectSuggestion(s)"
                    >{{ s }}</div>
                </div>
            </div>
            <button class="btn btn-primary" @click="fetchBooks">🔍 Tìm</button>
            <button class="btn btn-success" @click="openAddModal">➕ Thêm sách</button>
        </div>

        <!-- Table -->
        <div v-if="loading" style="text-align:center;padding:40px;color:#888;">Đang tải...</div>
        <table v-else>
            <thead>
                <tr>
                    <th>ID</th><th>Tên sách</th><th>Tác giả</th>
                    <th>Giá</th><th>Thể loại</th><th>Tồn kho</th><th>Ảnh</th><th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="!books.length">
                    <td colspan="8" style="padding:30px;background:#fff;">Không có sách nào</td>
                </tr>
                <tr v-for="b in books" :key="b.id">
                    <td>{{ b.id }}</td>
                    <td style="text-align:left;font-weight:600;max-width:200px;">{{ b.title }}</td>
                    <td>{{ b.author }}</td>
                    <td>{{ fmt(b.price) }} VNĐ</td>
                    <td>{{ b.category_name || b.category }}</td>
                    <td>{{ b.quantity }}</td>
                    <td><img :src="imgUrl(b.image)" class="book-img"></td>
                    <td>
                        <button class="btn-edit" @click="openEditModal(b)" style="margin-right:6px">✏️ Sửa</button>
                        <button class="btn-delete" @click="deleteBook(b)">🗑️ Xóa</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Add / Edit Modal -->
    <div v-if="modal.show" class="modal-overlay" @click.self="modal.show = false">
        <div class="modal-box">
            <button class="modal-close" @click="modal.show = false">&times;</button>
            <div class="modal-title">{{ modal.mode === 'add' ? '➕ Thêm sách mới' : '✏️ Sửa sách' }}</div>

            <div v-if="modal.error" style="background:#f8d7da;color:#842029;padding:10px;border-radius:8px;margin-bottom:14px;font-size:13px;">{{ modal.error }}</div>

            <form @submit.prevent="saveBook">
                <div class="form-group">
                    <label>Tên sách *</label>
                    <input type="text" v-model="modal.form.title" required>
                </div>
                <div class="form-group">
                    <label>Tác giả *</label>
                    <input type="text" v-model="modal.form.author" required>
                </div>
                <div class="form-group">
                    <label>Giá (VNĐ) *</label>
                    <input type="number" v-model="modal.form.price" min="0" required>
                </div>
                <div class="form-group">
                    <label>Thể loại *</label>
                    <select v-model="modal.form.category_id" required>
                        <option value="">-- Chọn thể loại --</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Số lượng</label>
                    <input type="number" v-model="modal.form.quantity" min="0">
                </div>
                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea v-model="modal.form.description" rows="4"></textarea>
                </div>
                <div v-if="modal.mode === 'add'" class="form-group">
                    <label>Tải ảnh lên</label>
                    <input type="file" accept="image/*" @change="onFileChange">
                    <div v-if="modal.form.image" style="margin-top:8px;font-size:13px;color:#28a745;">✅ {{ modal.form.image }}</div>
                </div>
                <button type="submit" class="btn-save" :disabled="modal.saving">
                    {{ modal.saving ? 'Đang lưu...' : '💾 Lưu' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Toast -->
    <div v-if="toast.show" :class="['toast', toast.type]">{{ toast.msg }}</div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script>
const { createApp, ref, reactive, onMounted } = Vue;

createApp({
    setup() {
        const books      = ref([]);
        const categories = ref([]);
        const loading    = ref(true);
        const searchQ    = ref('');
        const suggestions = ref([]);
        let   sugTimeout  = null;
        const toast = ref({ show: false, msg: '', type: 'success' });

        const modal = reactive({
            show: false, mode: 'add', saving: false, error: '',
            bookId: null,
            form: { title:'', author:'', price:'', category_id:'', quantity:0, description:'', image:'' }
        });

        const resetForm = () => {
            Object.assign(modal.form, { title:'', author:'', price:'', category_id:'', quantity:0, description:'', image:'' });
            modal.error = ''; modal.bookId = null;
        };

        // ─── Fetch ───
        const fetchBooks = async () => {
            loading.value = true;
            const q = searchQ.value.trim();
            const url = q ? `<?= BASE_URL ?>/api/books?search=${encodeURIComponent(q)}` : '<?= BASE_URL ?>/api/books';
            const res  = await fetch(url);
            const data = await res.json();
            books.value   = data.success ? data.data : [];
            loading.value = false;
        };

        const fetchCategories = async () => {
            const res  = await fetch('<?= BASE_URL ?>/api/categories');
            const data = await res.json();
            categories.value = data.success ? data.data : [];
        };

        // ─── Autocomplete ───
        const onSearchInput = () => {
            clearTimeout(sugTimeout);
            const q = searchQ.value.trim();
            if (q.length < 2) { suggestions.value = []; return; }
            sugTimeout = setTimeout(async () => {
                const res  = await fetch(`<?= BASE_URL ?>/api/books/autocomplete?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                suggestions.value = data.success ? data.data : [];
            }, 300);
        };

        const selectSuggestion = (s) => {
            searchQ.value     = s;
            suggestions.value = [];
            fetchBooks();
        };

        // ─── CRUD ───
        const openAddModal = () => {
            resetForm(); modal.mode = 'add'; modal.show = true;
        };

        const openEditModal = (b) => {
            resetForm();
            modal.mode = 'edit'; modal.bookId = b.id;
            Object.assign(modal.form, {
                title: b.title, author: b.author, price: b.price,
                category_id: b.category_id, quantity: b.quantity, description: b.description, image: b.image
            });
            modal.show = true;
        };

        const onFileChange = async (e) => {
            const file = e.target.files[0];
            if (!file) return;
            const fd = new FormData();
            fd.append('image', file);
            const res  = await fetch('<?= BASE_URL ?>/api/upload-image', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) modal.form.image = data.data.filename;
            else showToast('Upload ảnh thất bại', 'error');
        };

        const saveBook = async () => {
            modal.saving = true; modal.error = '';
            const isAdd = modal.mode === 'add';
            const url    = isAdd ? '<?= BASE_URL ?>/api/books' : `<?= BASE_URL ?>/api/books/${modal.bookId}`;
            const method = isAdd ? 'POST' : 'PUT';

            try {
                const res  = await fetch(url, {
                    method, headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(modal.form),
                });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message, 'success');
                    modal.show = false;
                    fetchBooks();
                } else {
                    modal.error = data.message;
                }
            } catch (e) {
                modal.error = 'Lỗi kết nối';
            } finally {
                modal.saving = false;
            }
        };

        const deleteBook = async (b) => {
            if (!confirm(`Xóa sách "${b.title}"?`)) return;
            const res  = await fetch(`<?= BASE_URL ?>/api/books/${b.id}`, { method: 'DELETE' });
            const data = await res.json();
            if (data.success) { showToast('Đã xóa sách', 'success'); fetchBooks(); }
            else showToast(data.message, 'error');
        };

        const showToast = (msg, type = 'success') => {
            toast.value = { show: true, msg, type };
            setTimeout(() => { toast.value.show = false; }, 2800);
        };

        const fmt    = (n)   => Number(n).toLocaleString('vi-VN');
        const imgUrl = (img) => img ? '<?= IMAGES_URL ?>/' + img : '<?= BASE_URL ?>/images/no-image.png';

        onMounted(() => { fetchBooks(); fetchCategories(); });

        return { books, categories, loading, searchQ, suggestions, modal, toast,
                 fetchBooks, onSearchInput, selectSuggestion, openAddModal, openEditModal, onFileChange, saveBook, deleteBook, fmt, imgUrl };
    }
}).mount('#adminApp');
</script>
</body>
</html>
