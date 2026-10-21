# Nghiệm thu yêu cầu chỉnh sửa lần 1

Ngày nghiệm thu: 21/10/2026.

Nguồn yêu cầu: `C:/Users/Son/Downloads/Yêu cầu trỉnh sửa lần 1.docx`.
Thực hiện: 3 agent phụ trách các nhóm chức năng; agent chính tích hợp và nghiệm thu trên XAMPP.

| Yêu cầu | Kết quả |
| --- | --- |
| Đưa sách hết hàng xuống cuối | Trang chủ và danh mục ưu tiên sách còn hàng; giữ thứ tự trong từng nhóm. |
| Tiếp tục chọn sách | Nút trong giỏ hàng điều hướng về trang chủ. |
| Ghi chú đơn hàng | Ban đầu để trống, cho phép nhập và lưu theo đơn; hiển thị tại chi tiết đơn của khách và quản trị. |
| Đánh giá sách | Khách có đơn đã hoàn thành chứa sách được đánh giá 1–5 sao. Mỗi người có một đánh giá trên mỗi sách và có thể cập nhật. Hiển thị điểm trung bình và số đánh giá thực tế. |
| Chọn phương thức thanh toán | Có tiền mặt khi nhận hàng hoặc QR demo; QR xuất hiện khi chọn và cập nhật mã đơn/số tiền sau khi đặt. |
| Bỏ độ mạnh mật khẩu | Đã bỏ thanh đánh giá; vẫn kiểm tra mật khẩu tối thiểu 6 ký tự. |
| Phân trang thật | Quản lý sách và danh mục có 10 sách/trang, tổng kết quả thực tế, nút trước/sau và số trang. Lọc/tìm kiếm đặt lại trang phù hợp. |

## Kiểm tra

- 44 kiểm tra tích hợp đạt trên web XAMPP: phân trang, điều hướng, checkout, lưu ghi chú, quyền đánh giá, cập nhật điểm và QR.
- Kiểm tra dữ liệu sai, quyền khách chưa đăng nhập, người chưa mua, tài khoản quản trị và CSRF.
- Ghi chú có ký tự HTML được hiển thị an toàn, không chèn thành phần HTML vào chi tiết đơn.
- Kiểm tra giao diện giỏ hàng và đánh giá trên màn hình điện thoại; không tràn ngang. Không có lỗi JavaScript trong các luồng nghiệm thu.
- Ma trận QR demo được đối chiếu với bộ mã hóa QR của ReportLab: không có ô sai khác.
- PHP/JavaScript đã kiểm tra cú pháp. Các tệp ứng dụng của đợt sửa đã đồng bộ với `C:/xampp/htdocs/bookstore`.
- Trang chủ và API sách trả HTTP 200 sau khi dọn dữ liệu thử.

## Dữ liệu và giới hạn

Giữ nguyên cấu trúc thư mục và các luồng cũ; sửa trong những tệp hiện có. Bổ sung bảng `book_reviews` và hai trường `orders.notes`, `orders.payment_method` để lưu các chức năng được yêu cầu. Tệp `bookstore.sql` đã cập nhật cấu trúc tương ứng.

Theo xác nhận của người dùng, QR chỉ là **demo**, không phải mã chuyển khoản ngân hàng, không dùng thông tin người nhận mẫu và không tự xác nhận đã thanh toán hoặc hoàn thành đơn.

Chỉ khách có đơn **đã hoàn thành** mới được đánh giá sách trong đơn đó. Điểm được cập nhật từ dữ liệu thực tế, không dùng số đánh giá mẫu ở các thẻ sách chính.

Dữ liệu thử đã xóa. Đối chiếu toàn bộ hàng dữ liệu trước/sau cho thấy 14 sách, 3 thể loại, 15 đơn, 33 chi tiết đơn, 10 tài khoản và dữ liệu tin nhắn cũ đều nguyên vẹn. Có bản sao lưu trước sửa cơ sở dữ liệu tại `.stitch-work/revision-1/database-before.sql`.

Kết quả chi tiết: `.stitch-work/revision-1/acceptance-results.json` và `.stitch-work/revision-1/cleanup-verification.json`.

Web đang chạy: http://localhost:8888/bookstore/

## Điều chỉnh tiếp theo ngày 21/10/2026

- Đơn hoàn thành giữ cùng bố cục thẻ với đơn chờ xử lý; nút “Xóa đơn” được thay bằng “Đánh giá đơn”. Bấm nút mới mở danh sách sách để đánh giá, không hiển thị sẵn các liên kết dài dưới đơn. Áp dụng desktop và điện thoại.
- Với QR demo, nút đặt hàng chỉ mở bước xác nhận. Chỉ khi bấm “Đã chuyển khoản” mới gọi API tạo đơn; “Hủy” quay về giỏ hàng, giữ sách và không tạo đơn. Khóa thao tác khi đang gửi, cho thử lại nếu gửi lỗi. Đơn vẫn chờ xử lý, chưa xác minh giao dịch ngân hàng.
- Đạt 30 kiểm tra bổ sung bằng trình duyệt với phản hồi API mô phỏng (bao gồm hủy, thử lại khi lỗi và chống bấm trùng), đồng thời chạy lại đạt 44 kiểm tra tích hợp trên XAMPP. Dữ liệu thử đã được dọn và dữ liệu cũ được đối chiếu nguyên vẹn.

Điều chỉnh tiếp theo: “Đánh giá đơn” mở cửa sổ ngay trên trang đơn hàng, hiển thị từng sách với lựa chọn 1–5 sao và gửi/cập nhật bằng API đánh giá hiện có. Không điều hướng sang chi tiết sách. Có nút đóng, phím Escape và trả lại tiêu điểm cho nút mở. Đạt 28 kiểm tra trình duyệt với API mô phỏng trên desktop/điện thoại, gồm gửi, cập nhật, thử lại khi lỗi và mở lại điểm đã lưu. Chỉ sửa JavaScript/CSS, không đổi backend.

Bổ sung theo yêu cầu tiếp theo: ô “Nội dung đánh giá” trong cửa sổ và trang sách, tối đa 2.000 ký tự, lưu cùng số sao và điền lại khi sửa. Thêm trường `book_reviews.comment` và mở rộng API đánh giá hiện có. Kiểm tra trình duyệt mô phỏng đạt 32 mục; kiểm thử thật XAMPP đã xác nhận lưu/mở lại/cập nhật nội dung, giới hạn tiếng Việt, từ chối dữ liệu sai và người chưa mua, không chèn HTML. Dữ liệu thử đã dọn, dữ liệu gốc giữ nguyên.
