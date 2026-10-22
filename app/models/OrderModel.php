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
    public function createWithItems(string $username, array $items, array $checkout = []): array
    {
        $notes = $checkout['notes'] ?? '';
        $paymentMethod = $checkout['payment_method'] ?? 'cash';
        if (!is_string($notes) || !is_string($paymentMethod)) throw new \InvalidArgumentException('Ghi chú hoặc phương thức thanh toán không hợp lệ.');
        $notes = trim($notes);
        if (mb_strlen($notes, 'UTF-8') > 2000) throw new \InvalidArgumentException('Ghi chú tối đa 2000 ký tự.');
        if (!in_array($paymentMethod, ['cash', 'qr'], true)) throw new \InvalidArgumentException('Phương thức thanh toán không hợp lệ.');
        $combined=[];
        foreach ($items as $item) {
            if (!is_array($item) || filter_var($item['book_id']??null,FILTER_VALIDATE_INT)===false || filter_var($item['quantity']??null,FILTER_VALIDATE_INT)===false || (int)$item['book_id']<1 || (int)$item['quantity']<1) throw new \InvalidArgumentException('Số lượng và mã sách phải là số nguyên dương.');
            $combined[(int)$item['book_id']]=($combined[(int)$item['book_id']]??0)+(int)$item['quantity'];
        }
        if (!$combined) throw new \InvalidArgumentException('Đơn hàng cần có sách.');
        ksort($combined);
        $fingerprint=hash('sha256',$username.json_encode([$combined,$notes,$paymentMethod], JSON_UNESCAPED_UNICODE));
        $previous=$_SESSION['recent_order']??null;
        if ($previous && $previous['key']===$fingerprint && $previous['time']>time()-5 && $this->exists($previous['result']['order_id'])) return $previous['result'];
        $items=[];foreach ($combined as $id=>$qty) $items[]=['book_id'=>$id,'quantity'=>$qty];
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

        // Phí vận chuyển: nếu tổng giá sách dưới 250.000đ thì cộng 30.000đ phí giao hàng
        $shippingFee = ($total > 0 && $total < 250000) ? 30000 : 0;
        $finalTotal  = $total + $shippingFee;

        // Transaction
        $this->db->beginTransaction();
        try {
            $orderId = $this->create([
                'username'    => $username,
                'total_price' => $finalTotal,
                'status'      => 'Đang xử lý',
                'notes'       => $notes,
                'payment_method' => $paymentMethod,
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
            $result=['order_id' => $orderId, 'total_price' => $finalTotal, 'notes' => $notes, 'payment_method' => $paymentMethod];
            $_SESSION['recent_order']=['key'=>$fingerprint,'time'=>time(),'result'=>$result];
            return $result;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Transactional status changes and stock restoration. */
    private function lockedOrder(int $id): array {
        $stmt=$this->db->prepare('SELECT * FROM orders WHERE id=? FOR UPDATE');$stmt->execute([$id]);
        $order=$stmt->fetch();if (!$order) throw new \RuntimeException('Không tìm thấy đơn hàng.');return $order;
    }
    private function restoreStock(int $id): void {
        $stmt=$this->db->prepare('SELECT book_id, SUM(quantity) AS quantity FROM order_details WHERE order_id=? GROUP BY book_id ORDER BY book_id');$stmt->execute([$id]);
        foreach ($stmt->fetchAll() as $item) {
            $this->db->prepare('UPDATE books SET quantity=quantity+?, sold=GREATEST(0,sold-?) WHERE id=?')->execute([$item['quantity'],$item['quantity'],$item['book_id']]);
        }
    }
    public function updateStatus(int $id, string $status): bool {
        if (!in_array($status,self::VALID_STATUSES,true)) throw new \InvalidArgumentException('Trạng thái không hợp lệ.');
        $this->db->beginTransaction();
        try {
            $o=$this->lockedOrder($id);$old=$o['status'];
            if ($old!==$status && $status==='Đã hủy') $this->restoreStock($id);
            if ($old==='Đã hủy' && $status!==$old) {
                $items=$this->db->prepare('SELECT book_id, SUM(quantity) AS quantity FROM order_details WHERE order_id=? GROUP BY book_id ORDER BY book_id');
                $items->execute([$id]);$bookModel=new BookModel();
                foreach ($items->fetchAll() as $item) {
                    if (!$bookModel->decreaseStock((int)$item['book_id'],(int)$item['quantity'])) {
                        throw new \InvalidArgumentException('Không đủ tồn kho để mở lại đơn hàng. Vui lòng kiểm tra sách #'.$item['book_id'].'.');
                    }
                }
            }
            $this->update($id,['status'=>$status]);$this->db->commit();return true;
        } catch (\Throwable $e) {$this->db->rollBack();throw $e;}
    }
    public function deleteWithDetails(int $id, ?string $owner=null): bool {
        $this->db->beginTransaction();
        try {
            $o=$this->lockedOrder($id);
            if ($owner!==null && $o['username']!==$owner) throw new \RuntimeException('Bạn không có quyền xóa đơn này.');
            if ($owner!==null && !in_array($o['status'],['Đang xử lý','Chờ xử lý','Đã hủy'],true)) throw new \RuntimeException('Không thể xóa đơn đang giao hoặc đã hoàn thành.');
            if (!in_array($o['status'],['Đã hủy','Đã hoàn thành'],true)) $this->restoreStock($id);
            $this->db->prepare('DELETE FROM order_details WHERE order_id=?')->execute([$id]);$this->delete($id);
            $this->db->commit();return true;
        } catch (\Throwable $e) {$this->db->rollBack();throw $e;}
    }
}
