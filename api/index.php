<?php
/**
 * api/index.php — Entry point cho tất cả REST API requests
 *
 * Tất cả request đến /bookstore/api/* đều đi qua đây.
 * Trả về JSON.
 */

// ── CORS Headers ───────────────────────────────────────
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ── Bootstrap ──────────────────────────────────────────
require_once dirname(__DIR__) . '/config/config.php';

spl_autoload_register(function (string $class): void {
    $file = APP_PATH . '/' . str_replace(['App/', '\\'], ['', '/'], $class) . '.php';
    if (file_exists($file)) require $file;
});

use App\Core\Router;
use App\Core\Request;

// ── Khởi tạo ──────────────────────────────────────────
$router  = new Router();
$request = new Request();

// ══════════════════════════════════════════════════════
//  API ROUTES (RESTful)
// ══════════════════════════════════════════════════════

// ---- Auth (/api/auth/*) ----
$router->post('/api/auth/login',    [\App\Controllers\Api\AuthApiController::class, 'login']);
$router->post('/api/auth/register', [\App\Controllers\Api\AuthApiController::class, 'register']);
$router->post('/api/auth/logout',   [\App\Controllers\Api\AuthApiController::class, 'logout']);
$router->get('/api/auth/me',        [\App\Controllers\Api\AuthApiController::class, 'me']);

// ---- Books (/api/books) ----
// Route cụ thể phải đứng trước route có param {id}
$router->get('/api/books',               [\App\Controllers\Api\BookApiController::class, 'index']);
$router->get('/api/books/autocomplete',  [\App\Controllers\Api\BookApiController::class, 'autocomplete']);
$router->get('/api/books/{id}',          [\App\Controllers\Api\BookApiController::class, 'show']);
$router->post('/api/books',              [\App\Controllers\Api\BookApiController::class, 'store']);
$router->put('/api/books/{id}',          [\App\Controllers\Api\BookApiController::class, 'update']);
$router->delete('/api/books/{id}',       [\App\Controllers\Api\BookApiController::class, 'destroy']);

// ---- Categories (/api/categories) ----
$router->get('/api/categories',          [\App\Controllers\Api\CategoryApiController::class, 'index']);
$router->get('/api/categories/{id}',     [\App\Controllers\Api\CategoryApiController::class, 'show']);
$router->post('/api/categories',         [\App\Controllers\Api\CategoryApiController::class, 'store']);
$router->put('/api/categories/{id}',     [\App\Controllers\Api\CategoryApiController::class, 'update']);
$router->delete('/api/categories/{id}',  [\App\Controllers\Api\CategoryApiController::class, 'destroy']);

// ---- Orders (/api/orders) ----
$router->get('/api/orders',              [\App\Controllers\Api\OrderApiController::class, 'index']);
$router->get('/api/orders/{id}',         [\App\Controllers\Api\OrderApiController::class, 'show']);
$router->post('/api/orders',             [\App\Controllers\Api\OrderApiController::class, 'store']);
$router->put('/api/orders/{id}',         [\App\Controllers\Api\OrderApiController::class, 'update']);
$router->delete('/api/orders/{id}',      [\App\Controllers\Api\OrderApiController::class, 'destroy']);

// ---- Users (/api/users) ----
$router->get('/api/users',               [\App\Controllers\Api\UserApiController::class, 'index']);
$router->get('/api/users/{id}',          [\App\Controllers\Api\UserApiController::class, 'show']);
$router->post('/api/users',              [\App\Controllers\Api\UserApiController::class, 'store']);
$router->put('/api/users/{id}',          [\App\Controllers\Api\UserApiController::class, 'update']);
$router->delete('/api/users/{id}',       [\App\Controllers\Api\UserApiController::class, 'destroy']);

// ---- Cart (/api/cart) ----
// QUAN TRỌNG: route cụ thể phải đăng ký TRƯỚC route có param
$router->get('/api/cart',                [\App\Controllers\Api\CartApiController::class, 'index']);
$router->post('/api/cart/checkout',      [\App\Controllers\Api\CartApiController::class, 'checkout']);
$router->delete('/api/cart/clear',       [\App\Controllers\Api\CartApiController::class, 'clear']);
$router->post('/api/cart',               [\App\Controllers\Api\CartApiController::class, 'add']);
$router->put('/api/cart/{book_id}',      [\App\Controllers\Api\CartApiController::class, 'updateItem']);
$router->delete('/api/cart/{book_id}',   [\App\Controllers\Api\CartApiController::class, 'removeItem']);

// ---- Image Upload (/api/upload-image) ----
$router->post('/api/upload-image', function (\App\Core\Request $req): void {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        \App\Core\Response::forbidden();
        return;
    }
    $file = $_FILES['image'] ?? null;
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        \App\Core\Response::error('Không có file hoặc upload lỗi');
        return;
    }

    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) {
        \App\Core\Response::error('Chỉ chấp nhận jpg, png, gif, webp');
        return;
    }

    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
    $dest     = IMAGES_DIR . '/' . $filename;

    if (!is_dir(IMAGES_DIR)) mkdir(IMAGES_DIR, 0755, true);

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        \App\Core\Response::success(['filename' => $filename], 'Upload thành công');
    } else {
        \App\Core\Response::error('Lưu file thất bại', 500);
    }
});

// ── Dispatch ───────────────────────────────────────────
$router->dispatch($request);
