# Đối chiếu backend và giao diện — 11/10/2026

**Cập nhật 12/10/2026:** các backend có code nhưng chưa hoàn chỉnh đã được sửa theo yêu cầu tiếp theo. Xem `BACKEND_REPAIR_REPORT.md` để biết thay đổi và 56 kiểm tra đạt. Phần dưới ghi lại phạm vi của lượt giao diện ngày 11/10; mô tả đổi mật khẩu qua hai API đã được thay bằng việc kiểm tra mật khẩu hiện tại ngay trong API cập nhật tài khoản.

Phạm vi: đối chiếu 31 route API trong `api/index.php`, các route web và các biểu mẫu/chat cũ với giao diện Stitch đang chạy. Chỉ bổ sung liên kết, biểu mẫu và xử lý giao diện bằng JavaScript/CSS; không thêm hoặc sửa backend trong lượt này.

## Những phần vừa hoàn thiện

| Chức năng | Giao diện hiện tại | Backend được sử dụng |
|---|---|---|
| Admin tạo đơn cho khách | Nút **Tạo đơn hàng** tại `/admin/orders`: chọn khách, thêm nhiều sách, số lượng, tổng tiền; gộp sách trùng, kiểm tra tồn kho và khóa gửi lặp | `GET /api/users`, `GET /api/books`, `POST /api/orders` |
| Lọc danh mục theo thể loại | Dropdown trên máy tính và các nút thể loại thật trên mobile; kết hợp tìm kiếm, hiển thị số sách và thông báo trống | Dữ liệu từ `GET /api/books`, `GET /api/categories` |
| Ảnh bìa và xem trước sách | Chọn file hoặc kéo thả, xem trước ảnh/thông tin khi nhập, bỏ ảnh đang dùng; kiểm tra định dạng và giới hạn 4 MB trên giao diện | `POST /api/upload-image`, `POST /api/books`, `PUT /api/books/{id}` |
| Đổi mật khẩu tài khoản | Xác thực mật khẩu hiện tại bằng API đăng nhập, cập nhật bằng API tài khoản; lỗi hiện tại/mật khẩu xác nhận rõ ràng, không gửi lặp | `POST /api/auth/login`, `PUT /api/users/{id}` |
| Mở trang chat hỗ trợ trực tiếp | Vào `/public/chat.php` sẽ mở ngay khung chat | Các action chat trong `public/chat_api.php` |

Luồng đổi mật khẩu trước đây gọi biểu mẫu PHP so sánh mật khẩu dạng văn bản, không phù hợp với mật khẩu đã mã hóa. Giao diện nay dùng hai API hiện có, giữ nguyên file xử lý PHP cũ.

## Các chức năng backend đã có giao diện

| Nhóm backend | Giao diện / thao tác |
|---|---|
| Xác thực | Đăng nhập, đăng ký, đăng xuất; trạng thái tài khoản trong header. Endpoint `auth/me` là tiện ích dữ liệu phiên, không cần màn hình riêng. |
| Sách | Danh mục, tìm tên/tác giả, gợi ý tựa sách, chi tiết/xem nhanh; quản trị thêm/sửa/xóa và thông tin số lượng/đã bán. |
| Thể loại | Danh sách, thêm/sửa/xóa; chọn thể loại khi thêm/sửa sách và lọc danh mục. |
| Người dùng | Hồ sơ, cập nhật thông tin, đổi mật khẩu; admin quản lý/thêm/sửa/xóa khách hàng và đặt mật khẩu mới. |
| Đơn hàng | Khách xem danh sách/chi tiết đơn; admin tạo đơn, lọc/xem chi tiết, cập nhật trạng thái và xóa qua backend cũ. |
| Giỏ hàng | Thêm, tăng/giảm hoặc nhập số lượng, bỏ sách, xóa toàn bộ, tổng tiền và đặt hàng. Khách chưa đăng nhập được xem sách; bấm thêm sẽ chuyển đến đăng nhập. |
| Upload ảnh | Chọn/kéo thả file, xem trước và lưu bìa khi tạo/sửa sách. |
| Chat khách/admin | Danh sách hội thoại, gửi/nhận tin, cập nhật tin mới, số chưa đọc và đánh dấu đọc khi tải hội thoại bằng xử lý cũ. |

Không khôi phục chức năng tự xóa tài khoản/vùng nguy hiểm, điểm thưởng hay hội viên đã được yêu cầu gỡ. Các mục không có backend như quên mật khẩu, đánh giá, yêu thích, ISBN, vận đơn, xuất Excel và lịch sử kho vẫn được ghi trong `UI_STITCH_GAPS.md`; không bổ sung backend cho chúng.

## Kiểm tra và giới hạn

- 37 kiểm tra trình duyệt đạt: lọc thể loại, chuyển khách đến đăng nhập, đổi mật khẩu desktop/mobile, tạo đơn và giới hạn tồn kho, chọn/kéo thả/bỏ ảnh, lưu sách và mở chat trực tiếp; không có lỗi JavaScript.
- Kiểm tra bổ sung: hộp tạo đơn vừa màn hình 1366px/390px, không tràn nội dung; luồng thêm sách gửi đúng dữ liệu ở cả hai kích thước.
- Đọc sách, thể loại và khách hàng từ API thật. Các yêu cầu tạo đơn, xác thực/đổi mật khẩu, upload/lưu sách và chat trong kiểm thử được chặn bằng phản hồi giả lập: không ghi dữ liệu nghiệp vụ thật. Chưa thử giao dịch tạo đơn hoặc đổi mật khẩu thực tế trong database.
- Đối chiếu SHA-256 59 tệp PHP backend với bản chụp đầu lượt; kết quả lưu tại `.stitch-work/ui-coverage-backend-proof.json`.
- Kết quả chi tiết: `.stitch-work/ui-coverage-results.json` và `.stitch-work/ui-coverage-layout-results.json`.
- Đã đồng bộ ba asset giao diện vào `C:/xampp/htdocs/bookstore/public/assets/`. Mở `http://localhost:8888/bookstore/` và tải lại trang để dùng bản mới.
