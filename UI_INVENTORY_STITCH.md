# BOOKSTORE123 — Hiện trạng giao diện và brief cho Stitch AI

Ngày rà soát: 09/10/2026. Căn cứ: mã nguồn route, controller, view, các trang PHP trực tiếp, API và cấu trúc dữ liệu. Đây là mô tả từ mã nguồn, chưa phải ảnh chụp website đang chạy. Không sửa giao diện hay nghiệp vụ trong lần rà soát này.

## 1. Cấu trúc giao diện hiện có

Dự án có hai bộ giao diện cùng tồn tại:

- **Bộ theo router:** entry `index.php`, giao diện trong `app/views/`, PHP + Vue 3 + REST API; đường dẫn `/`, `/login`, `/register`, `/cart`, `/profile`, `/my-orders`, `/admin`, `/admin/categories`, `/admin/orders`, `/admin/customers`.
- **Bộ PHP trực tiếp:** các file trong `public/` và `admin/`, dùng CSS tự viết, Bootstrap hoặc jQuery. Có những chức năng riêng chưa được đưa vào bộ router: quản lý kho, chi tiết sách của admin, xóa tài khoản, xóa đơn của khách hàng và hai trang chat.
- `.htaccess` vẫn cho truy cập file PHP thực có, nên không nên bỏ qua bộ trực tiếp khi thiết kế lại. Chưa có bằng chứng môi trường triển khai ưu tiên bộ nào.
- Đường dẫn trong tài liệu là tương đối theo gốc ứng dụng; cấu hình rewrite hiện dùng tiền tố `/bookstore/`.

### Phong cách đang dùng

- Thương hiệu trên giao diện: “Shop của tôi”, “Shop”, “Shop Admin”; chưa thống nhất tên BOOKSTORE123.
- Màu chủ đạo: xanh mint `#A8E6CF`; màu tiêu đề/nút chính: xanh xám `#4A708B`.
- Nền khách hàng: xám xanh rất nhạt `#f4f7f6`; card trắng, bo góc khoảng 8–16px và bóng nhẹ.
- Giá tiền thường đỏ `#d9534f`; thành công xanh lá; lỗi/xóa đỏ; nút sửa xanh cyan.
- Font chủ yếu Segoe UI/Arial; icon chủ yếu emoji. Những trang Bootstrap sử dụng kiểu chữ và thành phần của Bootstrap.
- Admin phần lớn là bảng đầu đen, ô xanh nhạt; chưa có sidebar thống nhất.
- Chat dùng gradient tím và xanh dương, khác phong cách phần bán sách. Danh mục PHP trực tiếp dùng header xám đen.
- Trang chủ có grid co giãn; đa số navbar, bảng và form chưa có bố cục mobile đầy đủ. Hai trang chat có breakpoint riêng.
- Bộ layout khách hàng mới chưa có footer nội dung; `layout/footer.php` chủ yếu chứa nút chat và mã kiểm tra tin chưa đọc.

## 2. Prompt nền — gửi trước cho Stitch

```text
Thiết kế lại UI/UX cho BOOKSTORE123, một website bán sách bằng tiếng Việt có khu khách hàng và khu quản trị. Mục tiêu là nâng cấp toàn bộ giao diện, giữ các dữ liệu, thao tác và luồng hiện có theo brief từng màn hình bên dưới.

Xây dựng một design system thống nhất: font dễ đọc, khoảng cách rõ ràng, card, input, button, bảng, badge trạng thái, modal và thông báo. Đề xuất diện mạo nhà sách hiện đại, có cá tính; có thể phát triển từ màu xanh mint hiện tại. Dùng BOOKSTORE123 làm tên thương hiệu thống nhất trong bản đề xuất.

Vẽ bản desktop 1440px và mobile 390px. Khu khách hàng ưu tiên khám phá sách và mua hàng; khu admin ưu tiên đọc dữ liệu và thao tác nhanh. Admin có thể dùng sidebar thống nhất chứa Sách, Thể loại, Đơn hàng, Tồn kho, Khách hàng, Chat. Sidebar là đề xuất thiết kế mới, không phải bố cục hiện tại.

Mỗi màn hình cần có trạng thái phù hợp: mặc định, có dữ liệu, trống, đang tải, lỗi, thành công, nút bị vô hiệu hóa. Modal cần có trạng thái mở và xác nhận thao tác nếu brief đề cập.

Không tự thêm nghiệp vụ như voucher, đánh giá, yêu thích, thanh toán online, phí vận chuyển, OTP, đăng nhập mạng xã hội hoặc dashboard doanh thu. Nếu đề xuất thêm, đánh dấu rõ là tính năng mở rộng. Với chức năng chỉ có ở bộ PHP trực tiếp, giữ riêng trong bản thiết kế và ghi chú để đội phát triển quyết định tích hợp.

Thiết kế từng màn hình một theo các brief sau. Sử dụng dữ liệu mẫu tiếng Việt, giá VNĐ và ảnh bìa sách; không sử dụng thông tin tài khoản thực.
```

## 3. Từng giao diện khách hàng

### 01 — Trang chủ / danh sách sách

**Nguồn:** `app/views/home/index.php`, `app/views/layout/header.php`; bản trực tiếp `public/index.php`.

