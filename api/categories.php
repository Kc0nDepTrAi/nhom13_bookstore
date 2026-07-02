<?php
// api/categories.php — CRUD thể loại
// Bảng: categories (id, name)

require_once __DIR__ . '/../db/db_connect.php';

$body = json_decode(file_get_contents("php://input"), true) ?? [];

switch ($method) {

    // -------------------------------------------------------
    // GET /api/categories      → danh sách thể loại (kèm số sách)
    // GET /api/categories/3    → chi tiết 1 thể loại
    // -------------------------------------------------------
    case 'GET':
        if ($id) {
            $stmt = $conn->prepare(
                "SELECT c.*, COUNT(b.id) AS book_count
                 FROM categories c
                 LEFT JOIN books b ON c.id = b.category_id
                 WHERE c.id = ?
                 GROUP BY c.id"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();

            if ($row) {
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Không tìm thấy thể loại với id = $id"]);
            }
        } else {
            $result = $conn->query(
                "SELECT c.*, COUNT(b.id) AS book_count
                 FROM categories c
                 LEFT JOIN books b ON c.id = b.category_id
                 GROUP BY c.id
                 ORDER BY c.name"
            );
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        }
        break;

    // -------------------------------------------------------
    // POST /api/categories → thêm thể loại mới
    // Body JSON: { "name": "Văn học" }
    // -------------------------------------------------------
    case 'POST':
        if (empty($body['name'])) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu trường bắt buộc: name"]);
            exit();
        }

        $name = trim($body['name']);

        // Kiểm tra trùng tên
        $check = $conn->prepare("SELECT id FROM categories WHERE name = ?");
        $check->bind_param("s", $name);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            http_response_code(409);
            echo json_encode(["error" => "Thể loại '$name' đã tồn tại"]);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Thêm thể loại thành công", "id" => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => $conn->error]);
        }
        break;

    // -------------------------------------------------------
    // PUT /api/categories/3 → sửa tên thể loại
    // Body JSON: { "name": "Tên mới" }
    // -------------------------------------------------------
    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu ID trong URL"]);
            exit();
        }
        if (empty($body['name'])) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu trường bắt buộc: name"]);
            exit();
        }

        $name = trim($body['name']);

        // Kiểm tra tồn tại
        $check = $conn->prepare("SELECT id FROM categories WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            http_response_code(404);
            echo json_encode(["error" => "Không tìm thấy thể loại với id = $id"]);
            exit();
        }

        // Kiểm tra trùng tên (trừ chính nó)
        $dup = $conn->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
        $dup->bind_param("si", $name, $id);
        $dup->execute();
        if ($dup->get_result()->num_rows > 0) {
            http_response_code(409);
            echo json_encode(["error" => "Thể loại '$name' đã tồn tại"]);
            exit();
        }

        $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        if ($stmt->execute()) {
            echo json_encode(["message" => "Cập nhật thể loại thành công"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => $conn->error]);
        }
        break;

    // -------------------------------------------------------
    // DELETE /api/categories/3 → xóa thể loại
    // -------------------------------------------------------
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu ID trong URL"]);
            exit();
        }

        // Không cho xóa nếu còn sách thuộc thể loại này
        $check_books = $conn->prepare("SELECT COUNT(*) AS cnt FROM books WHERE category_id = ?");
        $check_books->bind_param("i", $id);
        $check_books->execute();
        $cnt = $check_books->get_result()->fetch_assoc()['cnt'];
        if ($cnt > 0) {
            http_response_code(409);
            echo json_encode(["error" => "Không thể xóa — còn $cnt sách thuộc thể loại này"]);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(["message" => "Xóa thể loại thành công"]);
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
