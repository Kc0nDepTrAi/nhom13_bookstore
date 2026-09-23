<?php $pageTitle = 'Đăng nhập'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f7f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; border-radius: 16px; padding: 40px 36px; width: 380px; box-shadow: 0 8px 30px rgba(0,0,0,.1); }
        .logo { text-align: center; font-size: 28px; font-weight: 800; color: #333; margin-bottom: 6px; }
        .subtitle { text-align: center; color: #888; font-size: 14px; margin-bottom: 28px; }
        h2 { text-align: center; color: #4A708B; margin-bottom: 24px; font-size: 20px; }

        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #444; }
        input { width: 100%; padding: 11px 14px; border: 1.5px solid #ddd; border-radius: 8px; font-size: 15px; outline: none; transition: border-color .2s; }
        input:focus { border-color: #4A708B; }

        .btn-submit { width: 100%; padding: 13px; background: #A8E6CF; border: none; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; transition: background .2s; }
        .btn-submit:hover { background: #78c9a9; }
        .btn-submit:disabled { background: #ccc; cursor: not-allowed; }

        .links { text-align: center; margin-top: 18px; font-size: 14px; color: #666; }
        .links a { color: #4A708B; text-decoration: none; font-weight: 600; }
        .links a:hover { text-decoration: underline; }

        .error-msg { background: #f8d7da; color: #842029; border: 1px solid #f5c2c7; padding: 10px 14px; border-radius: 8px; margin-bottom: 18px; font-size: 14px; }
        .success-msg { background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; padding: 10px 14px; border-radius: 8px; margin-bottom: 18px; font-size: 14px; }

        .err-field { color: var(--danger, #dc3545); font-size: 12px; margin-top: 4px; }
    </style>
</head>
<body>

<!-- ============================================================
     VIEW: auth/login.php
     MVVM: Vue.js ViewModel xử lý form + gọi POST /api/auth/login
     ============================================================ -->
<div id="loginApp">
    <div class="card">
        <div class="logo">📚 Shop</div>
        <div class="subtitle">Nhà sách trực tuyến của bạn</div>
        <h2>Đăng nhập</h2>

        <?php if (!empty($flashError)): ?>
            <div class="error-msg"><?= htmlspecialchars($flashError) ?></div>
        <?php endif; ?>
        <?php if (!empty($flashSuccess ?? '')): ?>
            <div class="success-msg"><?= htmlspecialchars($flashSuccess ?? '') ?></div>
        <?php endif; ?>

        <div v-if="serverError" class="error-msg">{{ serverError }}</div>

        <form @submit.prevent="submitLogin" novalidate>
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input
                    type="text"
                    v-model="form.username"
                    placeholder="Nhập tên đăng nhập"
                    autocomplete="username"
                    :class="{ 'border-red': errors.username }"
                >
                <div class="err-field" v-if="errors.username">{{ errors.username }}</div>
            </div>

            <div class="form-group">
                <label>Mật khẩu</label>
                <input
                    type="password"
                    v-model="form.password"
                    placeholder="Nhập mật khẩu"
                    autocomplete="current-password"
                    :class="{ 'border-red': errors.password }"
                >
                <div class="err-field" v-if="errors.password">{{ errors.password }}</div>
            </div>

            <button type="submit" class="btn-submit" :disabled="loading">
                <span v-if="loading">Đang đăng nhập...</span>
                <span v-else>ĐĂNG NHẬP</span>
            </button>
        </form>

        <div class="links">
            Chưa có tài khoản? <a href="<?= BASE_URL ?>/register">Đăng ký ngay</a>
        </div>
    </div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script>
const { createApp, ref, reactive } = Vue;

createApp({
    setup() {
        // ─── ViewModel ───
        const form = reactive({ username: '', password: '' });
        const errors = reactive({ username: '', password: '' });
        const serverError = ref('');
        const loading = ref(false);

        const validate = () => {
            errors.username = form.username.trim() ? '' : 'Vui lòng nhập tên đăng nhập';
            errors.password = form.password        ? '' : 'Vui lòng nhập mật khẩu';
            return !errors.username && !errors.password;
        };

        const submitLogin = async () => {
            if (!validate()) return;
            loading.value     = true;
            serverError.value = '';

            try {
                const res  = await fetch('<?= BASE_URL ?>/api/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: form.username, password: form.password }),
                });
                const data = await res.json();

                if (data.success) {
                    // Redirect theo role
                    if (data.data.role === 'admin') {
                        window.location.href = '<?= BASE_URL ?>/admin';
                    } else {
                        window.location.href = '<?= BASE_URL ?>/';
                    }
                } else {
                    serverError.value = data.message || 'Đăng nhập thất bại';
                }
            } catch (e) {
                serverError.value = 'Lỗi kết nối máy chủ';
            } finally {
                loading.value = false;
            }
        };

        return { form, errors, serverError, loading, submitLogin };
    }
}).mount('#loginApp');
</script>
</body>
</html>