**Đường dẫn:** `/` hoặc `/public/index.php`.

**Hiện tại:** navbar ngang, dính trên đầu; trái là logo và menu danh mục, phải là ô tìm sách, giỏ hàng và tài khoản. Chưa đăng nhập thì bộ router có Đăng nhập/Đăng ký; bản trực tiếp chỉ có liên kết Đăng nhập. Khi đăng nhập, menu có hồ sơ, đơn hàng, đăng xuất; bộ router thêm liên kết Admin Panel cho admin.

Phía dưới là tiêu đề “Sách mới nhất”, đường kẻ mint và grid sách. Bộ router có vùng nội dung tối đa 1400px, card tối thiểu 230px, ảnh cao 210px. Mỗi card gồm bìa, tên, tác giả, giá VNĐ, số lượng còn hoặc Hết hàng, nút Thêm vào giỏ. Bộ trực tiếp có ảnh cao 220px, card tối thiểu 240px và không hiện tác giả trên card. Chưa có hero/banner, phân trang, sắp xếp, đánh giá hoặc yêu thích. Có nút chat nổi góc phải dưới.

**Trạng thái hiện có:** bộ router có skeleton 8 card, danh sách trống, nút Đang thêm, hết hàng bị vô hiệu hóa và toast. Bản trực tiếp dùng alert khi thêm giỏ hoặc hết hàng.

**Prompt màn hình:**

```text
Thiết kế trang chủ nhà sách BOOKSTORE123. Giữ navbar với thương hiệu, danh mục, tìm kiếm, giỏ hàng có số lượng và menu tài khoản. Nội dung chính là danh sách sách dạng card: ảnh bìa, tên sách, tác giả, giá VNĐ, tồn kho và Thêm vào giỏ. Có nút hỗ trợ chat nổi. Vẽ trạng thái khách vãng lai và đã đăng nhập, skeleton, không có sản phẩm, hết hàng và toast thêm giỏ. Đề xuất bố cục thoáng, ưu tiên bìa sách, tối ưu desktop/mobile. Không thêm nghiệp vụ mới; phần banner nếu đề xuất chỉ là khối trình bày.
```

### 02 — Kết quả tìm kiếm

**Nguồn:** cùng trang chủ. **Đường dẫn:** `/?search=...`; bản trực tiếp `/public/index.php?search=...`.

**Hiện tại:** không phải trang riêng. Bộ router thay tiêu đề thành “Kết quả tìm kiếm: …”, hiện số sách trả về và giữ từ khóa trên ô tìm. API bộ mới tìm theo tên hoặc tác giả. Bản trực tiếp tìm theo tên và vẫn giữ tiêu đề “Sách mới nhất”. Cùng grid và thao tác thêm giỏ; chưa có bộ lọc nâng cao hoặc sắp xếp.

**Prompt màn hình:**

```text
Vẽ trạng thái kết quả tìm kiếm của trang danh sách sách BOOKSTORE123: ô tìm giữ từ khóa, tiêu đề kết quả, số sách tìm được, grid sách và nút thêm giỏ. Dùng cùng hệ thống card với trang chủ. Có bản không tìm thấy sách và hướng dẫn sửa từ khóa. Đây là một biến thể của danh sách, không phải chức năng tìm kiếm nâng cao mới.
```

### 03 — Sách theo thể loại

**Nguồn:** cùng trang chủ và dropdown danh mục. **Đường dẫn:** `/?category_id=...`; bản trực tiếp dùng `?cat=...`.

**Hiện tại:** chọn thể loại từ dropdown, trả về grid đã lọc. Bộ router hiện “Sách theo thể loại” và số sách; bản trực tiếp vẫn hiện “Sách mới nhất”. Chưa có trang giới thiệu thể loại hay sidebar lọc.

**Prompt màn hình:**

```text
Vẽ danh sách sách theo thể loại của BOOKSTORE123. Giữ menu thể loại và grid sách; thể hiện rõ thể loại đang chọn, số sách và trạng thái không có sách. Dùng chung navbar, card và hành vi với trang chủ. Việc hiển thị tên thể loại nổi bật là cải tiến trình bày cho bộ lọc hiện có.
```

### 04 — Popup chi tiết sách của khách hàng

**Nguồn:** popup trong `app/views/home/index.php` và `public/index.php`.

**Hiện tại:** click ảnh/tên card để mở lớp phủ tối và hộp trắng giữa màn hình. Bộ router rộng 520px, tối đa 92% màn hình; có đóng X, badge thể loại, tên sách, tác giả, mô tả cuộn, giá và Thêm vào giỏ hoặc Hết hàng. **Popup hiện tại không có ảnh bìa.** Bản trực tiếp rộng 50%, chỉ có thể loại, tên, nhãn Mô tả sản phẩm và mô tả; không có giá, tác giả hay nút mua trong popup. Đóng bằng X hoặc click ngoài.

**Prompt màn hình:**

```text
Thiết kế popup chi tiết sách: thể loại, tên, tác giả, mô tả, giá VNĐ, nút Thêm vào giỏ và đóng. Giữ khả năng cuộn mô tả và trạng thái hết hàng. Có thể bổ sung ảnh bìa từ dữ liệu sách sẵn có như một cải tiến bố cục. Desktop có thể bố trí bìa và thông tin thành hai vùng; mobile dùng modal toàn màn hình hoặc sheet dễ đọc. Đây là popup hiện có, chưa có trang chi tiết sản phẩm độc lập cho khách hàng.
```

