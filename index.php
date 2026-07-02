<?php
/**
 * index.php — Entry point cho toàn bộ WEB requests
 * Tất cả request không phải API đều đi qua đây.
 *
 * URL pattern: /bookstore/<path>
 */

// ── Bootstrap ──────────────────────────────────────────
require_once __DIR__ . '/config/config.php';

// Autoloader đơn giản (không dùng Composer)
spl_autoload_register(function (string $class): void {
    // Chuyển namespace "App\Core\Database" → "app/core/Database.php"
    $file = APP_PATH . '/' . str_replace(['App/', '\\'], ['', '/'], $class) . '.php';
    if (file_exists($file)) require $file;
});

use App\Core\Router;
use App\Core\Request;
use App\Core\Response;

// ── Khởi tạo Router & Request ──────────────────────────
$router  = new Router();
$request = new Request();

// ── Đăng ký Web Routes ─────────────────────────────────
// Cú pháp: $router->METHOD('/path', [ControllerClass::class, 'methodName'])

// ---- Trang chủ ----
$router->get('/',          [\App\Controllers\Web\HomeController::class, 'index']);

// ---- Auth ----
$router->get('/login',     [\App\Controllers\Web\AuthController::class, 'showLogin']);
$router->post('/login',    [\App\Controllers\Web\AuthController::class, 'login']);
$router->get('/register',  [\App\Controllers\Web\AuthController::class, 'showRegister']);
$router->post('/register', [\App\Controllers\Web\AuthController::class, 'register']);
$router->get('/logout',    [\App\Controllers\Web\AuthController::class, 'logout']);

// ---- User pages ----
$router->get('/cart',      [\App\Controllers\Web\CartController::class,    'index']);
$router->get('/profile',   [\App\Controllers\Web\ProfileController::class, 'index']);
$router->get('/my-orders', [\App\Controllers\Web\ProfileController::class, 'myOrders']);

// ---- Admin pages ----
$router->get('/admin',            [\App\Controllers\Web\AdminController::class, 'dashboard']);
$router->get('/admin/categories', [\App\Controllers\Web\AdminController::class, 'categories']);
$router->get('/admin/orders',     [\App\Controllers\Web\AdminController::class, 'orders']);
$router->get('/admin/customers',  [\App\Controllers\Web\AdminController::class, 'customers']);

// ── Dispatch ───────────────────────────────────────────
$router->dispatch($request);
