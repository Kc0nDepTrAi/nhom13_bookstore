# Đối chiếu giao diện Stitch mới

Nguồn: `C:/Users/Son/Downloads/stitch_thi_t_k_ui_ux_bookstore123 (1)/stitch_thi_t_k_ui_ux_bookstore123/`.

Đã nhập nguyên bộ 17 màn hình vào `design/stitch-v2/` để giữ bản gốc. Các trang sử dụng HTML của bản xuất này qua `app/views/stitch/templates/`; chỉ thay dữ liệu mẫu, liên kết và sự kiện giao diện để gọi API/biểu mẫu hiện có. Không bổ sung controller, API, model, bảng dữ liệu hay chức năng backend trong lần thay đổi này.

## Các chức năng còn thiếu so với thiết kế

Các mục dưới đây vẫn còn thiếu so với thiết kế hoặc chưa có backend tương ứng. Chức năng có backend đã được nối thêm vào giao diện được ghi tại `BACKEND_UI_COVERAGE.md`.

| Nhóm / màn hình | Chức năng trong Stitch còn thiếu | Đối chiếu với hệ thống hiện tại |
|---|---|---|
| Đăng nhập | Đăng nhập bằng email hoặc số điện thoại | API xác thực hiện nhận tên đăng nhập; chưa tra tài khoản theo email/điện thoại. |
| Đăng nhập | Google, Apple ID | Chưa có luồng đăng nhập qua nhà cung cấp ngoài. |
| Đăng nhập | Quên mật khẩu, ghi nhớ đăng nhập dài hạn theo thiết bị | Chưa có khôi phục mật khẩu hay cơ chế phiên ghi nhớ riêng. |
| Danh mục, chi tiết sách | ISBN/SKU chuẩn; dịch giả, nhà xuất bản, năm xuất bản, số trang, kích thước, loại bìa, ngôn ngữ và thông tin ấn bản | Dữ liệu sách hiện có tên, tác giả, mô tả, giá, thể loại, ảnh, số lượng và số đã bán. |
| Danh mục | Lọc nâng cao theo khoảng giá, nhà xuất bản, tình trạng và tiêu chí ấn bản; xếp hạng phổ biến/đánh giá/mới xuất bản | Luồng tìm kiếm cũ hỗ trợ tên sách/tác giả và thể loại; chưa có các tiêu chí này. Các điều khiển nâng cao chưa được nối. |
| Danh mục | Chế độ danh sách, phân trang và các tab tuyển tập theo thiết kế | Chưa nối các điều khiển này với danh mục thực tế. |
| Chi tiết sách | Đọc thử chương, so sánh phiên bản, lưu sách yêu thích | Chưa có dữ liệu chương đọc thử, quan hệ các ấn bản hoặc danh sách yêu thích. |
| Chi tiết sách | Viết cảm nhận, sao đánh giá, thống kê đánh giá và gợi ý sách đồng điệu | Chưa có dữ liệu đánh giá hoặc cơ chế gợi ý tác phẩm. |
| Khách hàng | Lọc theo trạng thái hoạt động | Hồ sơ hiện chưa có trường trạng thái hoạt động tương ứng. |
| Giỏ hàng | Địa chỉ, tên và điện thoại nhận hàng riêng cho từng đơn; ghi chú giao hàng | Checkout cũ sử dụng tài khoản; đơn chỉ lưu username, tổng tiền, trạng thái, thời gian. Chưa lưu thông tin giao nhận riêng và ghi chú. |
| Giỏ hàng | Ưu đãi đơn đầu, tính phí vận chuyển, thuế/phụ thu và xác nhận thanh toán COD | Backend hiện tính tổng tiền sách, chưa có các khoản này hay trạng thái thanh toán riêng. |
| Thể loại | Mô tả thể loại | CRUD thể loại hiện chỉ lưu tên. |
| Quản trị sách | Xuất Excel, tìm theo ISBN, lịch sử chỉnh sửa và người chỉnh sửa | Chưa có đầu nối xuất file, trường ISBN hoặc nhật ký chỉnh sửa. |
| Tồn kho | Sức chứa kho, nhiều kho, theo dõi nhập/xuất, thống kê thay đổi theo tuần và đồng bộ thời gian thực như thiết kế | Backend hiện quản lý quantity/sold trên từng sách; chưa có dữ liệu nghiệp vụ kho và nhật ký này. |
| Chi tiết đơn | Timeline luân chuyển, xuất phiếu kho, vận đơn/hóa đơn nghiệp vụ, đóng gói, giao bưu tá, mã vận chuyển và phân kho | Hiện có trạng thái đơn; chưa có sự kiện xử lý, mã vận chuyển hoặc chứng từ tương ứng. Nút in trình duyệt không thay thế chứng từ nghiệp vụ. |
| Chat hỗ trợ | Tìm cuộc hội thoại theo tên/số điện thoại/mã đơn, gắn chat với hồ sơ đơn và trạng thái nhân viên trực tuyến | Chat cũ có gửi/nhận/đánh dấu đọc, chưa cung cấp các dữ liệu liên kết này. |
| Toàn site | Bản tin email, trang chính sách vận chuyển/đổi trả/bảo mật/điều khoản, hướng dẫn mua hàng và liên hệ nhà xuất bản | Chưa có trang/luồng xử lý tương ứng cho các liên kết của bản thiết kế. |