### 05 — Đăng nhập

**Nguồn:** `app/views/auth/login.php`; `public/login.php`. **Đường dẫn:** `/login`, `/public/login.php`.

**Hiện tại:** nền xám nhạt, một card trắng giữa màn hình. Bộ router card rộng 380px, logo “📚 Shop”, phụ đề “Nhà sách trực tuyến của bạn”, tiêu đề Đăng nhập. Hai trường tên đăng nhập/mật khẩu, nút mint toàn chiều ngang, liên kết đăng ký. Bản trực tiếp đơn giản hơn, không logo/phụ đề. Chưa có quên mật khẩu, nhớ đăng nhập, social login hoặc OTP. Đăng nhập thành công chuyển khách về shop và admin về quản trị.

**Prompt màn hình:**

```text
Vẽ trang đăng nhập BOOKSTORE123 với tên đăng nhập, mật khẩu, nút Đăng nhập và liên kết Đăng ký. Thương hiệu rõ ràng, form gọn và dễ đọc trên mobile. Vẽ lỗi thiếu dữ liệu, sai tài khoản/mật khẩu và đang đăng nhập. Không thêm OTP, social login hoặc quên mật khẩu như chức năng đã có.
```

### 06 — Đăng ký tài khoản

**Nguồn:** `app/views/auth/register.php`; `public/register.php`. **Đường dẫn:** `/register`, `/public/register.php`.

**Hiện tại:** card trắng 420px ở bộ router, form một cột gồm họ tên, điện thoại, địa chỉ, email tùy chọn, tên đăng nhập, mật khẩu. Có thanh độ mạnh mật khẩu, lỗi dưới từng trường, nút Đăng ký ngay và liên kết Đăng nhập. Tên đăng nhập tối thiểu 4 ký tự, mật khẩu tối thiểu 6 theo kiểm tra trên form. Bản trực tiếp card 350px, không email/thanh độ mạnh. Cả hai form đăng ký khách hàng hiện không có xác nhận mật khẩu.

**Prompt màn hình:**

```text
Thiết kế đăng ký BOOKSTORE123 với họ tên, điện thoại, địa chỉ, email tùy chọn, tên đăng nhập và mật khẩu. Giữ thanh độ mạnh mật khẩu, nút Đăng ký ngay và liên kết Đăng nhập. Vẽ lỗi từng trường, username đã được sử dụng, đang đăng ký và thông báo thành công trước khi chuyển đến đăng nhập. Form mobile có thể cuộn, không bị cắt khi mở bàn phím.
```

### 07 — Giỏ hàng và thao tác đặt hàng

**Nguồn:** `app/views/cart/index.php`; `public/cart/view_cart.php`, `public/cart/checkout.php`. **Đường dẫn:** `/cart`, `/public/cart/view_cart.php`.

**Hiện tại:** bộ router có navbar chung; nội dung rộng tối đa 960px. Bảng đầu đen gồm ảnh, tên sách/tác giả, đơn giá, số lượng, thành tiền và xóa. Có input số lượng, tổng cộng màu đỏ, Tiếp tục mua sắm và Đặt hàng ngay. Số lượng đổi trực tiếp gọi API. Chưa đăng nhập bấm đặt hàng chuyển sang đăng nhập; thành công hiện toast rồi sang Đơn hàng của tôi.

Bản trực tiếp là hộp trắng tối đa 900px, không navbar chung, có nút Cập nhật số lượng riêng và liên kết Thanh toán ngay. Checkout trực tiếp xử lý đặt hàng, báo alert rồi về trang chủ.

**Chưa có giao diện checkout riêng:** không form địa chỉ giao hàng trong giỏ, lựa chọn thanh toán, phí ship hoặc voucher. Từ “Thanh toán ngay” ở bản trực tiếp là nút tạo đơn, không bằng chứng có cổng thanh toán.

**Prompt màn hình:**

```text
Thiết kế giỏ hàng BOOKSTORE123 với bìa, tên sách, tác giả, đơn giá, số lượng chỉnh được, thành tiền, xóa và tổng tiền. Có Tiếp tục mua sắm và Đặt hàng ngay. Desktop có thể dùng danh sách sản phẩm bên trái, tổng đơn bên phải; mobile dùng card sản phẩm và nút đặt hàng dễ tiếp cận. Vẽ giỏ trống, đang tải, lỗi tồn kho, đang xử lý và đặt hàng thành công. Giữ luồng đặt hàng trực tiếp; không thêm phương thức thanh toán, phí giao hàng hoặc voucher.
```

### 08 — Thông tin cá nhân

**Nguồn:** `app/views/profile/index.php`; `public/profile.php`. **Đường dẫn:** `/profile`, `/public/profile.php`.

**Hiện tại:** bộ router là card trắng tối đa 700px; tên đăng nhập chỉ đọc, họ tên, điện thoại, địa chỉ, email và Lưu thay đổi. Đổi mật khẩu nằm tiếp phía dưới, có liên kết Xem đơn hàng của tôi. Bản trực tiếp dùng container cố định 900px, hai card cạnh nhau: thông tin cá nhân trái, mật khẩu/xóa tài khoản phải; chỉ có họ tên, điện thoại, địa chỉ, không email/username trong form.

