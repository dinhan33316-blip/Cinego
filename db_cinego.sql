-- ============================================================
-- CINEGO Cinema - Database Initialization Script
-- Hệ Quản Trị Cơ Sở Dữ Liệu: MySQL / MariaDB (Navicat Compatible)
-- Bộ mã: utf8mb4_unicode_ci
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tạo Database db_cinego nếu chưa tồn tại và chọn sử dụng
CREATE DATABASE IF NOT EXISTS `db_cinego`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `db_cinego`;

-- ============================================================
-- 2. Cấu trúc bảng `users`
-- Phục vụ đăng nhập quản trị hệ thống (admin.php & CinegoModel.php)
-- ============================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Quản Trị Viên',
  `role` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Chèn tài khoản Quản trị viên mặc định:
-- Tên đăng nhập: admin
-- Mật khẩu: 123456 (được băm chuẩn Bcrypt/PASSWORD_DEFAULT, CinegoModel hỗ trợ cả verify hash lẫn so sánh trực tiếp)
-- ------------------------------------------------------------
INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$12$Gw3WEbfE.cEJlDfSPH0ItueMfE2paJSSgrHZBYRRCGCI6tQvjtTQq', 'Quản Trị Viên CINEGO', 'admin', CURRENT_TIMESTAMP);


