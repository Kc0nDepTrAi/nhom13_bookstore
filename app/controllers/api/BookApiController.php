<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Models\BookModel;
use App\Models\CategoryModel;

/**
 * BookApiController — REST API CRUD sách
 *
 * GET    /api/books              → index()   — danh sách, hỗ trợ ?search=&category_id=
 * GET    /api/books/{id}         → show()    — chi tiết 1 sách
 * POST   /api/books              → store()   — thêm sách (admin)
 * PUT    /api/books/{id}         → update()  — sửa sách (admin)
 * DELETE /api/books/{id}         → destroy() — xóa sách (admin)
 * GET    /api/books/autocomplete → autocomplete() — gợi ý tiêu đề
 */
class BookApiController extends Controller
{
    private BookModel     $bookModel;
    private CategoryModel $catModel;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->bookModel = new BookModel();
        $this->catModel  = new CategoryModel();
    }

    // GET /api/books
    public function index(): void
    {
        $filters = array_filter([
            'search'      => $this->request->query('search'),
            'category_id' => $this->request->query('category_id'),
        ]);
        $books = $this->bookModel->getAllWithCategory($filters);
        $this->success($books);
    }

    // GET /api/books/{id}
    public function show(string $id): void
    {
        $book = $this->bookModel->getByIdWithCategory((int) $id);
        if (!$book) {
            $this->notFound("Không tìm thấy sách với id = $id");
            return;
        }
        $this->success($book);
    }

    // GET /api/books/{id}/reviews
    public function reviews(string $id): void
    {
        if (!ctype_digit($id) || (int) $id < 1 || !$this->bookModel->exists((int) $id)) {
            $this->notFound('Không tìm thấy sách.');
            return;
        }
        $this->success($this->bookModel->getReviewState((int) $id, (string) ($_SESSION['username'] ?? '')));
    }

    // POST /api/books/{id}/reviews (CSRF is enforced by the existing API bootstrap).
    public function storeReview(string $id): void
    {
        $this->requireAuth();
        if ($this->isAdmin()) {
            $this->error('Chỉ khách hàng đã mua sách mới có thể đánh giá.', 403);
            return;
        }
        if (!ctype_digit($id) || (int) $id < 1 || !$this->bookModel->exists((int) $id)) {
            $this->notFound('Không tìm thấy sách.');
            return;
        }
        $rawRating = $this->request->input('rating');
        // Reject JSON booleans, arrays, fractions and strings containing non-digits.
        if ((!is_int($rawRating) && !is_string($rawRating)) || !preg_match('/^[1-5]$/D', (string) $rawRating)) {
            $this->error('Vui lòng chọn số sao nguyên từ 1 đến 5.');
            return;
        }
        $comment = $this->request->input('comment', '');
        if (!is_string($comment) || mb_strlen($comment, 'UTF-8') > 2000) {
            $this->error('Nội dung đánh giá phải là văn bản tối đa 2.000 ký tự.');
            return;
        }
        $username = (string) $_SESSION['username'];
        if (!$this->bookModel->saveReview((int) $id, $username, (int) $rawRating, trim($comment))) {
            $this->error('Bạn cần có đơn hàng đã hoàn thành chứa sách này để đánh giá.', 403);
            return;
        }
        $this->success($this->bookModel->getReviewState((int) $id, $username), 'Đã lưu đánh giá của bạn.');
    }

    // POST /api/books
    public function store(): void
    {
        $this->requireAdmin();

        $data     = $this->request->allInput();
        try { bookstore_validate_book($data); } catch (\InvalidArgumentException $e) { $this->error($e->getMessage()); return; }
        $required = ['title', 'author', 'price', 'category_id'];
        foreach ($required as $f) {
            if (empty($data[$f])) {
                $this->error("Thiếu trường bắt buộc: $f");
                return;
            }
        }

        $cat = $this->catModel->findById((int) $data['category_id']);
        if (!$cat) {
            $this->error('category_id không hợp lệ');
            return;
        }

        $id = $this->bookModel->createBook($data, $cat['name']);
        $this->success(['id' => $id], 'Thêm sách thành công', 201);
    }

    // PUT /api/books/{id}
    public function update(string $id): void
    {
        $this->requireAdmin();

        $bookId = (int) $id;
        if (!$this->bookModel->exists($bookId)) {
            $this->notFound("Không tìm thấy sách với id = $bookId");
            return;
        }

        $data       = $this->request->allInput();
        try { bookstore_validate_book($data); } catch (\InvalidArgumentException $e) { $this->error($e->getMessage()); return; }
        $updateData = [];

        $fields = ['title', 'author', 'description', 'image'];
        foreach ($fields as $f) {
            if (array_key_exists($f, $data)) $updateData[$f] = $data[$f];
        }
        if (array_key_exists('price', $data))    $updateData['price']    = (float) $data['price'];
        if (array_key_exists('quantity', $data)) $updateData['quantity'] = (int)   $data['quantity'];

        if (!empty($data['category_id'])) {
            $cat = $this->catModel->findById((int) $data['category_id']);
            if (!$cat) {
                $this->error('category_id không hợp lệ');
                return;
            }
            $updateData['category_id'] = (int) $data['category_id'];
            $updateData['category']    = $cat['name'];
        }

        if (empty($updateData)) {
            $this->error('Không có dữ liệu để cập nhật');
            return;
        }

        $this->bookModel->update($bookId, $updateData);
        $this->success(null, 'Cập nhật sách thành công');
    }

    // DELETE /api/books/{id}
    public function destroy(string $id): void
    {
        $this->requireAdmin();

        $bookId = (int) $id;
        if (!$this->bookModel->exists($bookId)) {
            $this->notFound("Không tìm thấy sách với id = $bookId");
            return;
        }
        $this->bookModel->delete($bookId);
        $this->success(null, 'Xóa sách thành công');
    }

    // GET /api/books/autocomplete?q=
    public function autocomplete(): void
    {
        $q = trim((string) $this->request->query('q', ''));
        if (strlen($q) < 1) {
            $this->success([]);
            return;
        }
        $titles = $this->bookModel->searchTitles($q);
        $this->success($titles);
    }
}
