<?php
namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;

/**
 * CartController — Trang giỏ hàng (Web)
 * GET /cart → index()
 */
class CartController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function index(): void
    {
        $cart  = $_SESSION['cart'] ?? [];
        $total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart));
        $this->render('cart.index', compact('cart', 'total'));
    }
}