**Prompt màn hình:**

```text
Thiết kế hồ sơ khách hàng BOOKSTORE123: username chỉ đọc, họ tên, điện thoại, địa chỉ, email và Lưu thay đổi. Có liên kết Đơn hàng của tôi và khu đổi mật khẩu cùng trang. Phân nhóm thông tin rõ, vẽ trạng thái đang lưu, lưu thành công và lỗi. Desktop có thể chia khu vực; mobile xếp dọc.
```

### 09 — Khu đổi mật khẩu

**Nguồn:** nằm trong hai trang hồ sơ, không có route riêng.

**Hiện tại:** bộ router có Mật khẩu mới, Xác nhận mật khẩu, nút Đổi mật khẩu; tối thiểu 6 ký tự và báo không khớp. Bản trực tiếp có thêm Mật khẩu hiện tại và kiểm tra mật khẩu cũ. Đây là hai luồng khác nhau, không nên coi bộ router đã có xác thực mật khẩu hiện tại.

**Prompt màn hình:**

```text
Vẽ khu đổi mật khẩu trong hồ sơ BOOKSTORE123. Bản theo router có mật khẩu mới, xác nhận và nút đổi; bản PHP trực tiếp có thêm mật khẩu hiện tại. Thể hiện hai biến thể nếu cần bảo toàn cả hai luồng. Vẽ lỗi mật khẩu không khớp, quá ngắn, sai mật khẩu hiện tại ở biến thể tương ứng, đang cập nhật và thành công. Không tạo một trang riêng bắt buộc nếu vẫn dùng khu trong hồ sơ.
```

### 10 — Xóa tài khoản và xác nhận

**Nguồn:** chỉ `public/profile.php`.

**Hiện tại:** nút viền đỏ “XÓA TÀI KHOẢN VĨNH VIỄN” dưới khu đổi mật khẩu; click mở hộp `confirm()` của trình duyệt. Sau xóa kết thúc phiên và về shop. Bộ router không có nút này.

**Prompt màn hình:**

```text
Thiết kế khu Xóa tài khoản của biến thể hồ sơ PHP trực tiếp. Hiện nút xóa và hộp xác nhận với hai lựa chọn Hủy/Xóa tài khoản, nêu rõ hành động vĩnh viễn. Đây là thiết kế lại thao tác đang có ở bộ trực tiếp; nếu dùng trong bộ router cần đội phát triển tích hợp chức năng.
```

### 11 — Đơn hàng của tôi

**Nguồn:** `app/views/orders/my_orders.php`; `public/my_orders.php`. **Đường dẫn:** `/my-orders`, `/public/my_orders.php`.

**Hiện tại:** bộ router dùng card đơn hàng xếp dọc, tối đa 960px, viền trái mint. Mỗi card có mã đơn, thời gian, badge trạng thái, bảng sách với ảnh/tên/đơn giá/số lượng/thành tiền và tổng đơn. Có 5 nhãn: Đang xử lý, Chờ xử lý, Đang giao, Đã hoàn thành, Đã hủy. Không có nút xóa/hủy trong view này. Có đang tải, chưa có đơn và Mua sắm ngay.

Bản trực tiếp dùng một bảng với mã đơn, tên các sách gộp bằng dấu phẩy, tổng tiền, trạng thái và Xóa đơn. Nút này thực hiện xóa đơn sau confirm; không phải luồng đổi trạng thái sang Đã hủy.

**Prompt màn hình:**

```text
Vẽ Đơn hàng của tôi cho BOOKSTORE123: danh sách đơn có mã, ngày, trạng thái, các sách với ảnh, đơn giá, số lượng, thành tiền và tổng đơn. Vẽ từng badge trạng thái, đang tải và chưa có đơn kèm Mua sắm ngay. Nếu bảo toàn biến thể PHP trực tiếp, thêm thiết kế Xóa đơn và hộp xác nhận riêng; không đổi tên thành Hủy đơn vì nghiệp vụ hiện tại là xóa.
```

### 12 — Chat hỗ trợ của khách hàng

**Nguồn/đường dẫn:** `public/chat.php`, `/public/chat.php`.

**Hiện tại:** trang riêng, yêu cầu đăng nhập; bản trang chủ trực tiếp mở tab mới. Nền gradient tím, hộp chat trắng tối đa 400px, cao 600px, bo 20px. Header gradient xanh: Chat với Admin, chấm xanh, “Admin đang online”, mã người dùng và đóng X. Nhãn online hiện là nội dung cố định, chưa chứng minh trạng thái hiện diện thật.

Tin khách bên phải màu xanh, tin admin bên trái màu trắng, có giờ; đáy có ô Nhập tin nhắn và nút gửi tròn. Có lời chào khi chưa có tin, Enter để gửi và cập nhật tin bằng polling. Có thành phần “Admin đang nhập” nhưng chưa thấy kết nối tín hiệu nhập từ admin. Mobile dưới 480px chuyển khung chat về kích thước toàn màn hình.

**Prompt màn hình:**

