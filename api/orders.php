<?php
// api/orders.php — CRUD đơn hàng
// Bảng: orders (id, username, total_price, status, created_at)
//        order_details (id, order_id, book_id, quantity, price)

require_once __DIR__ . '/../db/db_connect.php';

$body = json_decode(file_get_contents("php://input"), true) ?? [];

switch ($method) {

    // -------------------------------------------------------
    // GET /api/orders              → danh sách đơn hàng (kèm chi tiết)
    // GET /api/orders?username=abc → lọc theo username
    // GET /api/orders/5            → chi tiết 1 đơn hàng
    // -------------------------------------------------------
    case 'GET':
        if ($id) {
            // Lấy thông tin đơn hàng
            $stmt = $conn->prepare(
                "SELECT o.*, u.fullname
                 FROM orders o
                 LEFT JOIN users u ON o.username = u.username
                 WHERE o.id = ?"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $order = $stmt->get_result()->fetch_assoc();

            if (!$order) {
                http_response_code(404);
                echo json_encode(["error" => "Không tìm thấy đơn hàng với id = $id"]);
                exit();
            }

            // Lấy chi tiết sản phẩm của đơn hàng
            $det = $conn->prepare(
                "SELECT od.*, b.title, b.image
                 FROM order_details od
                 JOIN books b ON od.book_id = b.id
                 WHERE od.order_id = ?"
            );
            $det->bind_param("i", $id);
            $det->execute();
            $order['items'] = $det->get_result()->fetch_all(MYSQLI_ASSOC);

            echo json_encode($order);
        } else {
            if (!empty($_GET['username'])) {
                $username = $_GET['username'];
                $stmt = $conn->prepare(
                    "SELECT o.*, u.fullname
                     FROM orders o
                     LEFT JOIN users u ON o.username = u.username
                     WHERE o.username = ?
                     ORDER BY o.created_at DESC"
                );
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            } else {
                $result = $conn->query(
                    "SELECT o.*, u.fullname
                     FROM orders o
                     LEFT JOIN users u ON o.username = u.username
                     ORDER BY o.created_at DESC"
                );
                $orders = $result->fetch_all(MYSQLI_ASSOC);
            }

            // Gắn items vào từng đơn hàng
            foreach ($orders as &$order) {
                $det = $conn->prepare(
                    "SELECT od.*, b.title, b.image
                     FROM order_details od
                     JOIN books b ON od.book_id = b.id
                     WHERE od.order_id = ?"
                );
                $det->bind_param("i", $order['id']);
                $det->execute();
                $order['items'] = $det->get_result()->fetch_all(MYSQLI_ASSOC);
            }

            echo json_encode($orders);
        }
        break;

    // -------------------------------------------------------
    // POST /api/orders → tạo đơn hàng mới
    // Body JSON:
    // {
    //   "username": "nguyenvana",
    //   "items": [
    //     { "book_id": 1, "quantity": 2 },
    //     { "book_id": 3, "quantity": 1 }
    //   ]
    // }
    // -------------------------------------------------------
    case 'POST':
        if (empty($body['username']) || empty($body['items']) || !is_array($body['items'])) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu username hoặc items"]);
            exit();
        }

        // Kiểm tra user tồn tại
        $check_user = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_user->bind_param("s", $body['username']);
        $check_user->execute();
        if ($check_user->get_result()->num_rows === 0) {
            http_response_code(400);
            echo json_encode(["error" => "Username không tồn tại"]);
            exit();
        }

        // Tính tổng tiền và kiểm tra từng sách
        $total = 0;
        $items = [];
        foreach ($body['items'] as $item) {
            if (empty($item['book_id']) || empty($item['quantity'])) {
                http_response_code(400);
                echo json_encode(["error" => "Mỗi item cần có book_id và quantity"]);
                exit();
            }
            $book_id  = (int)$item['book_id'];
            $quantity = (int)$item['quantity'];

            $book_stmt = $conn->prepare("SELECT id, price, quantity FROM books WHERE id = ?");
            $book_stmt->bind_param("i", $book_id);
            $book_stmt->execute();
            $book = $book_stmt->get_result()->fetch_assoc();

            if (!$book) {
                http_response_code(400);
                echo json_encode(["error" => "Sách với book_id = $book_id không tồn tại"]);
                exit();
            }
            if ($book['quantity'] < $quantity) {
                http_response_code(400);
                echo json_encode(["error" => "Sách id=$book_id không đủ tồn kho (còn {$book['quantity']})"]);
                exit();
            }

            $total += $book['price'] * $quantity;
            $items[] = ['book_id' => $book_id, 'quantity' => $quantity, 'price' => $book['price']];
        }

        // Tạo đơn hàng
        $username = $body['username'];
        $status   = 'Đang xử lý';
        $ord_stmt = $conn->prepare(
            "INSERT INTO orders (username, total_price, status, created_at) VALUES (?, ?, ?, NOW())"
        );
        $ord_stmt->bind_param("sds", $username, $total, $status);
        if (!$ord_stmt->execute()) {
            http_response_code(500);
            echo json_encode(["error" => $conn->error]);
            exit();
        }
        $order_id = $conn->insert_id;

        // Lưu chi tiết và trừ kho
        foreach ($items as $item) {
            $det_stmt = $conn->prepare(
                "INSERT INTO order_details (order_id, book_id, quantity, price) VALUES (?, ?, ?, ?)"
            );
            $det_stmt->bind_param("iiid", $order_id, $item['book_id'], $item['quantity'], $item['price']);
            $det_stmt->execute();

            $upd_stmt = $conn->prepare(
                "UPDATE books SET quantity = quantity - ?, sold = sold + ? WHERE id = ?"
            );
            $upd_stmt->bind_param("iii", $item['quantity'], $item['quantity'], $item['book_id']);
            $upd_stmt->execute();
        }

        http_response_code(201);
        echo json_encode([
            "message"     => "Tạo đơn hàng thành công",
            "order_id"    => $order_id,
            "total_price" => $total
        ]);
        break;

    // -------------------------------------------------------
    // PUT /api/orders/5 → cập nhật trạng thái đơn hàng
    // Body JSON: { "status": "Đang giao" }
    // Các trạng thái hợp lệ: Chờ xử lý | Đang giao | Đã hoàn thành | Đã hủy
    // -------------------------------------------------------
    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu ID trong URL"]);
            exit();
        }
        if (empty($body['status'])) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu trường bắt buộc: status"]);
            exit();
        }

        $valid_statuses = ['Đang xử lý', 'Chờ xử lý', 'Đang giao', 'Đã hoàn thành', 'Đã hủy'];
        if (!in_array($body['status'], $valid_statuses)) {
            http_response_code(400);
            echo json_encode([
                "error"  => "Trạng thái không hợp lệ",
                "valid"  => $valid_statuses
            ]);
            exit();
        }

        // Kiểm tra đơn hàng tồn tại
        $check = $conn->prepare("SELECT id FROM orders WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            http_response_code(404);
            echo json_encode(["error" => "Không tìm thấy đơn hàng với id = $id"]);
            exit();
        }

        $status = $body['status'];
        $stmt   = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            echo json_encode(["message" => "Cập nhật trạng thái thành công"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => $conn->error]);
        }
        break;

    // -------------------------------------------------------
    // DELETE /api/orders/5 → xóa đơn hàng (và chi tiết)
    // -------------------------------------------------------
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu ID trong URL"]);
            exit();
        }

        $check = $conn->prepare("SELECT id FROM orders WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            http_response_code(404);
            echo json_encode(["error" => "Không tìm thấy đơn hàng với id = $id"]);
            exit();
        }

        // Xóa chi tiết trước
        $del_det = $conn->prepare("DELETE FROM order_details WHERE order_id = ?");
        $del_det->bind_param("i", $id);
        $del_det->execute();

        // Xóa đơn hàng
        $del_ord = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $del_ord->bind_param("i", $id);
        if ($del_ord->execute()) {
            echo json_encode(["message" => "Xóa đơn hàng thành công"]);
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
