<?php
namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Models\UserModel;
use App\Models\OrderModel;

/**
 * ProfileController — Trang cá nhân
 * GET /profile     → index()
 * GET /my-orders   → myOrders()
 */
class ProfileController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function index(): void
    {
        $this->requireAuth();
        $userModel = new UserModel();
        $user      = $userModel->findByIdSafe((int) $_SESSION['user_id']);
        $flashSuccess = $this->getFlash('success');
        $flashError   = $this->getFlash('error');
        $this->render('profile.index', compact('user', 'flashSuccess', 'flashError'));
    }

    public function myOrders(): void
    {
        $this->requireAuth();
        $username = $_SESSION['username'];
        $this->render('orders.my_orders', compact('username'));
    }
}
