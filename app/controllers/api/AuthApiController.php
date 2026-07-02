// Thanh vien 2: Cau hinh controller
<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\UserModel;

/**
 * AuthApiController — REST API cho xác thực
 * POST /api/auth/login
 * POST /api/auth/register
 * POST /api/auth/logout
 * GET  /api/auth/me
 */
class AuthApiController extends Controller
{
    private UserModel $userModel;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->userModel = new UserModel();
    }

    /** POST /api/auth/login */
    public function login(): void
    {
        $username = trim((string) $this->request->input('username', ''));
        $password = (string) $this->request->input('password', '');

        if (!$username || !$password) {
            $this->error('Vui lòng nhập username và password');
            return;
        }

        $user = $this->userModel->verifyLogin($username, $password);
        if (!$user) {
            $this->error('Sai tài khoản hoặc mật khẩu', 401);
            return;
        }

        // Lưu session
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['role']     = $user['role'];

        $this->success([
            'username' => $user['username'],
            'fullname' => $user['fullname'],
            'role'     => $user['role'],
        ], 'Đăng nhập thành công');
    }

    /** POST /api/auth/register */
    public function register(): void
    {
        $data = $this->request->allInput();

        $required = ['username', 'password', 'fullname', 'phone', 'address'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->error("Thiếu trường bắt buộc: $field");
                return;
            }
        }

        if (strlen($data['password']) < 6) {
            $this->error('Mật khẩu phải ít nhất 6 ký tự');
            return;
        }

        if ($this->userModel->usernameExists($data['username'])) {
            $this->error('Tên đăng nhập đã được sử dụng', 409);
            return;
        }

        $id = $this->userModel->register($data);
        $this->success(['id' => $id], 'Đăng ký thành công', 201);
    }

    /** POST /api/auth/logout */
    public function logout(): void
    {
        session_destroy();
        $this->success(null, 'Đăng xuất thành công');
    }

    /** GET /api/auth/me */
    public function me(): void
    {
        if (!$this->isLoggedIn()) {
            Response::unauthorized();
            return;
        }

        $user = $this->userModel->findByIdSafe((int) $_SESSION['user_id']);
        if (!$user) {
            Response::unauthorized('Phiên đăng nhập không hợp lệ');
            return;
        }
        $this->success($user);
    }
}
