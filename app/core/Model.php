<?php
namespace App\Core;

/**
 * Model — Lớp cơ sở cho tất cả Models
 * Cung cấp CRUD chung với PDO prepared statements.
 */
abstract class Model
{
    protected \PDO   $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ----------------------------------------------------------
    //  READ
    // ----------------------------------------------------------

    /** Lấy tất cả bản ghi, có thể lọc theo conditions */
    public function findAll(array $conditions = [], string $orderBy = ''): array
    {
        [$whereSql, $params] = $this->buildWhere($conditions);
        $sql  = "SELECT * FROM `{$this->table}`" . $whereSql;
        $sql .= $orderBy ? " ORDER BY $orderBy" : '';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Lấy một bản ghi theo primary key */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Lấy một bản ghi theo điều kiện bất kỳ */
    public function findOne(array $conditions): ?array
    {
        [$whereSql, $params] = $this->buildWhere($conditions);
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}`" . $whereSql . " LIMIT 1"
        );
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    /** Đếm số bản ghi */
    public function count(array $conditions = []): int
    {
        [$whereSql, $params] = $this->buildWhere($conditions);
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM `{$this->table}`" . $whereSql
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /** Kiểm tra bản ghi có tồn tại không */
    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetchColumn() !== false;
    }

    // ----------------------------------------------------------
    //  WRITE
    // ----------------------------------------------------------

    /** Tạo bản ghi mới, trả về ID */
    public function create(array $data): int
    {
        $cols         = implode('`, `', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $this->db->prepare(
            "INSERT INTO `{$this->table}` (`$cols`) VALUES ($placeholders)"
        );
        $stmt->execute(array_values($data));
        return (int) $this->db->lastInsertId();
    }

    /** Cập nhật bản ghi theo ID */
    public function update(int $id, array $data): bool
    {
        if (empty($data)) return false;
        $sets = implode(', ', array_map(fn($c) => "`$c` = ?", array_keys($data)));
        $stmt = $this->db->prepare(
            "UPDATE `{$this->table}` SET $sets WHERE `{$this->primaryKey}` = ?"
        );
        return $stmt->execute([...array_values($data), $id]);
    }

    /** Xóa bản ghi theo ID */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?"
        );
        return $stmt->execute([$id]);
    }

    // ----------------------------------------------------------
    //  Helpers
    // ----------------------------------------------------------

    /** Xây dựng mệnh đề WHERE từ mảng conditions */
    protected function buildWhere(array $conditions): array
    {
        if (empty($conditions)) return ['', []];
        $parts  = [];
        $params = [];
        foreach ($conditions as $col => $val) {
            $parts[]  = "`$col` = ?";
            $params[] = $val;
        }
        return [' WHERE ' . implode(' AND ', $parts), $params];
    }

    /** Trả về PDO để subclass dùng query phức tạp */
    protected function pdo(): \PDO
    {
        return $this->db;
    }
}
