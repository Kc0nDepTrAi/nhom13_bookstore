<?php
namespace App\Core;

/**
 * Controller — Lớp cơ sở cho tất cả Controllers
 */
abstract class Controller
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    // ------ JSON helpers (cho API) ------

    protected function json(mixed $data, int $status = 200): void
    {
        Response::json($data, $status);
    }

    protected function success(mixed $data = null, string $message = 'Thành công', int $status = 200): void
    {
        Response::success($data, $message, $status);
    }

    protected function error(string $message, int $status = 400): void
    {
        Response::error($message, $status);
    }

    protected function notFound(string $msg = 'Không tìm thấy'): void
    {
        Response::notFound($msg);
    }

    // ------ HTML helpers (cho Web) ------

    protected function render(string $view, array $data = []): void
    {
        Response::render($view, $data);
    }

    protected function redirect(string $url): void
    {
        Response::redirect($url);
    }

    protected function redirectWithError(string $url, string $error): void
    {
        Response::redirectWithError($url, $error);
    }

    protected function redirectWithSuccess(string $url, string $msg): void
    {
        Response::redirectWithSuccess($url, $msg);
    }

    // ------ Auth helpers ------

    protected function isLoggedIn(): bool
    {
        return !empty($_SESSION['username']);
    }

    protected function isAdmin(): bool
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    /** Yêu cầu đăng nhập; nếu chưa thì redirect hoặc trả 401 */
    protected function requireAuth(): void
    {
        if (!$this->isLoggedIn()) {
            if ($this->request->isMethod('GET')) {
                $this->redirect(BASE_URL . '/login');
            } else {
                Response::unauthorized();
            }
        }
    }

    /** Yêu cầu quyền admin */
    protected function requireAdmin(): void
    {
        if (!$this->isAdmin()) {
            if ($this->request->isMethod('GET')) {
                $this->redirect(BASE_URL . '/login');
            } else {
                Response::forbidden();
            }
        }
    }

    // ------ Flash message helpers ------

    protected function flash(string $type, string $message): void
    {
        $_SESSION["flash_{$type}"] = $message;
    }

    protected function getFlash(string $type): ?string
    {
        $msg = $_SESSION["flash_{$type}"] ?? null;
        unset($_SESSION["flash_{$type}"]);
        return $msg;
    }
}
