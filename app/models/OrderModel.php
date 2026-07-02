<?php
namespace App\Models;

use App\Core\Model;

/**
 * OrderModel — Quản lý bảng `orders` + `order_details`
 */
class OrderModel extends Model
{
    protected string $table = 'orders';

    public const VALID_STATUSES = ['Đang xử lý', 'Chờ xử lý', 'Đang giao', 'Đã hoàn thành', 'Đã hủy'];

    // ----------------------------------------------------------
    //  READ
    // ----------------------------------------------------------

    /** Lấy tất cả đơn hàng kèm chi tiết, có thể lọc theo username */
    public function getAllWithDetails(string $username = ''): array
    {
        $sql    = "SELECT o.*, u.`fullname`
                   FROM `orders` o
                   LEFT JOIN `users` u ON o.`username` = u.`username`";
        $params = [];

        if ($username !== '') {
            $sql     .= " WHERE o.`username` = ?";
            $params[] = $username;
        }
        $sql .= " ORDER BY o.`created_at` DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        foreach ($orders as &$order) {
            $order['items'] = $this->getOrderItems((int) $order['id']);
        }
        return $orders;
    }

    /** Lấy một đơn hàng kèm chi tiết theo ID */
    public function getByIdWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.`fullname`
             FROM `orders` o
             LEFT JOIN `users` u ON o.`username` = u.`username`
             WHERE o.`id` = ?"
        );
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        if (!$order) return null;

        $order['items'] = $this->getOrderItems($id);
        return $order;
    }

    /** Lấy chi tiết sản phẩm của một đơn hàng */
    public function getOrderItems(int $orderId): array
    {
        $stmt = $this->db->prepare(
            "SELECT od.*, b.`title`, b.`image`
             FROM `order_details` od
             JOIN `books` b ON od.`book_id` = b.`id`
             WHERE od.`order_id` = ?"
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------
    //  WRITE
    // ----------------------------------------------------------

    /**
     * Tạo đơn hàng mới với transaction:
     * - Validate sách còn hàng
     * - Insert orders + order_details
     * - Trừ kho
     * @throws \Exception nếu validation thất bại
     */
    public function createWithItems(string $username, array $items): array
    {
        $bookModel = new BookModel();
        $total     = 0;
        $validated = [];

        foreach ($items as $item) {
            $bookId   = (int) ($item['book_id']  ?? 0);
            $quantity = (int) ($item['quantity']  ?? 0);

            if ($bookId <= 0 || $quantity <= 0) {
                throw new \InvalidArgumentException("item không hợp lệ: book_id=$bookId, quantity=$quantity");
            }

            $book = $bookModel->findById($bookId);
            if (!$book) {
                throw new \RuntimeException("Sách với book_id=$bookId không tồn tại");
            }
            if ($book['quantity'] < $quantity) {
                throw new \RuntimeException("Sách '{$book['title']}' không đủ tồn kho (còn {$book['quantity']})");
            }

            $total      += $book['price'] * $quantity;
            $validated[] = [
                'book_id'  => $bookId,
                'quantity' => $quantity,
                'price'    => (float) $book['price'],
            ];
        }

        // Transaction
        $this->db->beginTransaction();
        try {
            $orderId = $this->create([
                'username'    => $username,
                'total_price' => $total,
                'status'      => 'Đang xử lý',
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

            $detStmt = $this->db->prepare(
                "INSERT INTO `order_details` (`order_id`, `book_id`, `quantity`, `price`)
                 VALUES (?, ?, ?, ?)"
            );
            foreach ($validated as $item) {
                $detStmt->execute([$orderId, $item['book_id'], $item['quantity'], $item['price']]);
                if (!$bookModel->decreaseStock($item['book_id'], $item['quantity'])) {
                    throw new \RuntimeException("Trừ kho thất bại cho book_id={$item['book_id']}");
                }
            }

            $this->db->commit();
            return ['order_id' => $orderId, 'total_price' => $total];
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Cập nhật trạng thái đơn hàng */
    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException(
                "Trạng thái không hợp lệ. Hợp lệ: " . implode(', ', self::VALID_STATUSES)
            );
        }
        return $this->update($id, ['status' => $status]);
    }

    /** Xóa đơn hàng và tất cả chi tiết */
    public function deleteWithDetails(int $id): bool
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM `order_details` WHERE `order_id` = ?")->execute([$id]);
            $this->delete($id);
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
