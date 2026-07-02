<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\UserModel;

/**
 * UserApiController — REST API CRUD người dùng
 *
 * GET    /api/users         → index()   (admin)
 * GET    /api/users/{id}    → show()    (admin hoặc chính user đó)
 * POST   /api/users         → store()   (admin)
 * PUT    /api/users/{id}    → update()  (admin hoặc chính user đó)
 * DELETE /api/users/{id}    → destroy() (admin)
 */
class UserApiController extends Controller
{
    private UserModel $model;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->model = new UserModel();
    }

    public function index(): void
    {
        $this->requireAdmin();
        $users = $this->model->getCustomers();
        $this->success($users);
    }

    public function show(string $id): void
    {
        $this->requireAuth();

        $userId = (int) $id;
        // Chỉ admin hoặc chính user đó được xem
        if (!$this->isAdmin() && (int) ($_SESSION['user_id'] ?? 0) !== $userId) {
            Response::forbidden();
            return;
        }

        $user = $this->model->findByIdSafe($userId);
        if (!$user) {
            $this->notFound("Không tìm thấy người dùng với id = $userId");
            return;
        }
        $this->success($user);
    }

    public function store(): void
    {
        $this->requireAdmin();

        $data     = $this->request->allInput();
        $required = ['username', 'password', 'fullname'];
        foreach ($required as $f) {
            if (empty($data[$f])) {
                $this->error("Thiếu trường bắt buộc: $f");
                return;
            }
        }

        if ($this->model->usernameExists($data['username'])) {
            $this->error('Tên đăng nhập đã được sử dụng', 409);
            return;
        }

        $id = $this->model->register($data);
        $this->success(['id' => $id], 'Tạo người dùng thành công', 201);
    }

    public function update(string $id): void
    {
        $this->requireAuth();

        $userId = (int) $id;
        if (!$this->isAdmin() && (int) ($_SESSION['user_id'] ?? 0) !== $userId) {
            Response::forbidden();
            return;
        }

        if (!$this->model->exists($userId)) {
            $this->notFound("Không tìm thấy người dùng với id = $userId");
            return;
        }

        $data = $this->request->allInput();

        // Cập nhật profile
        $updated = $this->model->updateProfile($userId, $data);

        // Đổi mật khẩu nếu có
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                $this->error('Mật khẩu phải ít nhất 6 ký tự');
                return;
            }
            $this->model->changePassword($userId, $data['password']);
            $updated = true;
        }

        if (!$updated) {
            $this->error('Không có dữ liệu để cập nhật');
            return;
        }

        $this->success(null, 'Cập nhật người dùng thành công');
    }

    public function destroy(string $id): void
    {
        $this->requireAdmin();

        $userId = (int) $id;
        if (!$this->model->exists($userId)) {
            $this->notFound("Không tìm thấy người dùng với id = $userId");
            return;
        }
        $this->model->delete($userId);
        $this->success(null, 'Xóa người dùng thành công');
    }
}
