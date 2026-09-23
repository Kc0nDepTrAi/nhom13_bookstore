<?php
namespace App\Core;

/**
 * Router — Ánh xạ URL → Controller::method
 * Hỗ trợ named params: /books/{id}
 */
class Router
{
    /** @var array<array{method:string, pattern:string, handler:array|callable}> */
    private array $routes = [];

    // ------ Route registration ------

    public function get(string $pattern, array|callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, array|callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function put(string $pattern, array|callable $handler): void
    {
        $this->add('PUT', $pattern, $handler);
    }

    public function delete(string $pattern, array|callable $handler): void
    {
        $this->add('DELETE', $pattern, $handler);
    }

    private function add(string $method, string $pattern, array|callable $handler): void
    {
        $this->routes[] = [
            'method'  => $method,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    // ------ Dispatch ------

    public function dispatch(Request $request): void
    {
        // Xử lý CORS preflight
        if ($request->getMethod() === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        $method  = $request->getMethod();
        $rawUri  = $request->getUri(); // vd: bookstore/api/books/5

        // Cắt base URL prefix (bookstore) để chỉ còn phần path
        $base    = ltrim(BASE_URL, '/');          // "bookstore"
        $pathRaw = $base ? preg_replace('#^' . preg_quote($base, '#') . '/?#', '', $rawUri) : $rawUri;
        $path    = '/' . trim($pathRaw, '/');     // "/api/books/5"

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $regex = $this->compilePattern($route['pattern']);
            if (preg_match($regex, $path, $matches)) {
                // Lấy các named capture groups làm params
                $params = array_filter(
                    $matches,
                    fn($k) => is_string($k),
                    ARRAY_FILTER_USE_KEY
                );

                $this->invoke($route['handler'], $request, array_values($params));
                return;
            }
        }

        // Không tìm thấy route
        $this->handle404();
    }

    // ------ Helpers ------

    /** Chuyển pattern "/books/{id}" → regex */
    private function compilePattern(string $pattern): string
    {
        $regex = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $regex . '/?$#u';
    }

    /** Gọi controller method hoặc closure */
    private function invoke(array|callable $handler, Request $request, array $params): void
    {
        if (is_callable($handler)) {
            $handler($request, ...$params);
            return;
        }

        [$class, $method] = $handler;
        if (!class_exists($class)) {
            http_response_code(500);
            die("Controller class không tồn tại: $class");
        }
        $controller = new $class($request);
        if (!method_exists($controller, $method)) {
            http_response_code(500);
            die("Method không tồn tại: $class::$method");
        }
        $controller->$method(...array_map('urldecode', $params));
    }

    private function handle404(): void
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_contains($uri, '/api/')) {
            Response::notFound('Endpoint không tồn tại');
        } else {
            http_response_code(404);
            echo '<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>404</title><style>body{font-family:Arial,sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;background:#f4f7f6;flex-direction:column}</style></head><body><h1 style="color:#4A708B">404 - Không tìm thấy trang</h1><p style="color:#888;margin-top:12px"><a href="/bookstore/">← Về trang chủ</a></p></body></html>';
            exit();
        }
    }
}
