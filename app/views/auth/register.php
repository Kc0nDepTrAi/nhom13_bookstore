<?php $pageTitle = 'Đăng ký'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f7f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .card { background: #fff; border-radius: 16px; padding: 36px; width: 420px; box-shadow: 0 8px 30px rgba(0,0,0,.1); }
        .logo { text-align: center; font-size: 26px; font-weight: 800; color: #333; margin-bottom: 4px; }
        .subtitle { text-align: center; color: #888; font-size: 13px; margin-bottom: 24px; }
        h2 { text-align: center; color: #4A708B; margin-bottom: 22px; font-size: 20px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; color: #444; }
        input { width: 100%; padding: 10px 13px; border: 1.5px solid #ddd; border-radius: 8px; font-size: 14px; outline: none; transition: border-color .2s; }
        input:focus { border-color: #4A708B; }
        .err-field { color: #dc3545; font-size: 12px; margin-top: 4px; }
        .btn-submit { width: 100%; padding: 13px; background: #A8E6CF; border: none; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; transition: background .2s; margin-top: 6px; }
        .btn-submit:hover { background: #78c9a9; }
        .btn-submit:disabled { background: #ccc; cursor: not-allowed; }
        .links { text-align: center; margin-top: 16px; font-size: 14px; color: #666; }
        .links a { color: #4A708B; text-decoration: none; font-weight: 600; }
        .error-msg   { background: #f8d7da; color: #842029; border: 1px solid #f5c2c7; padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
        .success-msg { background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }

        .strength-bar { height: 4px; border-radius: 2px; margin-top: 6px; transition: width .3s, background .3s; }
    </style>
</head>
<body>

<!-- ============================================================
     VIEW: auth/register.php
     MVVM: Vue.js ViewModel gọi POST /api/auth/register
     ============================================================ -->
<div id="registerApp">
    <div class="card">
        <div class="logo">📚 Shop</div>
        <div class="subtitle">Tạo tài khoản mới</div>
        <h2>Đăng ký</h2>

        <?php if (!empty($flashError)): ?>
            <div class="error-msg"><?= htmlspecialchars($flashError) ?></div>
        <?php endif; ?>

        <div v-if="serverError" class="error-msg">{{ serverError }}</div>
        <div v-if="successMsg"  class="success-msg">{{ successMsg }}</div>

        <form @submit.prevent="submitRegister" novalidate>
            <div class="form-group">
                <label>Họ và tên *</label>
                <input type="text" v-model="form.fullname" placeholder="Nguyễn Văn A">
                <div class="err-field" v-if="errors.fullname">{{ errors.fullname }}</div>
            </div>

            <div class="form-group">
                <label>Số điện thoại *</label>
                <input type="tel" v-model="form.phone" placeholder="0901234567">
                <div class="err-field" v-if="errors.phone">{{ errors.phone }}</div>
            </div>

            <div class="form-group">
                <label>Địa chỉ *</label>
                <input type="text" v-model="form.address" placeholder="123 Đường ABC, TP.HCM">
                <div class="err-field" v-if="errors.address">{{ errors.address }}</div>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" v-model="form.email" placeholder="email@example.com">
                <div class="err-field" v-if="errors.email">{{ errors.email }}</div>
            </div>

            <div class="form-group">
                <label>Tên đăng nhập *</label>
                <input type="text" v-model="form.username" placeholder="Tối thiểu 4 ký tự" autocomplete="username">
                <div class="err-field" v-if="errors.username">{{ errors.username }}</div>
            </div>

            <div class="form-group">
                <label>Mật khẩu *</label>
                <input type="password" v-model="form.password" placeholder="Tối thiểu 6 ký tự" autocomplete="new-password">
                <div class="strength-bar" :style="{ width: strengthWidth, background: strengthColor }"></div>
                <div class="err-field" v-if="errors.password">{{ errors.password }}</div>
            </div>

            <button type="submit" class="btn-submit" :disabled="loading">
                <span v-if="loading">Đang đăng ký...</span>
                <span v-else>ĐĂNG KÝ NGAY</span>
            </button>
        </form>

        <div class="links">
            Đã có tài khoản? <a href="<?= BASE_URL ?>/login">Đăng nhập</a>
        </div>
    </div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script>
const { createApp, ref, reactive, computed } = Vue;

createApp({
    setup() {
        const form = reactive({
            fullname: '', phone: '', address: '', email: '', username: '', password: ''
        });
        const errors      = reactive({});
        const serverError = ref('');
        const successMsg  = ref('');
        const loading     = ref(false);

        // Password strength (ViewModel logic)
        const strengthWidth = computed(() => {
            const p = form.password;
            if (!p) return '0%';
            let score = 0;
            if (p.length >= 6)  score++;
            if (p.length >= 10) score++;
            if (/[A-Z]/.test(p)) score++;
            if (/[0-9]/.test(p)) score++;
            if (/[^A-Za-z0-9]/.test(p)) score++;
            return (score / 5 * 100) + '%';
        });
        const strengthColor = computed(() => {
            const w = parseFloat(strengthWidth.value);
            if (w <= 20)  return '#dc3545';
            if (w <= 60)  return '#ffc107';
            return '#28a745';
        });

        const validate = () => {
            Object.keys(errors).forEach(k => delete errors[k]);
            if (!form.fullname.trim()) errors.fullname = 'Vui lòng nhập họ và tên';
            if (!form.phone.trim())    errors.phone    = 'Vui lòng nhập số điện thoại';
            if (!form.address.trim())  errors.address  = 'Vui lòng nhập địa chỉ';
            if (form.username.length < 4) errors.username = 'Tên đăng nhập ít nhất 4 ký tự';
            if (form.password.length < 6) errors.password = 'Mật khẩu ít nhất 6 ký tự';
            if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) errors.email = 'Email không hợp lệ';
            return Object.keys(errors).length === 0;
        };

        const submitRegister = async () => {
            if (!validate()) return;
            loading.value     = true;
            serverError.value = '';
            successMsg.value  = '';

            try {
                const res  = await fetch('<?= BASE_URL ?>/api/auth/register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(form),
                });
                const data = await res.json();

                if (data.success) {
                    successMsg.value = 'Đăng ký thành công! Đang chuyển đến trang đăng nhập...';
                    setTimeout(() => { window.location.href = '<?= BASE_URL ?>/login'; }, 1500);
                } else {
                    serverError.value = data.message || 'Đăng ký thất bại';
                }
            } catch (e) {
                serverError.value = 'Lỗi kết nối máy chủ';
            } finally {
                loading.value = false;
            }
        };

        return { form, errors, serverError, successMsg, loading, strengthWidth, strengthColor, submitRegister };
    }
}).mount('#registerApp');
</script>
</body>
</html>