## Ánh xạ các màn hình

| Trang đang chạy | Mẫu Stitch sử dụng |
|---|---|
| `/`, `/public/index.php` | Trang chủ |
| `/?search=...`, `/?category_id=...` | Danh mục / tìm kiếm; dùng mẫu danh mục mobile khi trình duyệt mobile |
| `/?book_id=...` | Chi tiết sách |
| `/login`, `/register` và các trang đăng nhập/đăng ký trực tiếp | Đăng nhập, đăng ký độc giả riêng |
| `/cart`, `/public/cart/view_cart.php` | Giỏ hàng / đặt hàng |
| `/profile`, `/my-orders` và các trang trực tiếp tương ứng | Hồ sơ / đơn hàng; dùng mẫu tài khoản mobile khi trình duyệt mobile |
| `/admin`, `/admin/admin_dashboard.php` | Quản trị sách |
| `/admin/add_book.php`, `/admin/book_detail.php?id=...` | Thêm / sửa / chi tiết sách |
| `/admin/categories`, `/admin/categories.php`, `/admin/manage_inventory.php` | Thể loại / tồn kho |
| `/admin/customers` và các trang quản lý/thêm/sửa khách hàng trực tiếp | Quản lý khách hàng |
| `/admin/orders`, `/admin/manage_orders.php` | Quản trị đơn hàng / khách hàng |
| `/admin/manage_orders.php?id=...` | Chi tiết / xử lý đơn riêng |
| `/admin/admin_chat.php` | Chat quản trị |
| `/public/chat.php` | Danh mục / chat hỗ trợ mobile |

Mẫu đăng nhập/đăng ký kết hợp được giữ trong kho mẫu; luồng chính sử dụng hai màn hình riêng trong cùng bộ thiết kế.

## Bổ sung giao diện cho backend hiện có — 11/10/2026

Đã thêm tạo đơn cho khách ở trang quản trị, lọc thể loại thật trên danh mục desktop/mobile, kéo thả và xem trước ảnh/thông tin sách, mở ngay khung chat ở trang chat trực tiếp. Đổi mật khẩu nay gọi API xác thực và cập nhật tài khoản sẵn có để làm việc với mật khẩu đã mã hóa. Xem phạm vi và kết quả kiểm thử trong `BACKEND_UI_COVERAGE.md`. Không thêm hoặc sửa backend trong lượt này.

## Phạm vi kiểm tra

