<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập hệ thống</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5; /* Màu nền xám nhạt */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 350px;
            text-align: center;
        }
        .login-box h2 {
            margin-bottom: 25px;
            color: #4A708B;
        }
        .login-box input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box; /* Giữ input không bị tràn */
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #A8E6CF; /* Màu xanh pastel theo ảnh mẫu */
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-login:hover {
            background-color: #8eddbd;
        }
        /* Dòng đăng ký nhỏ ở dưới cùng */
        .register-link {
            margin-top: 20px;
            font-size: 13px;
            color: #666;
        }
        .register-link a {
            color: #1E51A4;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Đăng nhập</h2>
    <form action="login_process.php" method="POST">
        <input type="text" name="username" placeholder="Tên đăng nhập" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <button type="submit" name="login" class="btn-login">ĐĂNG NHẬP</button>
    </form>

    <div class="register-link">
        Bạn chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
    </div>
    
</div>

</body>
</html>