```text
Thiết kế chat hỗ trợ khách hàng BOOKSTORE123 với header Admin, lịch sử tin nhắn phân biệt người gửi, giờ gửi, ô nhập và nút gửi. Có trạng thái chưa có tin, đang gửi và gửi lỗi. Desktop là khung chat gọn; mobile toàn màn hình. Đồng bộ phong cách với shop. Không thể hiện trạng thái online hoặc đang nhập như dữ liệu thời gian thực đã được hỗ trợ; đánh dấu đó là phần cần tích hợp nếu muốn có.
```

## 4. Từng giao diện quản trị

### 13 — Danh sách sách / trang vào admin

**Nguồn:** `app/views/admin/dashboard.php`; `admin/admin_dashboard.php`. **Đường dẫn:** `/admin`, `/admin/admin_dashboard.php`.

**Hiện tại:** dù tên file là dashboard, đây là quản lý sách, chưa phải màn hình tổng quan doanh thu. Bộ router có navbar mint với Shop Admin, Sách, Thể loại, Đơn hàng, Khách hàng, Chat, Đăng xuất. Dưới là tiêu đề Danh sách sách, tìm kiếm có gợi ý, Tìm và Thêm sách. Bảng: ID, tên, tác giả, giá, thể loại, tồn kho, ảnh, thao tác Sửa/Xóa; đầu đen, ô xanh nhạt. Có loading, bảng trống, popup thêm/sửa, confirm xóa và toast.

Bản trực tiếp có ô tìm trên navbar; bên dưới là nhóm nút tới thể loại, thêm sách, khách hàng, hóa đơn, kho và chat. Bảng không có tồn kho; Edit mở popup và Delete mở confirm.

**Prompt màn hình:**

```text
Thiết kế trang quản lý sách admin BOOKSTORE123. Có điều hướng tới Sách, Thể loại, Đơn hàng, Tồn kho, Khách hàng, Chat và Đăng xuất; có thể dùng sidebar thống nhất. Nội dung gồm tìm kiếm có gợi ý, nút Thêm sách và bảng ID, ảnh, tên, tác giả, thể loại, giá, tồn kho, Sửa/Xóa. Vẽ đang tải, không có sách, danh sách gợi ý và xác nhận xóa. Không biến màn hình này thành dashboard doanh thu vì chưa có dữ liệu/luồng đó.
```

### 14 — Thêm sách

**Nguồn:** popup trong `app/views/admin/dashboard.php`; trang `admin/add_book.php`.

**Hiện tại:** bộ router popup trắng 500px gồm tên sách, tác giả, giá, chọn thể loại, số lượng, mô tả, tải ảnh và Lưu. Upload thành công hiện tên file, chưa có preview bìa. Bản trực tiếp là form Bootstrap toàn vùng container, có tên, mô tả bắt buộc, tác giả, giá, thể loại, file ảnh bắt buộc, nút Add new; không có ô số lượng.

**Prompt màn hình:**

```text
Thiết kế form thêm sách admin: tên, tác giả, giá VNĐ, thể loại chọn từ danh sách, số lượng, mô tả, upload ảnh và Lưu. Thể hiện vùng upload, tên file đã chọn và có thể preview bìa như cải tiến trình bày. Có lỗi và đang lưu. Vẽ dạng modal desktop, mobile có vùng cuộn; cũng có thể đề xuất form trang riêng cho cùng nghiệp vụ. Ghi chú số lượng có ở bộ router nhưng chưa có ở form PHP trực tiếp.
```

### 15 — Sửa sách

**Nguồn:** popup trong hai trang danh sách sách admin.

**Hiện tại:** bộ router dùng lại popup thêm sách với dữ liệu điền sẵn: tên, tác giả, giá, select thể loại, số lượng, mô tả. Trường tải ảnh chỉ xuất hiện khi thêm, **không có khi sửa**. Bản trực tiếp popup 450px có tên, tác giả, giá, thể loại nhập text, mô tả; không số lượng/tải ảnh.

**Prompt màn hình:**

```text
Vẽ popup sửa sách với dữ liệu sẵn: tên, tác giả, giá, thể loại, số lượng và mô tả; có Lưu, đóng và thông báo lỗi. Bộ router dùng select thể loại; bản trực tiếp dùng text và không có số lượng. Không thể hiện thay ảnh như thao tác đã có trong form sửa; nếu đề xuất thì đánh dấu cần bổ sung chức năng.
```

### 16 — Chi tiết sách của admin

**Nguồn/đường dẫn:** `admin/book_detail.php?id=...`.

**Hiện tại:** trang Bootstrap với navbar mint, tiêu đề Thông tin sản phẩm; bảng hai cột nhãn/giá trị gồm mã, tên, mô tả, tác giả, giá, thể loại và ảnh bìa rộng 100px. Dưới có Delete và Quay lại. File tồn tại, nhưng chưa tìm thấy liên kết dẫn tới trang này trong danh sách sách hiện tại. Không có route chi tiết admin tương ứng ở bộ router.

**Prompt màn hình:**

```text
Thiết kế trang chi tiết sách dành cho admin: mã sách, bìa, tên, tác giả, mô tả, giá và thể loại. Có Quay lại và Xóa kèm xác nhận. Giữ mục đích xem thông tin quản trị. Đây là trang PHP trực tiếp đang tồn tại; việc thêm đường vào từ bảng sách là cải tiến điều hướng cần nối khi triển khai.
```

