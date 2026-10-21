# Ghép trực tiếp thiết kế Stitch

Đã dùng HTML/CSS từ bộ xuất người dùng cung cấp, không vẽ lại sidebar và modal theo phong cách.

## Nguồn gốc và chỗ sửa

- `design/stitch-admin/code.html`, `DESIGN.md`, `screen.png`: bản gốc nguyên trạng để đối chiếu.
- `app/views/layout/stitch-head.php`: head, font, Material Symbols và Tailwind config lấy trực tiếp từ code.html.
- `app/views/layout/admin-shell.php`: nguyên cấu trúc sidebar/header gốc; thay đường dẫn liên kết, mục đang chọn và tên người đăng nhập.
- `app/views/layout/stitch-delete-modal.php`: cấu trúc/class đầy đủ của modal gốc; thay dữ liệu mẫu bằng vị trí dữ liệu thật.
- `app/views/layout/admin-shell-end.php`: đóng khung và nạp template modal.
- `app/views/admin/dashboard.php`: lưới 12 cột theo bố cục danh sách của file gốc, nối sách thật và giữ các thao tác tìm/thêm/sửa/xóa.
- `public/assets/bookstore.js`: nối modal gốc với hàm xóa hiện có; xác minh mã sách hoặc cụm từ, checkbox, Hủy/Esc, trả focus và menu mobile.
- `public/assets/stitch-integration.css`: chỉ các điều chỉnh để ghép khung, tránh CSS cũ đè lên thành phần gốc, và bổ sung mobile.
- Các trang PHP trực tiếp dùng cùng tên phiên `bookstore_sess` để chuyển qua lại với bộ router.

## Các phần chưa có thiết kế riêng

File xuất chỉ có màn hình quản trị với modal xóa. Trang khách hàng, đăng nhập/đăng ký, giỏ hàng và nội dung của các màn hình quản trị khác vẫn giữ phần triển khai trước; khu quản trị dùng khung Stitch gốc. Bổ sung thiết kế riêng cho các màn hình này sau.

Các thông tin mẫu thùng rác 30 ngày, ISBN, NXB, CDN, IP và audit log được thay bằng thông tin hiện có. Không bổ sung cơ chế thùng rác hay nhật ký kiểm toán. Không sử dụng script giả lập xóa thành công trong bản xuất; thao tác được nối với backend hiện có.

Bản xuất gốc dùng Tailwind CDN và Google Fonts; bản ghép giữ các phụ thuộc này.

## Chạy và kiểm tra

Đã đồng bộ vào `C:/xampp/htdocs/bookstore`.

- URL: http://localhost:8888/bookstore/admin (cần đăng nhập bằng tài khoản quản trị).
- PHP lint toàn app/api/admin/public và JavaScript syntax: qua.
- Chrome: khung/lưới/modal gốc, 14 sách thật, bốn trang router và các trang quản trị PHP trực tiếp, mở modal sửa, xác minh xóa và menu mobile 390px: qua.
- Yêu cầu DELETE được chặn trong kiểm thử; database vẫn có 14 sách. Không xóa sách hoặc tạo tài khoản/đơn hàng thật.
- Phiên quản trị thử tạm được dọn sau kiểm tra, không dùng để đăng nhập cho người dùng.
