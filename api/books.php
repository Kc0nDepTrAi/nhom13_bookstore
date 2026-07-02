<?php
// api/books.php — CRUD sách
// Bảng: books (id, title, author, description, price, category, category_id, image, quantity, sold)

require_once __DIR__ . '/../db/db_connect.php';

// Đọc body JSON (dùng cho POST/PUT)
$body = json_decode(file_get_contents("php://input"), true) ?? [];

switch ($method) {

    // -------------------------------------------------------
    // GET /api/books        → danh sách tất cả sách
    // GET /api/books?category_id=2 → lọc theo thể loại
    // GET /api/books/5      → chi tiết 1 cuốn
    // -------------------------------------------------------
    case 'GET':
        if ($id) {
            $stmt = $conn->prepare(
                "SELECT b.*, c.name AS category_name
                 FROM books b
                 LEFT JOIN categories c ON b.category_id = c.id
                 WHERE b.id = ?"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $book = $stmt->get_result()->fetch_assoc();

            if ($book) {
                echo json_encode($book);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Không tìm thấy sách với id = $id"]);
            }
        } else {
            // Hỗ trợ lọc theo category_id
            if (!empty($_GET['category_id']) && is_numeric($_GET['category_id'])) {
                $cat_id = (int)$_GET['category_id'];
                $stmt = $conn->prepare(
                    "SELECT b.*, c.name AS category_name
                     FROM books b
                     LEFT JOIN categories c ON b.category_id = c.id
                     WHERE b.category_id = ?
                     ORDER BY b.id DESC"
                );
                $stmt->bind_param("i", $cat_id);
                $stmt->execute();
                $books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            } else {
                $result = $conn->query(
                    "SELECT b.*, c.name AS category_name
                     FROM books b
                     LEFT JOIN categories c ON b.category_id = c.id
                     ORDER BY b.id DESC"
                );
                $books = $result->fetch_all(MYSQLI_ASSOC);
            }
            echo json_encode($books);
        }
        break;

    // -------------------------------------------------------
    // POST /api/books → thêm sách mới
    // Body JSON: { title, author, description, price, category_id, quantity }
    // (image upload không hỗ trợ qua JSON — dùng tên file trực tiếp nếu cần)
    // -------------------------------------------------------
    case 'POST':
        $required = ['title', 'author', 'price', 'category_id'];
        foreach ($required as $field) {
            if (empty($body[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Thiếu trường bắt buộc: $field"]);
                exit();
            }
        }

        // Lấy tên thể loại từ category_id
        $cat_stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
        $cat_id   = (int)$body['category_id'];
        $cat_stmt->bind_param("i", $cat_id);
        $cat_stmt->execute();
        $cat_row = $cat_stmt->get_result()->fetch_assoc();
        if (!$cat_row) {
            http_response_code(400);
            echo json_encode(["error" => "category_id không hợp lệ"]);
            exit();
        }
        $category_name = $cat_row['name'];

        $title       = $body['title'];
        $author      = $body['author'];
        $description = $body['description'] ?? '';
        $price       = (float)$body['price'];
        $quantity    = isset($body['quantity']) ? (int)$body['quantity'] : 0;
        $image       = $body['image'] ?? '';

        $stmt = $conn->prepare(
            "INSERT INTO books (title, author, description, price, category, category_id, image, quantity, sold)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)"
        );
        $stmt->bind_param(
            "sssdsisi",
            $title, $author, $description, $price,
            $category_name, $cat_id, $image, $quantity
        );

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Thêm sách thành công", "id" => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => $conn->error]);
        }
        break;

    // -------------------------------------------------------
    // PUT /api/books/5 → cập nhật sách
    // Body JSON: { title, author, description, price, category_id, quantity }
    // -------------------------------------------------------
    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu ID sách trong URL"]);
            exit();
        }

        // Kiểm tra sách tồn tại
        $check = $conn->prepare("SELECT id FROM books WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            http_response_code(404);
            echo json_encode(["error" => "Không tìm thấy sách với id = $id"]);
            exit();
        }

        // Lấy tên thể loại nếu có cập nhật category_id
        $category_name = null;
        $cat_id        = null;
        if (!empty($body['category_id'])) {
            $cat_id   = (int)$body['category_id'];
            $cat_stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
            $cat_stmt->bind_param("i", $cat_id);
            $cat_stmt->execute();
            $cat_row = $cat_stmt->get_result()->fetch_assoc();
            if (!$cat_row) {
                http_response_code(400);
                echo json_encode(["error" => "category_id không hợp lệ"]);
                exit();
            }
            $category_name = $cat_row['name'];
        }

        $title       = $body['title']       ?? null;
        $author      = $body['author']      ?? null;
        $description = $body['description'] ?? null;
        $price       = isset($body['price']) ? (float)$body['price'] : null;
        $quantity    = isset($body['quantity']) ? (int)$body['quantity'] : null;
        $image       = $body['image']       ?? null;

        $stmt = $conn->prepare(
            "UPDATE books
             SET title       = COALESCE(?, title),
                 author      = COALESCE(?, author),
                 description = COALESCE(?, description),
                 price       = COALESCE(?, price),
                 category    = COALESCE(?, category),
                 category_id = COALESCE(?, category_id),
                 image       = COALESCE(?, image),
                 quantity    = COALESCE(?, quantity)
             WHERE id = ?"
        );
        $stmt->bind_param(
            "sssdsisii",
            $title, $author, $description, $price,
            $category_name, $cat_id, $image, $quantity, $id
        );

        if ($stmt->execute()) {
            echo json_encode(["message" => "Cập nhật sách thành công"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => $conn->error]);
        }
        break;

    // -------------------------------------------------------
    // DELETE /api/books/5 → xóa sách
    // -------------------------------------------------------
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu ID sách trong URL"]);
            exit();
        }

        $check = $conn->prepare("SELECT id FROM books WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            http_response_code(404);
            echo json_encode(["error" => "Không tìm thấy sách với id = $id"]);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(["message" => "Xóa sách thành công"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => $conn->error]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method không được hỗ trợ"]);
        break;
}