### 17 — Quản lý thể loại, thêm và sửa

**Nguồn:** `app/views/admin/categories.php`; `admin/categories.php`. **Đường dẫn:** `/admin/categories`, `/admin/categories.php`.

**Hiện tại:** bộ router chia hai cột, trái card rộng 360px với tên thể loại và nút Thêm; khi sửa đổi thành Cập nhật, thêm Hủy sửa. Phải bảng ID, tên thể loại, số sách, Sửa/Xóa; đầu đen, ô xanh nhạt. Có lỗi form, toast và confirm xóa.

Bản trực tiếp khác hẳn: header xám đen lớn, Quay lại, hai card thống kê Tổng thể loại/Tổng sách, thanh Danh sách thể loại/Thêm thể loại mới, bảng trắng. Thêm và sửa dùng hai modal riêng với tên, Hủy, Lưu/Thêm. Nếu thể loại còn sách, hiển thị Không thể xóa thay nút xóa.

**Prompt màn hình:**

```text
Thiết kế quản lý thể loại admin BOOKSTORE123: bảng ID, tên, số sách, Sửa/Xóa; form hoặc modal thêm/sửa chỉ có tên thể loại. Giữ trạng thái thêm, sửa, hủy sửa, tên trùng, danh sách trống và không thể xóa thể loại đang có sách. Có thể giữ hai chỉ số Tổng thể loại/Tổng sách từ biến thể PHP trực tiếp. Đồng bộ giao diện với admin sách.
```

### 18 — Quản lý đơn hàng / hóa đơn

**Nguồn:** `app/views/admin/orders.php`; `admin/manage_orders.php`. **Đường dẫn:** `/admin/orders`, `/admin/manage_orders.php`.

**Hiện tại:** bộ router có lọc username, select trạng thái và Lọc. Các card đơn xếp dọc: mã, username/họ tên, thời gian, badge, tổng tiền, select trạng thái, Lưu và Xóa. Click header mở chi tiết gồm ảnh, tên sách, đơn giá, số lượng, thành tiền. Hỗ trợ 5 trạng thái như trang đơn khách hàng. Có loading/trống/toast/confirm.

Bản trực tiếp là bảng rộng: STT, khách hàng, ngày, bảng chi tiết lồng bên trong, tổng tiền, select trạng thái, Delete. Chọn trạng thái tự gửi form, không nút Lưu riêng; select có 4 trạng thái, thiếu Đang xử lý. Cuối trang có tổng thành tiền các đơn được hiển thị. Đây không phải hệ thống xuất hóa đơn PDF.

**Prompt màn hình:**

```text
Thiết kế quản lý đơn hàng admin: lọc theo username/trạng thái, danh sách mã đơn, khách hàng, ngày, tổng tiền, badge. Có vùng mở chi tiết sách, đơn giá, số lượng, thành tiền; đổi trạng thái và Lưu; Xóa kèm xác nhận. Dùng 5 trạng thái Đang xử lý, Chờ xử lý, Đang giao, Đã hoàn thành, Đã hủy cho biến thể router. Vẽ đơn đóng/mở, trống, đang tải và cập nhật thành công. Tổng tiền danh sách có ở biến thể trực tiếp; không gọi đó là doanh thu thuần hoặc thêm xuất hóa đơn như tính năng sẵn có.
```

### 19 — Quản lý tồn kho và popup cập nhật

**Nguồn/đường dẫn:** `admin/manage_inventory.php`. Chỉ có ở bộ PHP trực tiếp.

**Hiện tại:** navbar mint, tiêu đề giữa; bảng Bootstrap đầu đen, hàng xen kẽ. Cột Mã sách, tên, ảnh, Đã bán, Còn lại và Edit. Trong mã hiện tại, cột có nhãn Mã sách nhưng đang render số thứ tự, không phải ID thật. Đã bán xanh lá, tồn kho đỏ kể cả khi vẫn còn hàng. Edit mở popup rộng 40% màn hình với hai ô số Đã bán/Còn lại và Lưu thay đổi. Chưa có lọc kho, cảnh báo sắp hết hay lịch sử nhập kho.

**Prompt màn hình:**

```text
Thiết kế quản lý tồn kho admin BOOKSTORE123 với danh sách sách: mã hoặc STT được ghi đúng nhãn, tên, bìa, đã bán, còn lại và Cập nhật. Vẽ popup cập nhật hai số Đã bán/Còn lại, nút Lưu, đóng và trạng thái thành công/lỗi. Thể hiện số tồn rõ ràng; màu hết hàng có thể phân biệt theo số lượng sẵn có. Không thêm lịch sử nhập kho hay ngưỡng sắp hết như nghiệp vụ hiện có.
```

### 20 — Danh sách khách hàng

**Nguồn:** `app/views/admin/customers.php`; `admin/manage_customers.php`. **Đường dẫn:** `/admin/customers`, `/admin/manage_customers.php`.

**Hiện tại:** bộ router có tìm theo họ tên/username/điện thoại, Thêm khách hàng; bảng ID, username, họ tên, điện thoại, địa chỉ, email, Sửa/Xóa. Form thêm/sửa ở modal; có loading, trống, toast và confirm.

