<?php
// api/customers.php — CRUD khách hàng
// Bảng: users (id, fullname, phone, address, username, password, role)
// Chỉ thao tác với role = 'user', không lộ password ra ngoài

require_once __DIR__ . '/../db/db_connect.php';

$body = json_decode(file_get_contents("php://input"), true) ?? [];

switch ($method) {

    // -------------------------------------------------------
    // GET /api/customers       → danh sách khách hàng
    // GET /api/customers/5     → chi tiết 1 khách hàng
    // -------------------------------------------------------
    case 'GET':
        if ($id) {
            $stmt = $conn->prepare(
                "SELECT id, fullname, phone, address, username, email, role
                 FROM users
                 WHERE id = ? AND role = 'user'"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();

            if ($row) {
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Không tìm thấy khách hàng với id = $id"]);
            }
        } else {
            $result = $conn->query(
                "SELECT id, fullname, phone, address, username, email, role
                 FROM users
                 WHERE role = 'user'
                 ORDER BY id DESC"
            );
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        }
        break;

    // -------------------------------------------------------
    // POST /api/customers → thêm khách hàng mới
    // Body JSON: { fullname, phone, address, username, password }
    // -------------------------------------------------------
    case 'POST':
        $required = ['fullname', 'username', 'password'];
        foreach ($required as $field) {
            if (empty($body[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Thiếu trường bắt buộc: $field"]);
                exit();
            }
        }

        // Kiểm tra username đã tồn tại chưa
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $body['username']);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            http_response_code(409);
            echo json_encode(["error" => "Username '{$body['username']}' đã được sử dụng"]);
            exit();
        }

        $fullname = $body['fullname'];
        $phone    = $body['phone']    ?? '';
        $address  = $body['address']  ?? '';
        $email    = $body['email']    ?? '';
        $username = $body['username'];
        $password = password_hash($body['password'], PASSWORD_DEFAULT);
        $role     = 'user';

        $stmt = $conn->prepare(
            "INSERT INTO users (fullname, phone, address, email, username, password, role)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssssss", $fullname, $phone, $address, $email, $username, $password, $role);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Thêm khách hàng thành công", "id" => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => $conn->error]);
        }
        break;

    // -------------------------------------------------------
    // PUT /api/customers/5 → cập nhật thông tin khách hàng
    // Body JSON: { fullname, phone, address, password }  (tất cả đều optional)
    // -------------------------------------------------------
    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu ID trong URL"]);
            exit();
        }

        // Kiểm tra tồn tại
        $check = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'user'");
        $check->bind_param("i", $id);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            http_response_code(404);
            echo json_encode(["error" => "Không tìm thấy khách hàng với id = $id"]);
            exit();
        }

        $fullname = $body['fullname'] ?? null;
        $phone    = $body['phone']    ?? null;
        $address  = $body['address']  ?? null;
        $email    = $body['email']    ?? null;

        // Hash password mới nếu có
        $password = null;
        if (!empty($body['password'])) {
            $password = password_hash($body['password'], PASSWORD_DEFAULT);
        }

        $stmt = $conn->prepare(
            "UPDATE users
             SET fullname = COALESCE(?, fullname),
                 phone    = COALESCE(?, phone),
                 address  = COALESCE(?, address),
                 email    = COALESCE(?, email),
                 password = COALESCE(?, password)
             WHERE id = ? AND role = 'user'"
        );
        $stmt->bind_param("sssssi", $fullname, $phone, $address, $email, $password, $id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Cập nhật khách hàng thành công"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => $conn->error]);
        }
        break;

    // -------------------------------------------------------
    // DELETE /api/customers/5 → xóa khách hàng
    // -------------------------------------------------------
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu ID trong URL"]);
            exit();
        }

        $check = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'user'");
        $check->bind_param("i", $id);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            http_response_code(404);
            echo json_encode(["error" => "Không tìm thấy khách hàng với id = $id"]);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(["message" => "Xóa khách hàng thành công"]);
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
