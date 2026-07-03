<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\OrderModel;
use App\Models\UserModel;

/**
 * OrderApiController — REST API CRUD đơn hàng
 *
 * GET    /api/orders              → index()   — danh sách (?username= lọc theo user)
 * GET    /api/orders/{id}         → show()    — chi tiết
 * POST   /api/orders              → store()   — tạo đơn hàng
 * PUT    /api/orders/{id}         → update()  — cập nhật trạng thái (admin)
 * DELETE /api/orders/{id}         → destroy() — xóa (admin)
 */
class OrderApiController extends Controller
{
    private OrderModel $model;
    private UserModel  $userModel;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->model     = new OrderModel();
        $this->userModel = new UserModel();
    }

    public function index(): void
    {
        // Admin xem tất cả; user chỉ xem của mình
        if ($this->isAdmin()) {
            $username = (string) $this->request->query('username', '');
        } else {
            $this->requireAuth();
            $username = $_SESSION['username'];
        }

        $orders = $this->model->getAllWithDetails($username);
        $this->success($orders);
    }

    public function show(string $id): void
    {
        $this->requireAuth();

        $order = $this->model->getByIdWithDetails((int) $id);
        if (!$order) {
            $this->notFound("Không tìm thấy đơn hàng với id = $id");
            return;
        }

        // Non-admin chỉ xem đơn của mình
        if (!$this->isAdmin() && $order['username'] !== $_SESSION['username']) {
            Response::forbidden();
            return;
        }
        $this->success($order);
    }

    public function store(): void
    {
        $this->requireAuth();

        $data     = $this->request->allInput();
        $username = (string) ($data['username'] ?? $_SESSION['username'] ?? '');
        $items    = $data['items'] ?? [];

        if (!$username || empty($items) || !is_array($items)) {
            $this->error('Thiếu username hoặc items');
            return;
        }

        // Non-admin chỉ được đặt cho chính mình
        if (!$this->isAdmin() && $username !== $_SESSION['username']) {
            Response::forbidden('Không thể đặt hàng cho người khác');
            return;
        }

        if (!$this->userModel->usernameExists($username)) {
            $this->error('Username không tồn tại');
            return;
        }

        try {
            $result = $this->model->createWithItems($username, $items);
            $this->success($result, 'Đặt hàng thành công', 201);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function update(string $id): void
    {
        $this->requireAdmin();

        $orderId = (int) $id;
        if (!$this->model->exists($orderId)) {
            $this->notFound("Không tìm thấy đơn hàng với id = $orderId");
            return;
        }

        $status = (string) $this->request->input('status', '');
        if (!$status) {
            $this->error('Thiếu trường status');
            return;
        }

        try {
            $this->model->updateStatus($orderId, $status);
            $this->success(null, 'Cập nhật trạng thái thành công');
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage() . '. Hợp lệ: ' . implode(', ', OrderModel::VALID_STATUSES));
        }
    }

    public function destroy(string $id): void
    {
        $this->requireAdmin();

        $orderId = (int) $id;
        if (!$this->model->exists($orderId)) {
            $this->notFound("Không tìm thấy đơn hàng với id = $orderId");
            return;
        }

        try {
            $this->model->deleteWithDetails($orderId);
            $this->success(null, 'Xóa đơn hàng thành công');
        } catch (\Exception $e) {
            $this->error('Xóa thất bại: ' . $e->getMessage(), 500);
        }
    }
}