Bản trực tiếp bảng Bootstrap gồm STT, họ tên, điện thoại, địa chỉ, username, Password, thao tác; giá trị ở cột Password đang in 15 ký tự đầu của dữ liệu mật khẩu và dấu ba chấm. Không nên đưa cột này vào thiết kế mới. Có Add new dẫn trang riêng, Edit dẫn trang riêng, Delete confirm; chưa có tìm kiếm ở biến thể này.

**Prompt màn hình:**

```text
Thiết kế quản lý khách hàng BOOKSTORE123 với tìm theo tên, username, điện thoại; nút Thêm khách hàng; bảng ID, username, họ tên, điện thoại, địa chỉ, email và Sửa/Xóa. Vẽ dữ liệu dài, email trống, không có kết quả, đang tải và xác nhận xóa. Loại bỏ cột hiển thị dữ liệu mật khẩu của bản trực tiếp khỏi giao diện đề xuất.
```

### 21 — Thêm khách hàng của admin

**Nguồn:** modal `app/views/admin/customers.php`; trang `admin/add_customer.php`.

**Hiện tại:** modal router rộng 460px: username, mật khẩu, họ tên, điện thoại, địa chỉ, email, Lưu. Bản trực tiếp form Bootstrap tối đa 600px: họ tên, username, mật khẩu, xác nhận mật khẩu, địa chỉ, điện thoại; tiêu đề ĐĂNG KÝ, nút đang ghi Sign in và Quay lại danh sách. Đây là tạo khách hàng bởi admin, khác trang khách tự đăng ký.

**Prompt màn hình:**

```text
Thiết kế form Thêm khách hàng trong khu admin: username, mật khẩu, họ tên, điện thoại, địa chỉ, email, nút Tạo khách hàng và đóng/quay lại. Nếu thiết kế biến thể PHP trực tiếp, giữ thêm Xác nhận mật khẩu và bỏ email vì form đó chưa có. Sửa nhãn Sign in thành Tạo khách hàng cho đúng ý nghĩa. Vẽ lỗi username trùng và đang lưu; dùng phong cách quản trị.
```

### 22 — Sửa khách hàng của admin

**Nguồn:** modal `app/views/admin/customers.php`; trang `admin/edit_customer.php?id=...`.

**Hiện tại:** bộ router username chỉ đọc; mật khẩu mới có thể để trống để giữ nguyên; họ tên, điện thoại, địa chỉ, email và Lưu. Bản trực tiếp form 600px, tiêu đề chứa username; chỉ họ tên, điện thoại (đang dùng textarea 2 dòng), địa chỉ và Submit. Không có đổi username/mật khẩu/email trong form trực tiếp.

**Prompt màn hình:**

```text
Vẽ Sửa khách hàng admin với username chỉ đọc, họ tên, điện thoại, địa chỉ, email và mật khẩu mới tùy chọn; ghi rõ để trống mật khẩu thì giữ nguyên. Có Lưu, đóng/quay lại, lỗi và đang lưu. Biến thể PHP trực tiếp chỉ sửa họ tên, điện thoại, địa chỉ; thay textarea điện thoại bằng input phù hợp như cải tiến trình bày.
```

### 23 — Chat của admin

**Nguồn/đường dẫn:** `admin/admin_chat.php`. Chỉ có giao diện PHP trực tiếp.

**Hiện tại:** header gradient tím, tiêu đề Admin Chat - Hỗ trợ khách hàng và Quay lại. Vùng nội dung tối đa 1200px, hai cột: trái rộng 300px danh sách khách đã chat, mã người dùng, giờ tin gần nhất, badge chưa đọc; phải khung hội thoại. Chưa chọn khách thì có hướng dẫn và input/nút gửi bị khóa. Chọn khách hiện trạng thái active, header người đang chat và lịch sử tin. Tin khách trái xanh nhạt; tin admin phải xanh lá, kèm giờ. Đáy có input và Gửi. Polling 3 giây; mobile dưới 768px xếp danh sách khách ở trên, hội thoại bên dưới. Không có ô tìm cuộc hội thoại hoặc đính kèm file.

**Prompt màn hình:**

```text
Thiết kế hộp thư hỗ trợ admin BOOKSTORE123: danh sách cuộc hội thoại bên trái với định danh khách, thời gian, badge chưa đọc; khung chat bên phải có tên người đang chat, lịch sử phân biệt gửi/nhận, giờ, ô nhập và Gửi. Vẽ chưa có cuộc hội thoại, chưa chọn khách, đang tải, đang gửi và lỗi gửi. Mobile có thể dùng danh sách rồi chuyển sang hội thoại để dễ thao tác. Không tự thêm file đính kèm hoặc trạng thái online thời gian thực.
```

## 5. Hai giao diện kỹ thuật còn lại

### 24 — API Test Panel

**Nguồn/đường dẫn:** `api/test.html`.

