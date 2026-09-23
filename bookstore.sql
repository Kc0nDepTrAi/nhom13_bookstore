-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th9 23, 2026 lúc 09:02 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `bookstore_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sold` int(11) DEFAULT 0 COMMENT 'Số lượng đã bán',
  `quantity` int(11) DEFAULT 0 COMMENT 'Số lượng còn lại trong kho'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `price`, `category`, `image`, `category_id`, `description`, `sold`, `quantity`) VALUES
(1, 'Ngụ Ngôn La Fontaine', NULL, 934999.00, 'văn học', 'book1.png', 1, 'hay đấy', 7, 1),
(2, 'Xã Hội Học - Khái Lược', NULL, 331500.00, 'xã hội học', 'book2.png', 3, 'hay', 6, 17),
(3, 'Kinh Tế Học - Khái Lược', NULL, 331500.00, 'kinh tế', 'book3.png', 2, 'hay', 3, 41),
(4, 'Harry Potter Tập 5', NULL, 327250.00, 'văn học', 'book4.png', 1, 'hay', 2, 76),
(5, 'Harry Potter Tập 1', NULL, 120000.00, 'xã hội học', 'book5.png', 3, 'hay', 2, 54),
(6, 'ông già và biển cả', 'Ernest Hemingway', 36000.00, 'Văn học', '1768236137_ong-gia-dinh-ti.webp', 1, 'Ông Già và biển cả (tên tiếng Anh: The Old Man and the Sea) là một tiểu thuyết ngắn được Ernest Hemingway viết ở Cuba năm 1951 và xuất bản năm 1952. Tác phẩm là truyện ngắn dạng viễn tưởng và là một trong những đỉnh cao trong sự nghiệp sáng tác của nhà văn, đoạt giải Pulitzer năm 1953.', 0, 15),
(7, 'hoàng tử bé', 'Antoine de Saint-Exupéry', 18000.00, 'Văn học', '1768239030_bia-1-hoang-tu-be.webp', 1, 'Được mệnh danh là “cuốn sách dành cho mọi độ tuổi”, Hoàng tử bé là một tác phẩm hiếm hoi có thể khiến người lớn dừng lại, suy ngẫm, và hồi tưởng về chính tuổi thơ của mình. Dưới hình hài một câu chuyện thiếu nhi là một bản ngụ ngôn thấm đẫm tình yêu, sự cô đơn, lòng bao dung và bài học về những điều “thiết yếu mà mắt thường không nhìn thấy được”.', 0, 14),
(8, 'hai số phận', 'Jeffrey Archer', 29000.00, 'Văn học', '1768403877_hsp_bia_cung_-_xuat_in_goc-b1_a9056626d4ef4861a3c62699eb181d83_master.jpg', 1, 'William Lowell Kane: Sinh ra trong nhung lụa, là người thừa kế của một gia tộc ngân hàng danh giá tại Boston, Mỹ. Anh được giáo dục bài bản để trở thành một quý ông thượng lưu. Abel Rosnovski (tên thật là Wladek Koskiewicz): Sinh ra trong cảnh nghèo khó tại một khu rừng ở Ba Lan, lớn lên trong sự tàn khốc của Thế chiến thứ nhất và các trại tập trung. Anh di cư sang Mỹ với bàn tay trắng để tìm kiếm tương lai.', 0, 45),
(9, 'số đỏ', 'vũ trọng phụng', 30000.00, 'Văn học', '1768406673_thiet_ke_chua_co_ten_-_2024-08-30t094519.197_549f30009de045a79ace8f6f151401e7.png', 1, 'Số đỏ là một tiểu thuyết văn học của nhà văn Vũ Trọng Phụng đăng trên Hà Nội báo từ số 40 ngày 7 tháng 10 năm 1936 và được in thành sách lần đầu vào năm 1938, với 20 chương.', 0, 156),
(10, 'bố già', 'Mario Puzo', 312000.00, 'Văn học', '1768407843_download.jpg', 1, 'Bố già là tên một cuốn tiểu thuyết nổi tiếng của nhà văn người Mỹ gốc Ý Mario Puzo, được nhà xuất bản G. P. Putnam Sons xuất bản lần đầu vào năm 1969. Tác phẩm là câu chuyện về một gia đình mafia gốc Sicilia tại Mỹ, được một nhân vật gọi là Bố già Don Vito Corleone tạo lập và lãnh đạo.', 0, 30),
(11, 'chiến lược đại dương xanh ', 'W.Chan Kim', 32000.00, 'Kinh tế', '1768473374_download (1).jpg', 2, 'Bản chất của Chiến lược đại dương xanh là nâng cao về giá trị đi kèm với sự tiện lợi, giá cả thấp và giảm chi phí. Nó buộc các công ty phải có bước nhảy vọt về giá trị, mang lại sự gia tăng mạnh mẽ về giá trị cho cả người mua và chính họ.', 0, 50),
(14, 'cú hích', 'Richard Thaler', 545643.00, 'Kinh tế', '1768491527_cu-hich-01.jpg', 2, 'Được dịch từ tiếng Anh-Nudge: Cải thiện Quyết định về Sức khỏe, Sự giàu có và Hạnh phúc là cuốn sách được viết bởi nhà kinh tế học và người đoạt giải Nobel Richard H. Thaler của Đại học Chicago và giáo sư Cass R. Sunstein của Trường Luật Harvard, xuất bản lần đầu năm 2008.', 0, 23),
(15, 'cha giàu cha nghèo', 'Robert Kiyosaki', 77777.00, 'Kinh tế', '1768500512_download (2).jpg', 2, 'Rich Dad, Poor Dad là cuốn sách bán chạy nhất của Robert Kiyosaki. Trong đó, ông bày tỏ thái độ ủng hộ cho sự độc lập về tài chính nhờ đầu tư, bất động sản, kinh doanh và sử dụng tài chính hợp lý.', 0, 0),
(16, 'xây dựng xã hội học tập', 'Joseph E. Stiglitz & Bruce C. Greenwal', 888888.00, 'Xã hội học', '1768500901_462565968-956584146291295-4908388658040420703-n.webp', 3, 'Từ khi được xuất bản, cuốn sách Xây dựng xã hội học tập: Cách tiếp cận mới cho tăng trưởng, phát triển và tiến bộ xã hội đã trở thành một tài liệu hữu ích cho độc giả – những người luôn dành sự ủng hộ đối với những chính sách thúc đẩy sự tiến bộ về khoa học và công nghệ của chính phủ. Cuốn sách là một tác phẩm đầy thuyết phục cho thấy mức sống của chúng ta được cải thiện đáng kể như thế nào là kết quả của việc học cách để học tập, và đưa ra những lý giải làm thế nào mà các quốc gia phát triển và các quốc gia đang phát triển đều có thể xây dựng được một nền kinh tế học tập kiểu mới.', 0, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Văn học'),
(2, 'Kinh tế'),
(3, 'Xã hội học'),
(4, 'Thiếu nhi');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender` varchar(50) DEFAULT NULL,
  `receiver` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `messages`
--

INSERT INTO `messages` (`id`, `sender`, `receiver`, `message`, `created_at`, `is_read`) VALUES
(31, 'user_Dong2k5', 'admin', 'xin chào', '2026-04-01 08:35:50', 1),
(32, 'admin', 'user_Dong2k5', 'chào bạn tôi có thể giúp gì cho bạn', '2026-04-01 08:36:14', 1),
(33, 'user_eden', 'admin', 'chào admin', '2026-04-22 02:22:04', 1),
(34, 'admin', 'user_eden', 'chào bạn, tôi có thể giúp gì cho b', '2026-04-22 04:32:07', 1),
(35, 'user_eden', 'admin', 'ád', '2026-04-22 04:32:33', 1),
(36, 'admin', 'user_eden', 'as', '2026-04-22 04:32:59', 1),
(37, 'admin', 'user_eden', 'fgfxg', '2026-04-22 08:53:30', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Đang xử lý',
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `username`, `total_price`, `status`, `created_at`) VALUES
(2, 'khai', 3740000.00, 'Đã hoàn thành', '2026-01-10 01:28:12'),
(3, 'khai', 663000.00, 'Đang xử lý', '2026-01-10 01:28:59'),
(4, 'khai', 1266500.00, 'Đang giao', '2026-01-12 21:30:32'),
(6, 'eden', 654500.00, 'Đã hủy', '2026-01-13 22:54:27'),
(8, 'eden', 994500.00, 'Đang xử lý', '2026-01-13 23:33:15'),
(9, 'eden', 658750.00, 'Đang xử lý', '2026-01-13 23:52:26'),
(10, 'eden', 2298249.00, 'Đang xử lý', '2026-01-15 20:40:23'),
(11, 'eden', 331500.00, 'Đang xử lý', '2026-01-15 20:50:16'),
(12, 'eden', 1386499.00, 'Đang xử lý', '2026-01-15 20:53:48'),
(13, 'eden', 1597999.00, 'Đang xử lý', '2026-01-15 20:54:24'),
(14, 'eden', 934999.00, 'Đang xử lý', '2026-01-15 21:30:07'),
(17, 'Dong2k5', 658750.00, 'Đang xử lý', '2026-03-26 17:40:13'),
(19, 'eden', 778750.00, 'Đang xử lý', '2026-04-22 09:40:35'),
(20, 'eden', 331500.00, 'Đang xử lý', '2026-04-22 11:53:58'),
(22, 'eden', 331500.00, 'Đang xử lý', '2026-05-10 14:40:57');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `book_id`, `quantity`, `price`) VALUES
(5, 2, 1, 4, 935000.00),
(6, 3, 2, 1, 331500.00),
(8, 4, 1, 1, 935000.00),
(9, 4, 2, 1, 331500.00),
(12, 6, 4, 2, 327250.00),
(14, 8, 3, 2, 331500.00),
(15, 8, 2, 1, 331500.00),
(16, 9, 4, 1, 327250.00),
(17, 9, 3, 1, 331500.00),
(18, 10, 1, 1, 934999.00),
(19, 10, 2, 1, 331500.00),
(20, 10, 3, 1, 331500.00),
(21, 10, 4, 1, 327250.00),
(22, 10, 12, 1, 32000.00),
(23, 10, 11, 1, 312000.00),
(24, 10, 9, 1, 29000.00),
(25, 11, 3, 1, 331500.00),
(26, 12, 1, 1, 934999.00),
(27, 12, 2, 1, 331500.00),
(28, 12, 5, 1, 120000.00),
(29, 13, 1, 1, 934999.00),
(30, 13, 2, 1, 331500.00),
(31, 13, 3, 1, 331500.00),
(32, 14, 1, 1, 934999.00),
(35, 17, 4, 1, 327250.00),
(36, 17, 2, 1, 331500.00),
(38, 19, 3, 1, 331500.00),
(39, 19, 4, 1, 327250.00),
(40, 19, 5, 1, 120000.00),
(41, 20, 3, 1, 331500.00),
(43, 22, 2, 1, 331500.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `phone`, `address`, `email`, `role`, `created_at`) VALUES
(1, 'admin', '123', NULL, NULL, NULL, 'admin@gmail.com', 'admin', '2026-01-09 09:07:00'),
(2, 'khai', '123', 'Bùi Khả', '0325916816', 'ha noi', NULL, 'user', '2026-01-09 09:45:00'),
(3, 'eden', '12345', 'duong', '09999', 'hanoi', NULL, 'user', '2026-01-12 14:31:22'),
(8, 'kien', '123', 'kien', '08888', 'phú lương', NULL, 'user', '2026-01-13 17:10:08'),
(9, 'son', '123', 'son', '0368102969', 'thanh ba - phú thọ', NULL, 'user', '2026-01-13 17:16:09'),
(10, 'minh', '123', 'minh', '0877736062', 'từ sơn - bắc ninh', NULL, 'user', '2026-01-13 17:19:34'),
(12, 'Dong2k5', '123', 'Nguyễn Văn Đồng', '0373660207', 'Hà Nội', NULL, 'user', '2026-03-26 10:28:17');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Các ràng buộc cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
