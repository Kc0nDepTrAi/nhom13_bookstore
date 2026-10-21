> Cập nhật sau: khu quản trị đã chuyển sang ghép HTML Stitch trực tiếp; xem STITCH_INTEGRATION.md. Nội dung bên dưới ghi lại lần áp dụng quy chuẩn ban đầu.

# Cập nhật giao diện BOOKSTORE123

Nguồn tham chiếu: `C:/Users/Son/Downloads/stitch_thi_t_k_ui_ux_bookstore123/` (DESIGN.md, code.html, screen.png).

Bộ xuất chỉ có một màn hình quản trị với modal xóa sách. Áp dụng quy chuẩn màu, Merriweather/Be Vietnam Pro, bảng, biểu mẫu và khoảng cách cho cả giao diện router và PHP trực tiếp; phần còn lại được điều chỉnh từ bố cục hiện có, không phải các màn hình Stitch riêng.

## Thay đổi

- `public/assets/bookstore.css`: design system và bố cục desktop/mobile.
- `public/assets/bookstore.js`: modal xác nhận xóa sách và menu quản trị mobile.
- `app/views/layout/design-assets.php`: dùng chung CSS/JS cho hai bộ giao diện.
- `app/views/layout/admin-shell.php`: sidebar quản trị, điều hướng đúng bộ trang, tên người dùng phiên đăng nhập và trạng thái trang đang mở.
- Các trang HTML trong `app/views`, `admin`, `public`: nạp giao diện mới. Bảng có vùng cuộn riêng; giữ biểu mẫu và dữ liệu động.
- Modal xóa dùng mã BK từ ID thực, tên/bìa sách được chọn, yêu cầu nhập đúng mã và đánh dấu xác nhận; hỗ trợ Hủy/Esc và trả focus. Nối với API DELETE của bộ router và đường dẫn xóa sách hiện có của bộ PHP trực tiếp.
- `api/index.php`: sửa autoload để tìm các lớp theo cấu trúc thư mục hiện có, giống entry web.
- `app/controllers/api/CartApiController.php`: sửa khai báo phương thức add bị hỏng sẵn, giúp API thêm giỏ phân tích cú pháp được.

Không đưa dữ liệu mẫu về ISBN, nhà xuất bản, thùng rác 30 ngày, CDN, IP, quyền Tổng Quản trị hay audit-log vào sản phẩm vì các tính năng này chưa có. Các file trùng `app/app/controllers/api/*` đã bị xóa trước lần cập nhật được giữ nguyên trạng thái đó.

## Kiểm tra

- PHP lint: toàn bộ app, api, admin, public đều qua.
- JavaScript syntax: qua.
- Autoload: Router, Request và cả sáu API controller đều tải được.
- Chromium: desktop 1440px và mobile 390px; trang chủ, đăng nhập/đăng ký, bốn trang quản trị; không có lỗi JavaScript và không tràn ngang trên các trang mobile đã thử.
- Kiểm tra modal thêm sách, xác nhận xóa, chặn mã sai, yêu cầu checkbox, Hủy/Esc không gửi DELETE, xác nhận hợp lệ gửi một DELETE, menu mobile và căn giữa dialog. Các API trình duyệt dùng dữ liệu mô phỏng.
- Phương thức thêm giỏ thực chạy với model giả lập: tính số lượng/tổng tiền đúng và chặn vượt tồn kho.

Chưa kiểm tra kết nối cơ sở dữ liệu và luồng mua hàng với dữ liệu thật trong môi trường triển khai. Không thay đổi dữ liệu thật.

