<?php
namespace App\Core;

/**
 * Response — Gửi HTTP response (JSON hoặc HTML)
 */
class Response
{
    // ----------------------------------------------------------
    //  JSON responses (dùng cho REST API)
    // ----------------------------------------------------------

    /** Gửi JSON thô */
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }

    /** Gửi JSON thành công chuẩn */
    public static function success(mixed $data = null, string $message = 'Thành công', int $status = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /** Gửi JSON lỗi chuẩn */
    public static function error(string $message, int $status = 400, mixed $errors = null): void
    {
        $payload = ['success' => false, 'message' => $message];
        if ($errors !== null) $payload['errors'] = $errors;
        self::json($payload, $status);
    }

    public static function notFound(string $msg = 'Không tìm thấy'): void
    {
        self::error($msg, 404);
    }

    public static function unauthorized(string $msg = 'Chưa đăng nhập'): void
    {
        self::error($msg, 401);
    }

    public static function forbidden(string $msg = 'Không có quyền truy cập'): void
    {
        self::error($msg, 403);
    }

    public static function methodNotAllowed(): void
    {
        self::error('Phương thức không được hỗ trợ', 405);
    }

    // ----------------------------------------------------------
    //  HTML / Redirect responses (dùng cho Web controllers)
    // ----------------------------------------------------------

    /** Render PHP view template */
    public static function render(string $view, array $data = []): void
    {
        // Đưa biến vào scope
        extract($data, EXTR_SKIP);

        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            die("View không tồn tại: $view ($viewFile)");
        }
        require $viewFile;
        exit();
    }

    /** Redirect đến URL khác */
    public static function redirect(string $url): void
    {
        header("Location: $url");
        exit();
    }

    /** Redirect với thông báo lỗi trong session */
    public static function redirectWithError(string $url, string $error): void
    {
        $_SESSION['flash_error'] = $error;
        self::redirect($url);
    }

    /** Redirect với thông báo thành công trong session */
    public static function redirectWithSuccess(string $url, string $message): void
    {
        $_SESSION['flash_success'] = $message;
        self::redirect($url);
    }
}
