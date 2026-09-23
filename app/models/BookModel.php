<?php
namespace App\Models;

use App\Core\Model;

/**
 * BookModel — Quản lý bảng `books`
 * Columns: id, title, author, description, price, category, category_id, image, quantity, sold
 */
class BookModel extends Model
{
    protected string $table = 'books';

    /**
     * Lấy tất cả sách kèm tên thể loại, hỗ trợ lọc/tìm kiếm
     * @param array $filters ['search'=>'', 'category_id'=>int]
     */
    public function getAllWithCategory(array $filters = []): array
    {
        $sql    = "SELECT b.*, c.`name` AS `category_name`
                   FROM `books` b
                   LEFT JOIN `categories` c ON b.`category_id` = c.`id`
                   WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql     .= " AND (b.`title` LIKE ? OR b.`author` LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['category_id']) && is_numeric($filters['category_id'])) {
            $sql     .= " AND b.`category_id` = ?";
            $params[] = (int) $filters['category_id'];
        }

        $sql .= " ORDER BY b.`id` DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Lấy một sách kèm tên thể loại */
    public function getByIdWithCategory(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT b.*, c.`name` AS `category_name`
             FROM `books` b
             LEFT JOIN `categories` c ON b.`category_id` = c.`id`
             WHERE b.`id` = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Gợi ý tiêu đề sách cho autocomplete */
    public function searchTitles(string $query, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT `title` FROM `books` WHERE `title` LIKE ? LIMIT ?"
        );
        $stmt->execute(['%' . $query . '%', $limit]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Giảm tồn kho an toàn (transaction-safe)
     * Trả về false nếu không đủ hàng
     */
    public function decreaseStock(int $bookId, int $quantity): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE `books`
             SET `quantity` = `quantity` - ?,
                 `sold`     = `sold`     + ?
             WHERE `id` = ? AND `quantity` >= ?"
        );
        $stmt->execute([$quantity, $quantity, $bookId, $quantity]);
        return $stmt->rowCount() > 0;
    }

    /** Lấy danh sách sách theo category_id */
    public function getByCategory(int $categoryId): array
    {
        return $this->findAll(['category_id' => $categoryId], 'id DESC');
    }

    /** Thêm sách mới (đảm bảo sync trường `category` theo category_id) */
    public function createBook(array $data, string $categoryName): int
    {
        return $this->create([
            'title'       => $data['title'],
            'author'      => $data['author'],
            'description' => $data['description'] ?? '',
            'price'       => (float) ($data['price'] ?? 0),
            'category'    => $categoryName,
            'category_id' => (int)  ($data['category_id'] ?? 0),
            'image'       => $data['image'] ?? '',
            'quantity'    => (int)  ($data['quantity'] ?? 0),
            'sold'        => 0,
        ]);
    }
}
