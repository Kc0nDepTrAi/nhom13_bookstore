<?php
namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Models\UserModel;

/**
 * AuthController — Đăng nhập / Đăng ký / Đăng xuất (Web)
 *
 * GET  /login    → showLogin()
 * POST /login    → login()
 * GET  /register → showRegister()
 * POST /register → register()
 * GET  /logout   → logout()
 */
class AuthController extends Controller
{
    private UserModel $userModel;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->userModel = new UserModel();
    }

    // GET /login
    public function showLogin(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/');
            return;
        }
        $flashError = $this->getFlash('error');
        $this->render('auth.login', compact('flashError'));
    }

    // POST /login
    public function login(): void
    {
        $username = trim((string) $this->request->input('username', ''));
        $password = (string) $this->request->input('password', '');

        if (!$username || !$password) {
            $this->redirectWithError(BASE_URL . '/login', 'Vui lòng nhập đầy đủ thông tin');
            return;
        }

        $user = $this->userModel->verifyLogin($username, $password);
        if (!$user) {
            $this->redirectWithError(BASE_URL . '/login', 'Sai tài khoản hoặc mật khẩu');
            return;
        }

        $_SESSION['username'] = $user['username'];
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['role']     = $user['role'];

        if ($user['role'] === 'admin') {
            $this->redirect(BASE_URL . '/admin');
        } else {
            $this->redirect(BASE_URL . '/');
        }
    }

    // GET /register
    public function showRegister(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/');
            return;
        }
        $flashError = $this->getFlash('error');
        $this->render('auth.register', compact('flashError'));
    }

    // POST /register
    public function register(): void
    {
        $data = $this->request->allInput();
        $required = ['username', 'password', 'fullname', 'phone', 'address'];

        foreach ($required as $f) {
            if (empty($data[$f])) {
                $this->redirectWithError(BASE_URL . '/register', "Vui lòng nhập $f");
                return;
            }
        }

        if (strlen($data['password']) < 6) {
            $this->redirectWithError(BASE_URL . '/register', 'Mật khẩu phải ít nhất 6 ký tự');
            return;
        }

        if ($this->userModel->usernameExists($data['username'])) {
            $this->redirectWithError(BASE_URL . '/register', 'Tên đăng nhập đã được sử dụng');
            return;
        }

        $this->userModel->register($data);
        $this->redirectWithSuccess(BASE_URL . '/login', 'Đăng ký thành công! Hãy đăng nhập.');
    }

    // GET /logout
    public function logout(): void
    {
        session_destroy();
        $this->redirect(BASE_URL . '/login');
    }
}
