<?php
namespace App\Models;

use App\Core\Model;

/**
 * CategoryModel — Quản lý bảng `categories`
 * Columns: id, name
 */
class CategoryModel extends Model
{
    protected string $table = 'categories';

    /** Lấy tất cả thể loại kèm số lượng sách */
    public function getAllWithBookCount(): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, COUNT(b.`id`) AS `book_count`
             FROM `categories` c
             LEFT JOIN `books` b ON c.`id` = b.`category_id`
             GROUP BY c.`id`
             ORDER BY c.`id` ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Kiểm tra tên thể loại đã tồn tại chưa */
    public function nameExists(string $name, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM `categories` WHERE `name` = ? AND `id` != ?"
        );
        $stmt->execute([$name, $excludeId]);
        return $stmt->fetchColumn() !== false;
    }
}
