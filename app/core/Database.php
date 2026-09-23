<?php
namespace App\Core;

/**
 * Database — Singleton PDO connection
 * Thay thế hoàn toàn mysqli bằng PDO chuẩn, có prepared statements.
 */
class Database
{
    private static ?self $instance = null;
    private \PDO $pdo;

    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $this->pdo = new \PDO($dsn, DB_USER, DB_PASS, $options);
    }

    /** Lấy instance duy nhất (Singleton) */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /** Trả về đối tượng PDO */
    public function getConnection(): \PDO
    {
        return $this->pdo;
    }

    // Ngăn clone và unserialize
    private function __clone() {}
    public function __wakeup(): void
    {
        throw new \RuntimeException('Cannot unserialize singleton.');
    }
}