-- ============================================================
-- 3. Cấu trúc bảng `movies`
-- Lưu trữ danh sách phim đang chiếu và sắp chiếu
-- ============================================================
DROP TABLE IF EXISTS `movies`;
CREATE TABLE `movies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `genre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '120 phút',
  `rating` decimal(3,1) DEFAULT 9.0,
  `age_limit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'P',
  `poster` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'showing',
  `trailer_url` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `formats` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '2D, 3D, IMAX',
  `release_date` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vietnamese_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Dữ liệu 17 bộ phim nguyên bản từ file assets/js/data.js
-- (Gồm 13 phim Đang chiếu & 4 phim Sắp chiếu)
-- ------------------------------------------------------------
INSERT INTO `movies` (`id`, `title`, `genre`, `duration`, `rating`, `age_limit`, `poster`, `description`, `status`, `trailer_url`, `formats`, `release_date`, `vietnamese_title`, `created_at`) VALUES
(1, 'Deadpool & Wolverine', 'Hành động, Khoa học viễn tưởng', '128 phút', 9.3, 'T18', 'https://image.tmdb.org/t/p/w500/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg', 'Cặp đôi bất hảo của vũ trụ Marvel tái xuất trong chuyến phiêu lưu nghẹt thở và hài hước nhất từ trước đến nay.', 'showing', 'https://www.youtube.com/embed/73_1biulkYk', '2D, 3D, IMAX', '26/07/2026', 'Deadpool & Wolverine', CURRENT_TIMESTAMP),
(2, 'Inside Out 2', 'Hoạt hình', '96 phút', 9.5, 'P', 'https://image.tmdb.org/t/p/w500/vpnVM9B6NMmQpWeZvzLvDESb2QY.jpg', 'Cuộc sống của cô bé Riley bùng nổ khi các cảm xúc mới xuất hiện và đảo lộn phòng chỉ huy tâm trí.', 'showing', 'https://www.youtube.com/embed/LEjhY15eCx0', '2D, 3D', '14/06/2026', 'Những Mảnh Ghép Cảm Xúc 2', CURRENT_TIMESTAMP),
(3, 'Godzilla x Kong: The New Empire', 'Hành động, Khoa học viễn tưởng', '115 phút', 8.9, 'T13', 'https://image.tmdb.org/t/p/w500/tMefBSflR6PGQLv7WvFPpKLZkyk.jpg', 'Liên minh khổng lồ giữa Godzilla và Kong chống lại Skar King và đoàn quân quái thú cổ đại.', 'showing', 'https://www.youtube.com/embed/qqrpMRDuPfc', '2D, IMAX', '29/03/2026', 'Godzilla x Kong: Đế Chế Mới', CURRENT_TIMESTAMP),
(4, 'Oppenheimer', 'Tâm lý, Lịch sử, Kịch tính', '180 phút', 9.6, 'T18', 'https://image.tmdb.org/t/p/w500/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg', 'Kiệt tác tiểu sử kịch tính đoạt 7 giải Oscar của đạo diễn Christopher Nolan về vũ khí hạt nhân.', 'showing', 'https://www.youtube.com/embed/uYPbbksJxIg', '2D, IMAX', '11/08/2026', 'Oppenheimer', CURRENT_TIMESTAMP),
(5, 'Alien: Romulus', 'Kinh dị, Khoa học viễn tưởng', '119 phút', 8.7, 'T18', 'https://image.tmdb.org/t/p/w500/b33nnKl1GSFbao4l3fZDDqsMx0F.jpg', 'Nhóm bạn trẻ khám phá trạm không gian bỏ hoang và đối mặt với sinh vật săn mồi tàn bạo nhất vũ trụ.', 'showing', 'https://www.youtube.com/embed/GTNMt84KT0k', '2D, IMAX', '16/08/2026', 'Alien: Romulus', CURRENT_TIMESTAMP),
(6, 'Kung Fu Panda 4', 'Hoạt hình, Hành động', '94 phút', 8.6, 'P', 'https://image.tmdb.org/t/p/w500/kDp1vUBnMpe8ak4rjgl3cLELqjU.jpg', 'Po được chọn trở thành Thủ lĩnh Tinh thần của Thung lũng Bình Yên và phải tìm kiếm một Thần Long Đại Hiệp mới.', 'showing', 'https://www.youtube.com/embed/_inKs4eeHiI', '2D, 3D', '08/03/2026', 'Kung Fu Panda 4', CURRENT_TIMESTAMP),
(7, 'Hành Tinh Khỉ: Vương Quốc Mới', 'Hành động, Khoa học viễn tưởng', '145 phút', 8.5, 'T13', 'https://image.tmdb.org/t/p/w500/gKkl37BQuKTanygYQG1pyYgLVgf.jpg', 'Nhiều thế hệ sau triều đại của Caesar, một chú vượn trẻ dũng cảm dấn thân vào hành trình định đoạt tương lai nhân loại.', 'showing', 'https://www.youtube.com/embed/XtFI7SNtVpY', '2D, IMAX', '10/05/2026', 'Kingdom of the Planet of the Apes', CURRENT_TIMESTAMP),
(8, 'Bad Boys: Ride or Die', 'Hành động, Hài hước', '115 phút', 8.4, 'T18', 'https://image.tmdb.org/t/p/w500/nP6RliHjxsz4irTKsxe8FRhKZYl.jpg', 'Cặp bài trùng cảnh sát Mike Lowrey và Marcus Burnett trở thành những kẻ bị truy nã gắt gao nhất Miami.', 'showing', 'https://www.youtube.com/embed/hRFY_Fesa9Q', '2D', '07/06/2026', 'Những Gã Trai Hư: Chơi Tới Bến', CURRENT_TIMESTAMP),
(9, 'Bạch Xà: Duyên Khởi', 'Hoạt hình, Tiên hiệp, Hành động', '99 phút', 9.1, 'T13', 'https://image.tmdb.org/t/p/w500/30SLnoKmYNyRPkKCYowsrGLRnJA.jpg', 'Siêu phẩm hoạt hình 3D huyền huyễn tiên hiệp mãn nhãn kể về tiền duyên ngàn năm của Bạch Tố Trinh và A Tuyên.', 'showing', 'https://www.youtube.com/embed/JeTy_t4WAfo', '2D, 3D, IMAX', '18/10/2026', 'White Snake (Hoạt Hình 3D Tiên Hiệp)', CURRENT_TIMESTAMP),
(10, 'Na Tra: Ma Đồng Giáng Thế', 'Hoạt hình, Tiên hiệp, Hành động', '110 phút', 9.3, 'P', 'https://image.tmdb.org/t/p/w500/phM9bb6s9c60LA8qwsdk7U1N2cS.jpg', 'Sinh ra mang danh Ma Hoàn nghịch thiên cải mệnh, Na Tra tự định đoạt số phận trong trận đại chiến bảo vệ muôn dân.', 'showing', 'https://www.youtube.com/embed/daoCb8oDf1k', '2D, 3D, IMAX', '20/10/2026', 'Ne Zha (Hoạt Hình 3D Tiên Hiệp)', CURRENT_TIMESTAMP),
(11, 'Dune: Part Two', 'Hành động, Khoa học viễn tưởng', '166 phút', 9.4, 'T16', 'https://image.tmdb.org/t/p/w500/1pdfLvkbY9ohJlCjQH2CZjjYVvJ.jpg', 'Paul Atreides liên minh cùng Chani và tộc người Fremen để trả thù những kẻ hủy diệt gia tộc trên hành tinh Arrakis.', 'showing', 'https://www.youtube.com/embed/Way9Dexny3w', '2D, IMAX', '01/03/2026', 'Hành Tinh Cát: Phần Hai', CURRENT_TIMESTAMP),
(12, 'One Piece Film Red', 'Hoạt hình, Hành động', '115 phút', 8.9, 'T13', 'https://image.tmdb.org/t/p/w500/8ibfhe4P7rhmn3lrPhOZzIJHA2B.jpg', 'Giọng hát của Uta - diva huyền thoại và con gái của Tứ Hoàng Shanks Tóc Đỏ làm rung chuyển toàn bộ Tân Thế Giới.', 'showing', 'https://www.youtube.com/embed/89JWRYEIG-s', '2D, 3D, IMAX', '25/08/2026', 'Đảo Hải Tặc: Phim Red', CURRENT_TIMESTAMP),
(13, 'Suzume', 'Hoạt hình, Tình cảm', '122 phút', 9.0, 'T13', 'https://image.tmdb.org/t/p/w500/yStW1TXF5s7Tbtu9KjIZEaWl6HL.jpg', 'Hành trình khắp đất nước Nhật Bản của nữ sinh Suzume và Souta để đóng lại những cánh cửa giải thoát hiểm họa.', 'showing', 'https://www.youtube.com/embed/9UuwS0mTBNM', '2D, IMAX', '10/03/2026', 'Khóa Chặt Cửa Nào', CURRENT_TIMESTAMP),
(14, 'Spider-Man: Beyond the Spider-Verse', 'Hoạt hình, Hành động, Viễn tưởng', '140 phút', 9.5, 'T13', 'https://image.tmdb.org/t/p/w500/8Vt6mWEReuy4Of61Lnj5Xj704m8.jpg', 'Chương cuối cùng đầy kịch tính của Miles Morales khi phải đối mặt với số phận của toàn bộ mạng lưới đa vũ trụ.', 'upcoming', 'https://www.youtube.com/embed/cqGjhVJWtEg', 'IMAX 3D, 2D', 'Khởi chiếu: 15/11/2026', 'Người Nhện: Du Hành Đa Vũ Trụ', CURRENT_TIMESTAMP),
(15, 'The Batman: Part II', 'Hành động, Tâm lý, Kinh dị', '165 phút', 9.4, 'T18', 'https://image.tmdb.org/t/p/w500/74xTEgt7R36Fpooo50r9T25onhq.jpg', 'Bruce Wayne tiếp tục lột trần những góc khuất thối nát và đối đầu với kẻ thù mới nguy hiểm tại thành phố Gotham ngập lụt.', 'upcoming', 'https://www.youtube.com/embed/mqqft2x_Aa4', 'IMAX, 2D', 'Khởi chiếu: 02/12/2026', 'Hiệp Sĩ Bóng Đêm: Phần 2', CURRENT_TIMESTAMP),
(16, 'Interstellar (Tái Chiếu IMAX Laser)', 'Khoa học viễn tưởng, Tâm lý', '169 phút', 9.8, 'T13', 'https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg', 'Trải nghiệm lại siêu phẩm điện ảnh không gian vĩ đại của Christopher Nolan trên màn hình khổng lồ IMAX Laser.', 'upcoming', 'https://www.youtube.com/embed/zSWdZVtXT7E', 'IMAX Laser', 'Khởi chiếu: 25/11/2026', 'Hố Đen Tử Thần', CURRENT_TIMESTAMP),
(17, 'Avatar: Fire and Ash', 'Khoa học viễn tưởng, Phiêu lưu', '190 phút', 9.6, 'T13', 'https://image.tmdb.org/t/p/w500/t6HIqrRAclMCA60NsSmeqe9RmNV.jpg', 'James Cameron đưa khán giả khám phá một thị tộc Na''vi tàn nhẫn gắn liền với núi lửa và bóng tối trên hành tinh Pandora.', 'upcoming', 'https://www.youtube.com/embed/d9MyW72ELq0', 'IMAX 3D, HFR 3D', 'Khởi chiếu: 18/12/2026', 'Avatar: Lửa & Tro Tàn', CURRENT_TIMESTAMP);


-- ============================================================
-- 4. Cấu trúc bảng `tickets`
-- Lưu trữ thông tin vé đặt từ khách hàng (api.php, admin.php & CinegoModel.php)
-- ============================================================
DROP TABLE IF EXISTS `tickets`;
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `movie_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `movie_poster` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cinema_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hall_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `showtime` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seats` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `format` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `combos` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_amount` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ticket_code` (`ticket_code`),
  KEY `idx_customer_phone` (`customer_phone`),
  KEY `idx_movie_title` (`movie_title`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Hoàn tất khởi tạo CSDL Cinego!
-- ============================================================