- XAMPP tại `http://localhost:8888/bookstore/`; trang chính lấy đủ 14 sách từ API thực tế.
- Kiểm tra HTTP, lỗi JavaScript, tìm kiếm, lọc tồn kho, mở/đóng xem nhanh, biểu mẫu sách/thể loại/khách hàng.
- Kiểm tra màn hình đơn và trạng thái với dữ liệu thử trong trình duyệt; các yêu cầu lưu/xóa được chặn, không ghi vào database.
- Kiểm tra mobile 390px: trang chủ, danh mục, hồ sơ, đơn mua, giỏ hàng và chat.
- So sánh SHA-256 34 tệp backend và so sánh phần PHP xử lý trước HTML với bản chụp đầu lượt: không thay đổi; kiểm tra cú pháp PHP không có lỗi.
- Chat được kiểm tra giao diện với phản hồi giả lập để không đánh dấu đọc hay gửi tin nhắn thật. Chưa xác nhận toàn bộ luồng chat/checkout/lưu/xóa với thao tác ghi trên database thật.
- Font, Tailwind và ảnh trang trí giữ các URL từ bản Stitch; cần kết nối mạng để tải các tài nguyên ngoài này.


## Chức năng đã khôi phục từ code cũ — 10/10/2026

Các phần dưới đây đã được nối vào giao diện Stitch bằng endpoint sẵn có; không bổ sung backend.

| Chức năng | Kết quả |
|---|---|
| Gợi ý tựa sách | Gọi API autocomplete, hỗ trợ chuột và phím lên/xuống/Enter/Escape. |
| Xóa toàn bộ giỏ | Nút xóa cả giỏ, có xác nhận; cập nhật tổng tiền và số lượng ở header. |
| Nhập số lượng trực tiếp | Ô số nguyên; cập nhật qua API cũ, hiển thị lỗi nếu vượt kho và nạp lại số lượng hợp lệ. |
| Chat tự cập nhật | Khách và admin tải tin mới mỗi 3 giây khi trang hiện; tránh trùng tin và tránh yêu cầu chồng nhau. |
| Tin chưa đọc | Badge cập nhật mỗi 5 giây ở các trang khác; màn chat admin cập nhật theo lượt tải cuộc hội thoại. |
| Chat trang chủ | Popup gửi/nhận tin; chỉ tải nội dung khi mở, yêu cầu đăng nhập nếu chưa có phiên. |
| Trạng thái đặt hàng | Khóa nút và thao tác giỏ trong lúc gửi; chặn yêu cầu lặp, mở lại khi có lỗi. |

Kiểm thử giỏ hàng dùng phiên riêng. Kiểm thử chat và checkout dùng phản hồi chặn trong trình duyệt, không gửi tin nhắn hoặc tạo đơn thật. Danh sách chức năng chưa có backend trong bảng trên vẫn giữ nguyên.

## Đã gỡ theo yêu cầu — 10/10/2026

Đã gỡ điểm thưởng/tích lũy, cấp hội viên, đặc quyền và giảm giá hội viên khỏi các mẫu giao diện đang chạy. Đã bỏ cột cấp hội viên, bộ lọc thẻ hội viên, thẻ giới thiệu chương trình và đổi nhãn điều hướng thành Quản lý khách hàng. Các phần này không còn nằm trong phạm vi chức năng cần bổ sung.

Giữ nguyên đăng nhập, đăng ký, hồ sơ tài khoản, quản lý khách hàng và các endpoint hiện có. Không thay đổi backend hoặc database; bản Stitch gốc trong design/stitch-v2 vẫn được giữ để đối chiếu.

Đã cập nhật bản chạy XAMPP. Kiểm tra bảng khách hàng còn 6 cột, hiển thị 9 khách hàng thực tế và mở được hộp sửa thông tin. Kiểm tra đăng nhập/đăng ký/giỏ hàng trên mobile không còn nội dung hội viên/điểm thưởng, không tràn ngang và không có lỗi JavaScript.
