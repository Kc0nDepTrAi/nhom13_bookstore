<?php
namespace App\Core;

/**
 * Request — Đóng gói HTTP request
 */
class Request
{
    private string $method;
    private string $uri;
    private array  $queryParams;
    private array  $body;
    private array  $files;
    private array  $headers;

    public function __construct()
    {
        $this->method      = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->uri         = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $this->queryParams = $_GET;
        $this->files       = $_FILES;
        $this->headers     = $this->parseHeaders();
        $this->body        = $this->parseBody();
    }

    // ------ Getters ------

    public function getMethod(): string { return $this->method; }
    public function getUri(): string    { return $this->uri; }

    /** Lấy query string param */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->queryParams[$key] ?? $default;
    }

    public function allQuery(): array { return $this->queryParams; }

    /** Lấy body param (JSON hoặc form-data) */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function allInput(): array { return $this->body; }

    /** Lấy file upload */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /** Kiểm tra HTTP method */
    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    /** Lấy header */
    public function header(string $key): ?string
    {
        $key = strtolower($key);
        return $this->headers[$key] ?? null;
    }

    // ------ Private helpers ------

    private function parseBody(): array
    {
        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($ct, 'application/json')) {
            return json_decode(file_get_contents('php://input'), true) ?? [];
        }
        // Nếu là PUT/DELETE với form-data, parse thủ công
        if (in_array($this->method, ['PUT', 'DELETE', 'PATCH']) && !str_contains($ct, 'application/json')) {
            parse_str(file_get_contents('php://input'), $data);
            return $data ?: $_POST;
        }
        return $_POST;
    }

    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $val) {
            if (str_starts_with($key, 'HTTP_')) {
                $name           = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name] = $val;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = $_SERVER['CONTENT_TYPE'];
        }
        return $headers;
    }
}
