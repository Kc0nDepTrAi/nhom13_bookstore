# Chạy với XAMPP

- Trang chủ: http://localhost:8888/bookstore/
- Đăng nhập: http://localhost:8888/bookstore/login
- Quản trị: http://localhost:8888/bookstore/admin
- XAMPP đang dùng Apache cổng 8888, MySQL cổng 3306.
- Bản đang phục vụ nằm tại `C:/xampp/htdocs/bookstore`. Bản phát triển nằm tại `D:/SÁCH/bookstore123`; khi sửa tiếp cần đồng bộ các file thay đổi vào thư mục XAMPP.
- Database: `bookstore_db`, sử dụng database có sẵn, không nhập lại SQL hay thay đổi dữ liệu sách/đơn hàng.
- Bản XAMPP trước khi đồng bộ được sao lưu tại `C:/xampp/tmp/bookstore-backup-20261009-231204/bookstore`.

Đã kiểm tra HTTP 200 cho trang chủ, đăng nhập, API sách/thể loại/giỏ hàng và CSS; Chrome đọc 14 sách từ API thật, mở chi tiết sách, thêm và xóa giỏ trong phiên thử riêng, chuyển admin chưa đăng nhập tới login, trang đăng ký và trang chủ PHP trực tiếp. Không có lỗi JavaScript, không thiếu ảnh bìa, không tràn ngang trên trang chủ và giỏ hàng ở 390px. Chưa thử đặt đơn hoặc đăng nhập bằng tài khoản người dùng thật.

Cấu hình database mặc định đã chuyển sang XAMPP và có thể ghi đè bằng biến môi trường DB_HOST, DB_NAME, DB_USER, DB_PASS. Docker Compose truyền các giá trị Docker cũ để giữ cấu hình đó.