**Hiện tại:** giao diện nền tối `#1e1e1e`, font monospace; tiêu đề Bookstore REST API — Test Panel, base URL, các nhóm books/categories/customers/orders. Mỗi nhóm có card GET/POST/PUT/DELETE, ID hoặc bộ lọc, JSON textarea, Gửi; cuối có khu kết quả JSON cuộn. Đây là công cụ kiểm thử, không nằm trong trải nghiệm mua sách thông thường. Nhóm customers trên trang test khác tên tuyến users của API router mới.

**Prompt tùy chọn:**

```text
Nếu cần nâng cấp công cụ nội bộ, thiết kế REST API Test Panel nền tối với nhóm tài nguyên, badge HTTP method, endpoint, ô ID/bộ lọc, JSON body, nút gửi và vùng kết quả. Tách khỏi website mua sách và admin vận hành.
```

### 25 — Trang debug tin nhắn

**Nguồn/đường dẫn:** `public/debug_messages.php`.

**Hiện tại:** HTML đơn giản: bảng 10 tin gần nhất với ID, Sender, Receiver, Message, Time, Read; tin mới trong 5 phút có nền xanh nhạt. Phía dưới thống kê tổng tin, chưa đọc cho admin, số người đã chat và Quay lại. Đây là trang chẩn đoán, không phải màn hình hỗ trợ khách hàng.

**Prompt tùy chọn:**

```text
Nếu cần thiết kế công cụ chẩn đoán nội bộ, vẽ bảng 10 tin gần nhất với ID, người gửi/nhận, nội dung, thời gian, trạng thái đã đọc; phía trên có tổng tin, chưa đọc cho admin và số người chat. Giữ tách riêng khỏi các giao diện khách hàng.
```

## 6. Những phần không phải giao diện riêng

- `logout.php`, `login_process.php`, `add_cart.php`, các file `*_process.php`, `delete_*.php`, các API và controller/model là xử lý/chuyển hướng, không cần yêu cầu Stitch vẽ thành trang độc lập.
- `public/cart/checkout.php` là xử lý tạo đơn và thông báo, không phải form checkout.
- Loading, toast, alert, confirm xóa sách/khách/thể loại/đơn là trạng thái hoặc thành phần của màn hình tương ứng; nên vẽ lại theo design system.
- Trang tìm kiếm và trang thể loại là hai biến thể của trang chủ. Đổi mật khẩu và xóa tài khoản nằm trong hồ sơ. Thêm/sửa sách và khách hàng ở bộ router là modal.
- Chưa thấy giao diện riêng cho quên mật khẩu, yêu thích, đánh giá, mã giảm giá, thanh toán online, theo dõi vận chuyển chi tiết, quản lý khuyến mãi, quản lý banner hay thống kê doanh thu.

## 7. Điểm cần giữ trong brief triển khai

1. **Chọn một bộ giao diện làm nền triển khai.** Thiết kế có thể thống nhất, nhưng cần quyết định tích hợp những chức năng chỉ có ở PHP trực tiếp; chỉ thay CSS của bộ router sẽ bỏ sót kho/chat/xóa tài khoản/xóa đơn.
2. **Hai liên kết chat ở bộ router đang không khớp file hiện có:** footer khách hàng dẫn `/chat.php` trong khi file là `/public/chat.php`; navbar admin dẫn `/public/admin_chat.php` trong khi file là `/admin/admin_chat.php`. Đây là phát hiện từ mã nguồn, chưa kiểm thử trên máy chủ.
3. **Không đổi ý nghĩa nút xóa đơn thành hủy đơn.** Hai thao tác khác nhau; view PHP trực tiếp hiện xóa, view router khách hàng chỉ xem.
4. **Không hiển thị mật khẩu trong bảng khách hàng mới.** Bản router đã không có cột này.
5. **Chuẩn hóa nhãn tiếng Việt:** Add new, Edit, Delete, Submit, Sign in và Search hiện trộn với tiếng Việt. Cột “Mã sách” trong kho cần dùng ID thật hoặc đổi nhãn thành STT.
6. **Thống nhất điều hướng:** kho và chat có file nhưng chưa tích hợp đầy đủ vào bộ navbar mới; trang chi tiết sách admin tồn tại nhưng chưa thấy liên kết vào.
7. **Thiết kế mobile cho toàn bộ bộ màn hình:** đặc biệt navbar dài, bảng lồng đơn hàng, form đăng ký và bảng admin; chưa thể coi các phần này đã tối ưu mobile vì chỉ có grid/viewport.

## 8. Thứ tự gửi Stitch để có bộ thiết kế nhất quán

Gửi prompt nền trước, rồi lần lượt: 01 trang chủ → 04 chi tiết → 02 tìm kiếm → 03 thể loại → 07 giỏ → 05 đăng nhập → 06 đăng ký → 08 hồ sơ → 09 mật khẩu → 10 xóa tài khoản → 11 đơn của tôi → 12 chat khách → 13 admin sách → 14 thêm sách → 15 sửa sách → 16 chi tiết admin → 17 thể loại → 18 đơn admin → 19 kho → 20 khách hàng → 21 thêm khách → 22 sửa khách → 23 chat admin. Hai trang kỹ thuật 24–25 chỉ gửi nếu muốn nâng cấp công cụ nội bộ.

Sau mỗi màn hình có thể thêm câu: “Giữ cùng design system với màn hình trước; xuất bản desktop và mobile, kèm trạng thái và modal được nêu trong brief.”
