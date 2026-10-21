# Hoàn thiện backend hiện có — 12/10/2026

Giữ nguyên cấu trúc thư mục, tên file, route, controller và model. Sửa trong 32 file hiện có; không thêm chức năng kinh doanh mới như thanh toán trực tuyến, khuyến mãi, đánh giá hay nhiều kho.

| Phần | Thay đổi |
|---|---|
| Chat | Tạo bảng `messages` còn thiếu theo định nghĩa đã có trong `bookstore.sql`, gồm khóa chính tự tăng. Gửi/nhận và đánh dấu đọc đã được kiểm thử với dữ liệu thật riêng. Không thay đổi các bảng khác. |
| Trạng thái đơn | Kiểm tra chuyển trạng thái; đơn hủy/hoàn thành không được mở lại. Hủy đơn hoàn lượng tồn và điều chỉnh số đã bán trong transaction, khóa dòng đơn để không hoàn hai lần. |
| Xóa đơn | Kiểm tra quyền sở hữu trước mọi thay đổi. Khách chỉ xóa đơn chờ/xử lý/hủy; giao diện khóa nút xóa đơn đang giao/hoàn thành. Admin xóa đơn chưa giao xong thì hoàn kho; đơn hủy không hoàn lần hai; đơn hoàn thành không đưa sách đã bán trở lại kho. |
| Xử lý cũ | Các đường xóa/cập nhật đơn PHP cũ dùng model hiện có để thống nhất transaction và hoàn kho. Xóa/thêm giỏ/checkout qua các đường cũ yêu cầu POST. |
| Phân quyền | Kiểm tra admin ở các entry point PHP cũ trước thao tác; chặn gọi trực tiếp các file API cũ không còn là entry point hợp lệ. REST API đang dùng giữ nguyên. |
| Mật khẩu | Backend yêu cầu mật khẩu hiện tại khi tự đổi mật khẩu; admin vẫn đặt mật khẩu mới cho khách khác. Kiểm tra độ dài trước cập nhật hồ sơ; các đường đăng ký/đăng nhập/đổi mật khẩu cũ dùng mã hóa và model hiện có. Đổi ID phiên khi đăng nhập thành công. |
| Dữ liệu | Chặn giá không hợp lệ, tên/tác giả trống, lượng tồn hoặc số đã bán âm/lẻ; giỏ và đơn nhận số lượng nguyên, kiểm tra kho. Các đường thêm/sửa khách và thêm sách PHP cũ dùng model/prepared statements. |
| Ảnh | Backend kiểm tra nội dung thực qua MIME và kích thước ảnh, khớp đuôi file, giới hạn 4 MB; tên file ngẫu nhiên. Áp dụng cho API và thêm sách PHP cũ. |
| CSRF | Token phiên được xác nhận ở backend cho các yêu cầu thay đổi. Giao diện gửi token qua header hoặc biểu mẫu; chat cũng gửi token. |
| Thử đăng nhập | Giới hạn 10 lần sai trong 15 phút theo IP, lưu trạng thái với khóa file để đổi session không bỏ qua giới hạn. |
| Đơn lặp | Gộp sách trùng trong đơn. Cùng tài khoản/cùng nội dung đơn gửi lại trong 5 giây ở cùng phiên dùng lại kết quả, không tạo thêm đơn. Checkout sau thành công xóa giỏ nên gửi lại không tạo đơn khác. |

## Kiểm chứng

- 54 kiểm tra tích hợp đạt: quyền truy cập, CSRF, số lượng/giá, đặt đơn thật, chống lặp, hoàn kho và số đã bán, quyền sở hữu, trạng thái hoàn thành, checkout, đổi mật khẩu qua API và giao diện, chat thật, ảnh giả/ảnh quá lớn, mở các trang chính và không có lỗi JavaScript.
- Hai kiểm tra bổ sung đạt: giới hạn đăng nhập vẫn có hiệu lực khi đổi phiên và upload ảnh PNG thực tế.
- Kiểm tra cú pháp toàn bộ PHP đã sửa và JavaScript đạt. 32 file khớp bản chạy XAMPP; trang chủ HTTP 200.
- Tài khoản, thể loại, sách, đơn, tin nhắn và ảnh kiểm thử được tạo riêng, sau đó đã xóa; các phiên kiểm thử cũng được dọn. Không thay đổi dữ liệu sách/tài khoản/đơn có trước.
- Kết quả chính nằm tại `.stitch-work/backend-repair-results.json`; bản sao file trước sửa tại `.stitch-work/backend-repair-backup/`.

## Giới hạn

- Các quy tắc hoàn kho áp dụng cho thao tác tiếp theo. Không tự điều chỉnh lại tồn kho của các đơn đã hủy/xóa trước bản sửa vì không đủ lịch sử để xác định số lượng cần bù.
- Chống đơn lặp hiện áp dụng trong cùng phiên và cửa sổ 5 giây, không phải hệ thống idempotency lưu database cho mọi thiết bị.
- Không thêm các backend chưa có như vận đơn, thanh toán, mã giảm giá, phục hồi mật khẩu hay nhật ký nghiệp vụ.
- Client gọi API bên ngoài giao diện phải tải phiên và gửi `X-CSRF-Token`; thao tác GET xóa dữ liệu cũ không còn được chấp nhận.

Ứng dụng: `http://localhost:8888/bookstore/`. Tải lại trang để nhận token và asset mới.
