<?php
$pageTitle  = 'Thông tin cá nhân';
$categories = (new \App\Models\CategoryModel())->findAll([], 'id ASC');
require VIEWS_PATH . '/layout/header.php';
?>

<style>
    .container { width: 92%; max-width: 700px; margin: 36px auto; }
    .card { background: #fff; border-radius: 16px; padding: 36px; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
    .page-title { color: #4A708B; font-size: 22px; margin-bottom: 26px; border-bottom: 2px solid #A8E6CF; padding-bottom: 10px; }
    .form-group { margin-bottom: 18px; }
    label { display: block; font-weight: 600; font-size: 14px; color: #444; margin-bottom: 6px; }
    input { width: 100%; padding: 11px 14px; border: 1.5px solid #ddd; border-radius: 8px; font-size: 15px; outline: none; }
    input:focus { border-color: #4A708B; }
    input[readonly] { background: #f8f9fa; color: #888; cursor: not-allowed; }
    .btn-save { background: #4A708B; color: #fff; border: none; padding: 12px 28px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; }
    .btn-save:hover    { background: #3a607c; }
    .btn-save:disabled { background: #aaa; cursor: not-allowed; }
    .msg-success { background: #d1e7dd; color: #0f5132; padding: 10px 14px; border-radius: 8px; margin-bottom: 18px; font-size: 14px; }
    .msg-error   { background: #f8d7da; color: #842029; padding: 10px 14px; border-radius: 8px; margin-bottom: 18px; font-size: 14px; }
    .section-title { font-size: 16px; font-weight: 700; color: #333; margin: 28px 0 14px; border-top: 1px solid #eee; padding-top: 18px; }
</style>

<!-- ============================================================
     VIEW: profile/index.php
     MVVM: Vue.js ViewModel gọi PUT /api/users/{id}
     ============================================================ -->
<div id="profileApp" v-cloak>
    <div class="container">
        <div class="card">
            <h2 class="page-title">👤 Thông tin cá nhân</h2>

            <div v-if="successMsg" class="msg-success">{{ successMsg }}</div>
            <div v-if="errorMsg"   class="msg-error">{{ errorMsg }}</div>

            <form @submit.prevent="saveProfile" novalidate>
                <div class="form-group">
                    <label>Tên đăng nhập</label>
                    <input type="text" :value="user.username" readonly>
                </div>
                <div class="form-group">
                    <label>Họ và tên</label>
                    <input type="text" v-model="form.fullname" placeholder="Họ và tên">
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="tel" v-model="form.phone" placeholder="Số điện thoại">
                </div>
                <div class="form-group">
                    <label>Địa chỉ</label>
                    <input type="text" v-model="form.address" placeholder="Địa chỉ">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" v-model="form.email" placeholder="email@example.com">
                </div>
                <button type="submit" class="btn-save" :disabled="saving">
                    {{ saving ? 'Đang lưu...' : '💾 Lưu thay đổi' }}
                </button>
            </form>

            <!-- Đổi mật khẩu -->
            <div class="section-title">🔒 Đổi mật khẩu</div>
            <form @submit.prevent="changePassword" novalidate>
                <div class="form-group">
                    <label>Mật khẩu mới (ít nhất 6 ký tự)</label>
                    <input type="password" v-model="newPassword" placeholder="Mật khẩu mới" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label>Xác nhận mật khẩu</label>
                    <input type="password" v-model="confirmPassword" placeholder="Nhập lại mật khẩu mới" autocomplete="new-password">
                </div>
                <div v-if="pwError" class="msg-error" style="margin-bottom:12px;">{{ pwError }}</div>
                <button type="submit" class="btn-save" :disabled="changingPw">
                    {{ changingPw ? 'Đang cập nhật...' : '🔑 Đổi mật khẩu' }}
                </button>
            </form>

            <div style="margin-top:24px;text-align:center;">
                <a href="<?= BASE_URL ?>/my-orders" style="color:#4A708B;font-weight:600;font-size:14px;">📦 Xem đơn hàng của tôi</a>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script>
const { createApp, ref, reactive, onMounted } = Vue;

createApp({
    setup() {
        const user    = ref(<?= json_encode($user ?? [], JSON_UNESCAPED_UNICODE) ?>);
        const form    = reactive({
            fullname: user.value.fullname ?? '',
            phone:    user.value.phone    ?? '',
            address:  user.value.address  ?? '',
            email:    user.value.email    ?? '',
        });

        const successMsg     = ref('');
        const errorMsg       = ref('');
        const saving         = ref(false);
        const newPassword    = ref('');
        const confirmPassword = ref('');
        const pwError        = ref('');
        const changingPw     = ref(false);

        const userId = <?= (int)($_SESSION['user_id'] ?? 0) ?>;

        const saveProfile = async () => {
            saving.value     = true;
            successMsg.value = '';
            errorMsg.value   = '';
            try {
                const res  = await fetch(`<?= BASE_URL ?>/api/users/${userId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(form),
                });
                const data = await res.json();
                if (data.success) {
                    successMsg.value = 'Cập nhật thông tin thành công!';
                } else {
                    errorMsg.value = data.message;
                }
            } catch (e) {
                errorMsg.value = 'Lỗi kết nối máy chủ';
            } finally {
                saving.value = false;
            }
        };

        const changePassword = async () => {
            pwError.value = '';
            if (!newPassword.value || newPassword.value.length < 6) {
                pwError.value = 'Mật khẩu ít nhất 6 ký tự'; return;
            }
            if (newPassword.value !== confirmPassword.value) {
                pwError.value = 'Mật khẩu xác nhận không khớp'; return;
            }
            changingPw.value = true;
            try {
                const res  = await fetch(`<?= BASE_URL ?>/api/users/${userId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ password: newPassword.value }),
                });
                const data = await res.json();
                if (data.success) {
                    successMsg.value  = 'Đổi mật khẩu thành công!';
                    newPassword.value = '';
                    confirmPassword.value = '';
                } else {
                    pwError.value = data.message;
                }
            } catch (e) {
                pwError.value = 'Lỗi kết nối máy chủ';
            } finally {
                changingPw.value = false;
            }
        };

        return { user, form, successMsg, errorMsg, saving, newPassword, confirmPassword, pwError, changingPw, saveProfile, changePassword };
    }
}).mount('#profileApp');
</script>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
