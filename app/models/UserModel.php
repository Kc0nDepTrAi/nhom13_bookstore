<?php
namespace App\Models;

use App\Core\Model;

/**
 * UserModel — Quản lý bảng `users`
 * Columns: id, username, password, fullname, phone, address, email, role
 */
class UserModel extends Model
{
    protected string $table = 'users';

    /** Tìm user theo username */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `users` WHERE `username` = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    /** Kiểm tra username đã tồn tại */
    public function usernameExists(string $username): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM `users` WHERE `username` = ?");
        $stmt->execute([$username]);
        return $stmt->fetchColumn() !== false;
    }

    /**
     * Đăng ký người dùng mới — mật khẩu được hash bằng password_hash()
     */
    public function register(array $data): int
    {
        return $this->create([
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'fullname' => $data['fullname'] ?? '',
            'phone'    => $data['phone']    ?? '',
            'address'  => $data['address']  ?? '',
            'email'    => $data['email']    ?? '',
            'role'     => 'user',
        ]);
    }

    /**
     * Kiểm tra thông tin đăng nhập.
     * Hỗ trợ cả password_hash (mới) và plain text (cũ, để tương thích).
     */
    public function verifyLogin(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);
        if (!$user) return null;

        // Mật khẩu đã được hash
        if (str_starts_with($user['password'], '$2y$') || str_starts_with($user['password'], '$argon')) {
            return password_verify($password, $user['password']) ? $user : null;
        }

        // Legacy: plain text (sẽ tự động migrate khi login thành công)
        if ($user['password'] === $password) {
            // Upgrade password hash tự động
            $this->update((int)$user['id'], [
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]);
            return $user;
        }
        return null;
    }

    /** Lấy danh sách khách hàng (role = 'user') */
    public function getCustomers(): array
    {
        $stmt = $this->db->prepare(
            "SELECT `id`, `username`, `fullname`, `phone`, `address`, `email`, `role`
             FROM `users`
             WHERE `role` = 'user'
             ORDER BY `id` DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Lấy danh sách tất cả users (kể cả admin), không trả password */
    public function getAllSafe(): array
    {
        $stmt = $this->db->prepare(
            "SELECT `id`, `username`, `fullname`, `phone`, `address`, `email`, `role`
             FROM `users`
             ORDER BY `id` DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Lấy một user không có password */
    public function findByIdSafe(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT `id`, `username`, `fullname`, `phone`, `address`, `email`, `role`
             FROM `users` WHERE `id` = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Cập nhật thông tin profile (không đổi password) */
    public function updateProfile(int $id, array $data): bool
    {
        $allowed = ['fullname', 'phone', 'address', 'email'];
        $update  = array_intersect_key($data, array_flip($allowed));
        if (empty($update)) return false;
        return $this->update($id, $update);
    }

    /** Đổi mật khẩu */
    public function changePassword(int $id, string $newPassword): bool
    {
        return $this->update($id, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }
}
