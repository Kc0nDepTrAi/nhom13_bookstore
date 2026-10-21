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
        $sql    = "SELECT b.*, c.`name` AS `category_name`,
                          COALESCE(r.`rating_average`, 0) AS `rating_average`,
                          COALESCE(r.`rating_count`, 0) AS `rating_count`
                   FROM `books` b
                   LEFT JOIN `categories` c ON b.`category_id` = c.`id`
                   LEFT JOIN (SELECT `book_id`, ROUND(AVG(`rating`), 2) AS `rating_average`, COUNT(*) AS `rating_count`
                              FROM `book_reviews` GROUP BY `book_id`) r ON r.`book_id` = b.`id`
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
            "SELECT b.*, c.`name` AS `category_name`,
                    COALESCE(r.`rating_average`, 0) AS `rating_average`,
                    COALESCE(r.`rating_count`, 0) AS `rating_count`
             FROM `books` b
             LEFT JOIN `categories` c ON b.`category_id` = c.`id`
             LEFT JOIN (SELECT `book_id`, ROUND(AVG(`rating`), 2) AS `rating_average`, COUNT(*) AS `rating_count`
                        FROM `book_reviews` GROUP BY `book_id`) r ON r.`book_id` = b.`id`
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

    /** Public aggregate data and only the current reader's review/eligibility. */
    public function getReviewState(int $bookId, string $username = ''): array
    {
        $stmt = $this->db->prepare(
            "SELECT `rating`, COUNT(*) AS `total` FROM `book_reviews`
             WHERE `book_id` = ? GROUP BY `rating`"
        );
        $stmt->execute([$bookId]);
        $ratings = array_fill(1, 5, 0);
        $count = 0;
        $sum = 0;
        foreach ($stmt->fetchAll() as $row) {
            $rating = (int) $row['rating'];
            $total = (int) $row['total'];
            $ratings[$rating] = $total;
            $count += $total;
            $sum += $rating * $total;
        }
        $eligible = false;
        $userRating = null;
        $userComment = '';
        if ($username !== '') {
            $stmt = $this->db->prepare(
                "SELECT r.`rating`, r.`comment`, EXISTS (
                     SELECT 1 FROM `orders` o
                     JOIN `order_details` od ON od.`order_id` = o.`id`
                     WHERE o.`username` = u.`username` AND o.`status` = 'Đã hoàn thành' AND od.`book_id` = ?
                 ) AS `eligible`
                 FROM `users` u
                 LEFT JOIN `book_reviews` r ON r.`user_id` = u.`id` AND r.`book_id` = ?
                 WHERE u.`username` = ? AND u.`role` = 'user' LIMIT 1"
            );
            $stmt->execute([$bookId, $bookId, $username]);
            if ($reader = $stmt->fetch()) {
                $eligible = (bool) $reader['eligible'];
                $userRating = $reader['rating'] === null ? null : (int) $reader['rating'];
                $userComment = (string) ($reader['comment'] ?? '');
            }
        }
        return [
            'book_id' => $bookId,
            'rating_average' => $count ? round($sum / $count, 2) : 0,
            'rating_count' => $count,
            'ratings' => $ratings,
            'eligible' => $eligible,
            'user_rating' => $userRating,
            'user_comment' => $userComment,
        ];
    }

    /** Atomic eligibility check and upsert: a reader has one rating per book. */
    public function saveReview(int $bookId, string $username, int $rating, string $comment = ''): bool
    {
        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException('Vui lòng chọn từ 1 đến 5 sao.');
        }
        if (mb_strlen($comment, 'UTF-8') > 2000) {
            throw new \InvalidArgumentException('Nội dung đánh giá tối đa 2.000 ký tự.');
        }
        $stmt = $this->db->prepare(
            "INSERT INTO `book_reviews` (`book_id`, `user_id`, `rating`, `comment`)
             SELECT ?, u.`id`, ?, ? FROM `users` u
             WHERE u.`username` = ? AND u.`role` = 'user' AND EXISTS (
                 SELECT 1 FROM `orders` o
                 JOIN `order_details` od ON od.`order_id` = o.`id`
                 WHERE o.`username` = u.`username` AND o.`status` = 'Đã hoàn thành' AND od.`book_id` = ?
             )
             ON DUPLICATE KEY UPDATE `rating` = VALUES(`rating`), `comment` = VALUES(`comment`), `updated_at` = CURRENT_TIMESTAMP"
        );
        $stmt->execute([$bookId, $rating, trim($comment), $username, $bookId]);
        // MySQL reports zero changed rows for a repeated rating; still an authorized success.
        return $this->getReviewState($bookId, $username)['eligible'];
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
