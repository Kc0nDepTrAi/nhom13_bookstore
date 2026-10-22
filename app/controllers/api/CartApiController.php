<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Models\BookModel;
use App\Models\OrderModel;
use App\Models\UserModel;

/**
 * CartApiController — REST API giỏ hàng (lưu trong SESSION)
 *
 * GET    /api/cart            → index()    — lấy giỏ hàng
 * POST   /api/cart            → add()      — thêm sản phẩm
 * PUT    /api/cart/{book_id}  → updateItem() — cập nhật số lượng
 * DELETE /api/cart/{book_id}  → removeItem() — xóa sản phẩm
 * DELETE /api/cart            → clear()    — xóa toàn bộ
 * POST   /api/cart/checkout   → checkout() — đặt hàng
 */
class CartApiController extends Controller
{
    private BookModel  $bookModel;
    private OrderModel $orderModel;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->bookModel  = new BookModel();
        $this->orderModel = new OrderModel();
    }

    // GET /api/cart
    public function index(): void
    {
        $cart = $this->getCart();
        $this->success($this->cartPayload($cart));
    }

    // POST /api/cart
    public function add(): void
    {
        $this->requireAuth();

        $rawId=$this->request->input('book_id',0);
        $rawQuantity=$this->request->input('quantity',1);
        if (filter_var($rawId,FILTER_VALIDATE_INT)===false || filter_var($rawQuantity,FILTER_VALIDATE_INT)===false || (int)$rawQuantity<1) {
            $this->error('Mã sách và số lượng phải là số nguyên dương.');return;
        }
        $bookId = (int) $rawId;
        $quantity = (int) $rawQuantity;

        if ($bookId <= 0) {
            $this->error('book_id không hợp lệ');
            return;
        }

        $book = $this->bookModel->findById($bookId);
        if (!$book) {
            $this->notFound("Không tìm thấy sách với id = $bookId");
            return;
        }
        if ($book['quantity'] <= 0) {
            $this->error('Sách đã hết hàng');
            return;
        }

        $cart = $this->getCart();
        $currentQty = $cart[$bookId]['quantity'] ?? 0;
        $newQty = $currentQty + $quantity;

        if ($newQty > $book['quantity']) {
            $this->error("Chỉ còn {$book['quantity']} sản phẩm trong kho");
            return;
        }

        $cart[$bookId] = [
            'book_id'  => $bookId,
            'title'    => $book['title'],
            'author'   => $book['author'],
            'price'    => (float) $book['price'],
            'image'    => $book['image'],
            'quantity' => $newQty,
        ];

        $this->saveCart($cart);
        $this->success($this->cartPayload($cart), 'Đã thêm vào giỏ hàng');
    }

    // PUT /api/cart/{book_id}   body: {quantity}
    public function updateItem(string $bookId): void
    {
        $this->requireAuth();

        $bookId   = (int) $bookId;
        $rawQuantity=$this->request->input('quantity',0);
        if (filter_var($rawQuantity,FILTER_VALIDATE_INT)===false || (int)$rawQuantity<0) {
            $this->error('Số lượng phải là số nguyên không âm.');return;
        }
        $quantity = (int) $rawQuantity;

        $cart = $this->getCart();
        if (!isset($cart[$bookId])) {
            $this->notFound('Sản phẩm không có trong giỏ');
            return;
        }

        if ($quantity <= 0) {
            unset($cart[$bookId]);
        } else {
            $book = $this->bookModel->findById($bookId);
            if (!$book) {$this->error('Sách không còn tồn tại.');return;}
            if ($book && $quantity > $book['quantity']) {
                $this->error("Chỉ còn {$book['quantity']} sản phẩm trong kho");
                return;
            }
            $cart[$bookId]['quantity'] = $quantity;
        }

        $this->saveCart($cart);
        $this->success($this->cartPayload($cart), 'Cập nhật giỏ hàng thành công');
    }

    // DELETE /api/cart/{book_id}
    public function removeItem(string $bookId): void
    {
        $bookId = (int) $bookId;
        $cart   = $this->getCart();
        unset($cart[$bookId]);
        $this->saveCart($cart);
        $this->success($this->cartPayload($cart), 'Đã xóa sản phẩm khỏi giỏ');
    }

    // DELETE /api/cart  (xóa toàn bộ)
    public function clear(): void
    {
        $this->saveCart([]);
        $this->success($this->cartPayload([]), 'Đã xóa giỏ hàng');
    }

    // POST /api/cart/checkout
    public function checkout(): void
    {
        $this->requireAuth();

        $cart = $this->getCart();
        if (empty($cart)) {
            $this->error('Giỏ hàng trống');
            return;
        }

        $username = $_SESSION['username'];
        $items    = array_map(fn($item) => [
            'book_id'  => $item['book_id'],
            'quantity' => $item['quantity'],
        ], $cart);

        try {
            $result = $this->orderModel->createWithItems($username, $items, [
                'notes' => $this->request->input('notes', ''),
                'payment_method' => $this->request->input('payment_method', 'cash'),
            ]);
            $this->saveCart([]);
            $this->success($result, 'Đặt hàng thành công!', 201);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    // ------ Private helpers ------

    private function cartPayload(array $cart): array
    {
        $subtotal = $this->calcTotal($cart);
        $count = (int) array_sum(array_column($cart, 'quantity'));
        $shipping = ($count > 0 && $subtotal < 250000) ? 30000.0 : 0.0;
        $total = $subtotal + $shipping;

        return [
            'items'    => array_values($cart),
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total'    => $total,
            'count'    => $count,
        ];
    }

    private function getCart(): array
    {
        $cart = $_SESSION['cart'] ?? [];
        foreach ($cart as $key => &$item) {
            $item['book_id'] = (int) ($item['book_id'] ?? $key);
            if (!isset($item['author'])) {
                $book = $this->bookModel->findById($item['book_id']);
                $item['author'] = $book['author'] ?? '';
            }
        }
        unset($item);
        return $cart;
    }

    private function saveCart(array $cart): void
    {
        $_SESSION['cart'] = $cart;
    }

    private function calcTotal(array $cart): float
    {
        return (float) array_sum(
            array_map(fn($i) => $i['price'] * $i['quantity'], $cart)
        );
    }
}
