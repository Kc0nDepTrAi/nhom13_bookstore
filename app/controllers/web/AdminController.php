<<<<<<< HEAD
=======
// Thanh vien 2: Hoan thanh nhiem vu feature-app
>>>>>>> ffa11817ea7a2017892626d2af89a75d153c614b
<?php
namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;

/**
 * AdminController — Các trang Admin (Web)
 *
 * GET /admin              → dashboard()
 * GET /admin/categories   → categories()
 * GET /admin/orders       → orders()
 * GET /admin/customers    → customers()
 *
 * Mỗi trang render view tương ứng + dùng Vue.js gọi REST API để lấy/cập nhật dữ liệu.
 */
class AdminController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->requireAdmin();
    }

    public function dashboard(): void
    {
        $this->render('admin.dashboard');
    }

    public function categories(): void
    {
        $this->render('admin.categories');
    }

    public function orders(): void
    {
        $this->render('admin.orders');
    }

    public function customers(): void
    {
        $this->render('admin.customers');
    }
}
