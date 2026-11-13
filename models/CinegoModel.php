<?php

class CinegoModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureSchema();
    }

    private function ensureSchema() {
        if (!$this->pdo) return;

        try {

            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `username` varchar(50) NOT NULL UNIQUE,
                `password` varchar(255) NOT NULL,
                `full_name` varchar(100) NOT NULL DEFAULT 'Quản Trị Viên',
                `role` varchar(20) DEFAULT 'admin',
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            $stmtUser = $this->pdo->prepare("SELECT COUNT(*) FROM `users` WHERE `username` = 'admin'");
            $stmtUser->execute();
            if ($stmtUser->fetchColumn() == 0) {
                $hash = password_hash('123456', PASSWORD_DEFAULT);
                $insAdmin = $this->pdo->prepare("INSERT INTO `users` (`username`, `password`, `full_name`, `role`) VALUES ('admin', :pass, 'Quản Trị Viên CINEGO', 'admin')");
                $insAdmin->execute([':pass' => $hash]);
            }

            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `movies` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `genre` varchar(150) NOT NULL,
                `duration` varchar(50) NOT NULL DEFAULT '120 phút',
                `rating` decimal(3,1) DEFAULT 9.0,
                `age_limit` varchar(20) DEFAULT 'P',
                `poster` text NOT NULL,
                `description` text DEFAULT NULL,
                `status` varchar(50) DEFAULT 'showing',
                `trailer_url` text DEFAULT NULL,
                `formats` varchar(100) DEFAULT '2D, 3D, IMAX',
                `release_date` varchar(100) DEFAULT NULL,
                `vietnamese_title` varchar(255) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            $colsToEnsure = [
                "trailer_url"      => "text DEFAULT NULL",
                "formats"          => "varchar(100) DEFAULT '2D, 3D, IMAX'",
                "release_date"     => "varchar(100) DEFAULT NULL",
                "vietnamese_title" => "varchar(255) DEFAULT NULL"
            ];
            foreach ($colsToEnsure as $col => $def) {
                try {
                    $this->pdo->exec("ALTER TABLE `movies` ADD COLUMN IF NOT EXISTS `$col` $def");
                } catch (\Exception $ex) {

                }
            }

            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `tickets` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `ticket_code` varchar(50) NOT NULL,
                `customer_name` varchar(100) NOT NULL,
                `customer_email` varchar(100) NOT NULL,
                `customer_phone` varchar(20) NOT NULL,
                `movie_title` varchar(255) NOT NULL,
                `movie_poster` text DEFAULT NULL,
                `cinema_name` varchar(255) NOT NULL,
                `hall_name` varchar(100) DEFAULT NULL,
                `showtime` varchar(100) DEFAULT NULL,
                `seats` varchar(255) DEFAULT NULL,
                `format` varchar(50) DEFAULT NULL,
                `combos` text DEFAULT NULL,
                `total_amount` varchar(50) DEFAULT NULL,
                `payment_method` varchar(50) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            if ($this->countMovies() < 17) {
                $this->seedAllMoviesFromData(true);
            }

        } catch (\PDOException $e) {
            error_log("Lỗi CinegoModel ensureSchema: " . $e->getMessage());
        }
    }

    public static function getDefaultMoviesList() {
        return [

            [
                'title'            => 'Deadpool & Wolverine',
                'vietnamese_title' => 'Deadpool & Wolverine',
                'genre'            => 'Hành động, Khoa học viễn tưởng',
                'duration'         => '128 phút',
                'rating'           => 9.3,
                'age_limit'        => 'T18',
                'formats'          => '2D, 3D, IMAX',
                'poster'           => 'https://image.tmdb.org/t/p/w500/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg',
                'trailer_url'      => 'https://www.youtube.com/embed/73_1biulkYk',
                'description'      => 'Cặp đôi bất hảo của vũ trụ Marvel tái xuất trong chuyến phiêu lưu nghẹt thở và hài hước nhất từ trước đến nay.',
                'release_date'     => '26/07/2026',
                'status'           => 'showing'
            ],
            [
                'title'            => 'Inside Out 2',
                'vietnamese_title' => 'Những Mảnh Ghép Cảm Xúc 2',
                'genre'            => 'Hoạt hình',
                'duration'         => '96 phút',
                'rating'           => 9.5,
                'age_limit'        => 'P',
                'formats'          => '2D, 3D',
                'poster'           => 'https://image.tmdb.org/t/p/w500/vpnVM9B6NMmQpWeZvzLvDESb2QY.jpg',
                'trailer_url'      => 'https://www.youtube.com/embed/LEjhY15eCx0',
                'description'      => 'Cuộc sống của cô bé Riley bùng nổ khi các cảm xúc mới xuất hiện và đảo lộn phòng chỉ huy tâm trí.',
                'release_date'     => '14/06/2026',
                'status'           => 'showing'
            ],
            [
                'title'            => 'Godzilla x Kong: The New Empire',
                'vietnamese_title' => 'Godzilla x Kong: Đế Chế Mới',
                'genre'            => 'Hành động, Khoa học viễn tưởng',
                'duration'         => '115 phút',
                'rating'           => 8.9,
                'age_limit'        => 'T13',
                'formats'          => '2D, IMAX',
                'poster'           => 'https://image.tmdb.org/t/p/w500/tMefBSflR6PGQLv7WvFPpKLZkyk.jpg',
                'trailer_url'      => 'https://www.youtube.com/embed/qqrpMRDuPfc',
                'description'      => 'Liên minh khổng lồ giữa Godzilla và Kong chống lại Skar King và đoàn quân quái thú cổ đại.',
                'release_date'     => '29/03/2026',
                'status'           => 'showing'
            ],
            [
                'title'            => 'Oppenheimer',
                'vietnamese_title' => 'Oppenheimer',
                'genre'            => 'Tâm lý, Lịch sử, Kịch tính',
                'duration'         => '180 phút',
                'rating'           => 9.6,
                'age_limit'        => 'T18',
                'formats'          => '2D, IMAX',
                'poster'           => 'https://image.tmdb.org/t/p/w500/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg',
                'trailer_url'      => 'https://www.youtube.com/embed/uYPbbksJxIg',
                'description'      => 'Kiệt tác tiểu sử kịch tính đoạt 7 giải Oscar của đạo diễn Christopher Nolan về vũ khí hạt nhân.',
                'release_date'     => '11/08/2026',
                'status'           => 'showing'
            ],
            [
                'title'            => 'Alien: Romulus',
                'vietnamese_title' => 'Alien: Romulus',
                'genre'            => 'Kinh dị, Khoa học viễn tưởng',
                'duration'         => '119 phút',
                'rating'           => 8.7,
                'age_limit'        => 'T18',
                'formats'          => '2D, IMAX',
                'poster'           => 'https://image.tmdb.org/t/p/w500/b33nnKl1GSFbao4l3fZDDqsMx0F.jpg',
                'trailer_url'      => 'https://www.youtube.com/embed/GTNMt84KT0k',
                'description'      => 'Nhóm bạn trẻ khám phá trạm không gian bỏ hoang và đối mặt với sinh vật săn mồi tàn bạo nhất vũ trụ.',
                'release_date'     => '16/08/2026',
                'status'           => 'showing'
            ],
            [
                'title'            => 'Kung Fu Panda 4',
                'vietnamese_title' => 'Kung Fu Panda 4',
                'genre'            => 'Hoạt hình, Hành động',
                'duration'         => '94 phút',
                'rating'           => 8.6,
                'age_limit'        => 'P',
                'formats'          => '2D, 3D',
                'poster'           => 'https://image.tmdb.org/t/p/w500/kDp1vUBnMpe8ak4rjgl3cLELqjU.jpg',
                'trailer_url'      => 'https://www.youtube.com/embed/_inKs4eeHiI',
                'description'      => 'Po được chọn trở thành Thủ lĩnh Tinh thần của Thung lũng Bình Yên và phải tìm kiếm một Thần Long Đại Hiệp mới.',
                'release_date'     => '08/03/2026',
                'status'           => 'showing'
            ],
            [
                'title'            => 'Hành Tinh Khỉ: Vương Quốc Mới',
                'vietnamese_title' => 'Kingdom of the Planet of the Apes',
                'genre'            => 'Hành động, Khoa học viễn tưởng',
                'duration'         => '145 phút',
                'rating'           => 8.5,
                'age_limit'        => 'T13',
                'formats'          => '2D, IMAX',
                'poster'           => 'https://image.tmdb.org/t/p/w500/gKkl37BQuKTanygYQG1pyYgLVgf.jpg',
                'trailer_url'      => 'https://www.youtube.com/embed/XtFI7SNtVpY',
                'description'      => 'Nhiều thế hệ sau triều đại của Caesar, một chú vượn trẻ dũng cảm dấn thân vào hành trình định đoạt tương lai nhân loại.',
                'release_date'     => '10/05/2026',
                'status'           => 'showing'
            ],
            [
                'title'            => 'Bad Boys: Ride or Die',
                'vietnamese_title' => 'Những Gã Trai Hư: Chơi Tới Bến',
                'genre'            => 'Hành động, Hài hước',
                'duration'         => '115 phút',
                'rating'           => 8.4,
                'age_limit'        => 'T18',
                'formats'          => '2D',
                'poster'           => 'https://image.tmdb.org/t/p/w500/nP6RliHjxsz4irTKsxe8FRhKZYl.jpg',
                'trailer_url'      => 'https://www.youtube.com/embed/hRFY_Fesa9Q',
                'description'      => 'Cặp bài trùng cảnh sát Mike Lowrey và Marcus Burnett trở thành những kẻ bị truy nã gắt gao nhất Miami.',
                'release_date'     => '07/06/2026',
                'status'           => 'showing'
            ],
            [
                'title'            => 'Bạch Xà: Duyên Khởi',
                'vietnamese_title' => 'White Snake (Hoạt Hình 3D Tiên Hiệp)',
                'genre'            => 'Hoạt hình, Tiên hiệp, Hành động',
                'duration'         => '99 phút',
                'rating'           => 9.1,
                'age_limit'        => 'T13',
                'formats'          => '2D, 3D, IMAX',
                'poster'           => 'https://image.tmdb.org/t/p/w500/30SLnoKmYNyRPkKCYowsrGLRnJA.jpg',
                'trailer_url'      => 'https://www.youtube.com/embed/JeTy_t4WAfo',
                'description'      => 'Siêu phẩm hoạt hình 3D huyền huyễn tiên hiệp mãn nhãn kể về tiền duyên ngàn năm của Bạch Tố Trinh và A Tuyên.',
                'release_date'     => '18/10/2026',
                'status'           => 'showing'
            ],
            [
                'title'            => 'Na Tra: Ma Đồng Giáng Thế',
                'vietnamese_title' => 'Ne Zha (Hoạt Hình 3D Tiên Hiệp)',
                'genre'            => 'Hoạt hình, Tiên hiệp, Hành động',
                'duration'         => '110 phút',
                'rating'           => 9.3,
                'age_limit'        => 'P',
                'formats'          => '2D, 3D, IMAX',
                'poster'           => 'https://image.tmdb.org/t/p/w500/phM9bb6s9c60LA8qwsdk7U1N2cS.jpg',
                'trailer_url'      => 'https://www.youtube.com/embed/daoCb8oDf1k',
                'description'      => 'Sinh ra mang danh Ma Hoàn nghịch thiên cải mệnh, Na Tra tự định đoạt số phận trong trận đại chiến bảo vệ muôn dân.',
                'release_date'     => '20/10/2026',
                'status'           => 'showing'
            ],
            [
                'title'            => 'Dune: Part Two',
                'vietnamese_title' => 'Hành Tinh Cát: Phần Hai',
                'genre'            => 'Hành động, Khoa học viễn tưởng',
                'duration'         => '166 phút',
                'rating'           => 9.4,
                'age_limit'        => 'T16',
                'formats'          => '2D, IMAX',
                'poster'           => 'https://image.tmdb.org/t/p/w500/1pdfLvkbY9ohJlCjQH2CZjjYVvJ.jpg',
                'trailer_url'      => 'https://www.youtube.com/embed/Way9Dexny3w',
                'description'      => 'Paul Atreides liên minh cùng Chani và tộc người Fremen để trả thù những kẻ hủy diệt gia tộc trên hành tinh Arrakis.',
                'release_date'     => '01/03/2026',
                'status'           => 'showing'
            ],
            [
                'title'            => 'One Piece Film Red',
                'vietnamese_title' => 'Đảo Hải Tặc: Phim Red',
                'genre'            => 'Hoạt hình, Hành động',
                'duration'         => '115 phút',
                'rating'           => 8.9,
                'age_limit'        => 'T13',
                'formats'          => '2D, 3D, IMAX',
                'poster'           => 'https://image.tmdb.org/t/p/w500/8ibfhe4P7rhmn3lrPhOZzIJHA2B.jpg',
                'trailer_url'      => 'https://www.youtube.com/embed/89JWRYEIG-s',
                'description'      => 'Giọng hát của Uta - diva huyền thoại và con gái của Tứ Hoàng Shanks Tóc Đỏ làm rung chuyển toàn bộ Tân Thế Giới.',
                'release_date'     => '25/08/2026',
                'status'           => 'showing'
            ],
            [
                'title'            => 'Suzume',
                'vietnamese_title' => 'Khóa Chặt Cửa Nào',
                'genre'            => 'Hoạt hình, Tình cảm',
                'duration'         => '122 phút',
                'rating'           => 9.0,
                'age_limit'        => 'T13',
                'formats'          => '2D, IMAX',
                'poster'           => 'https://image.tmdb.org/t/p/w500/yStW1TXF5s7Tbtu9KjIZEaWl6HL.jpg',
                'trailer_url'      => 'https://www.youtube.com/embed/9UuwS0mTBNM',
                'description'      => 'Hành trình khắp đất nước Nhật Bản của nữ sinh Suzume và Souta để đóng lại những cánh cửa giải thoát hiểm họa.',
                'release_date'     => '10/03/2026',
                'status'           => 'showing'
            ],

            [
                'title'            => 'Spider-Man: Beyond the Spider-Verse',
                'vietnamese_title' => 'Người Nhện: Du Hành Đa Vũ Trụ',
                'genre'            => 'Hoạt hình, Hành động, Viễn tưởng',
                'duration'         => '140 phút',
                'rating'           => 9.5,
                'age_limit'        => 'T13',
                'formats'          => 'IMAX 3D, 2D',
                'poster'           => 'https://image.tmdb.org/t/p/w500/8Vt6mWEReuy4Of61Lnj5Xj704m8.jpg',
                'trailer_url'      => 'https://www.youtube.com/embed/cqGjhVJWtEg',
                'description'      => 'Chương cuối cùng đầy kịch tính của Miles Morales khi phải đối mặt với số phận của toàn bộ mạng lưới đa vũ trụ.',
                'release_date'     => 'Khởi chiếu: 15/11/2026',
                'status'           => 'upcoming'
            ],
            [
                'title'            => 'The Batman: Part II',
                'vietnamese_title' => 'Hiệp Sĩ Bóng Đêm: Phần 2',
                'genre'            => 'Hành động, Tâm lý, Kinh dị',
                'duration'         => '165 phút',
                'rating'           => 9.4,
                'age_limit'        => 'T18',
                'formats'          => 'IMAX, 2D',
                'poster'           => 'https://image.tmdb.org/t/p/w500/74xTEgt7R36Fpooo50r9T25onhq.jpg',
                'trailer_url'      => 'https://www.youtube.com/embed/mqqft2x_Aa4',
                'description'      => 'Bruce Wayne tiếp tục lột trần những góc khuất thối nát và đối đầu với kẻ thù mới nguy hiểm tại thành phố Gotham ngập lụt.',
                'release_date'     => 'Khởi chiếu: 02/12/2026',
                'status'           => 'upcoming'
            ],
            [
                'title'            => 'Interstellar (Tái Chiếu IMAX Laser)',
                'vietnamese_title' => 'Hố Đen Tử Thần',
                'genre'            => 'Khoa học viễn tưởng, Tâm lý',
                'duration'         => '169 phút',
                'rating'           => 9.8,
                'age_limit'        => 'T13',
                'formats'          => 'IMAX Laser',
                'poster'           => 'https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg',
                'trailer_url'      => 'https://www.youtube.com/embed/zSWdZVtXT7E',
                'description'      => 'Trải nghiệm lại siêu phẩm điện ảnh không gian vĩ đại của Christopher Nolan trên màn hình khổng lồ IMAX Laser.',
                'release_date'     => 'Khởi chiếu: 25/11/2026',
                'status'           => 'upcoming'
            ],
            [
                'title'            => 'Avatar: Fire and Ash',
                'vietnamese_title' => 'Avatar: Lửa & Tro Tàn',
                'genre'            => 'Khoa học viễn tưởng, Phiêu lưu',
                'duration'         => '190 phút',
                'rating'           => 9.6,
                'age_limit'        => 'T13',
                'formats'          => 'IMAX 3D, HFR 3D',
                'poster'           => 'https://image.tmdb.org/t/p/w500/t6HIqrRAclMCA60NsSmeqe9RmNV.jpg',
                'trailer_url'      => 'https://www.youtube.com/embed/d9MyW72ELq0',
                'description'      => 'James Cameron đưa khán giả khám phá một thị tộc Na\'vi tàn nhẫn gắn liền với núi lửa và bóng tối trên hành tinh Pandora.',
                'release_date'     => 'Khởi chiếu: 18/12/2026',
                'status'           => 'upcoming'
            ]
        ];
    }

    public function seedAllMoviesFromData($force = false) {
        if (!$this->pdo) return false;
        try {
            $defaultMovies = self::getDefaultMoviesList();

            if ($force) {

                $this->pdo->exec("DELETE FROM `movies`");
                $this->pdo->exec("ALTER TABLE `movies` AUTO_INCREMENT = 1");

                $insertSql = "INSERT INTO `movies` (
                    `title`, `genre`, `duration`, `rating`, `age_limit`,
                    `poster`, `description`, `status`, `trailer_url`,
                    `formats`, `release_date`, `vietnamese_title`
                ) VALUES (
                    :title, :genre, :duration, :rating, :age_limit,
                    :poster, :description, :status, :trailer_url,
                    :formats, :release_date, :vietnamese_title
                )";
                $stmt = $this->pdo->prepare($insertSql);

                foreach ($defaultMovies as $m) {
                    $stmt->execute([
                        ':title'            => $m['title'],
                        ':genre'            => $m['genre'],
                        ':duration'         => $m['duration'],
                        ':rating'           => $m['rating'],
                        ':age_limit'        => $m['age_limit'],
                        ':poster'           => $m['poster'],
                        ':description'      => $m['description'],
                        ':status'           => $m['status'],
                        ':trailer_url'      => $m['trailer_url'],
                        ':formats'          => $m['formats'],
                        ':release_date'     => $m['release_date'],
                        ':vietnamese_title' => $m['vietnamese_title']
                    ]);
                }
                return true;
            } else {

                $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM `movies` WHERE `title` = :t");
                $insertSql = "INSERT INTO `movies` (
                    `title`, `genre`, `duration`, `rating`, `age_limit`,
                    `poster`, `description`, `status`, `trailer_url`,
                    `formats`, `release_date`, `vietnamese_title`
                ) VALUES (
                    :title, :genre, :duration, :rating, :age_limit,
                    :poster, :description, :status, :trailer_url,
                    :formats, :release_date, :vietnamese_title
                )";
                $stmtInsert = $this->pdo->prepare($insertSql);

                foreach ($defaultMovies as $m) {
                    $stmtCheck->execute([':t' => $m['title']]);
                    if ($stmtCheck->fetchColumn() == 0) {
                        $stmtInsert->execute([
                            ':title'            => $m['title'],
                            ':genre'            => $m['genre'],
                            ':duration'         => $m['duration'],
                            ':rating'           => $m['rating'],
                            ':age_limit'        => $m['age_limit'],
                            ':poster'           => $m['poster'],
                            ':description'      => $m['description'],
                            ':status'           => $m['status'],
                            ':trailer_url'      => $m['trailer_url'],
                            ':formats'          => $m['formats'],
                            ':release_date'     => $m['release_date'],
                            ':vietnamese_title' => $m['vietnamese_title']
                        ]);
                    }
                }
                return true;
            }
        } catch (\PDOException $e) {
            error_log("Lỗi seedAllMoviesFromData: " . $e->getMessage());
            return false;
        }
    }

    public function checkLogin($username, $password) {
        if (!$this->pdo) return false;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `username` = :user LIMIT 1");
            $stmt->execute([':user' => trim($username)]);
            $user = $stmt->fetch();

            if ($user) {

                if (password_verify($password, $user['password'])) {
                    return $user;
                }

                if ($password === $user['password']) {
                    return $user;
                }
            }
            return false;
        } catch (\PDOException $e) {
            error_log("Lỗi checkLogin: " . $e->getMessage());
            return false;
        }
    }

    public function getUserByUsername($username) {
        if (!$this->pdo) return null;
        try {
            $stmt = $this->pdo->prepare("SELECT `id`, `username`, `full_name`, `role`, `created_at` FROM `users` WHERE `username` = :user LIMIT 1");
            $stmt->execute([':user' => trim($username)]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            return null;
        }
    }

    public function getAllMovies($status = null, $keyword = null) {
        if (!$this->pdo) return [];
        try {
            $sql = "SELECT * FROM `movies` WHERE 1=1";
            $params = [];

            if (!empty($status)) {
                if ($status === 'showing' || $status === 'now_showing') {
                    $sql .= " AND `status` IN ('showing', 'now_showing')";
                } elseif ($status === 'upcoming' || $status === 'coming_soon') {
                    $sql .= " AND `status` IN ('upcoming', 'coming_soon')";
                } else {
                    $sql .= " AND `status` = :st";
                    $params[':st'] = $status;
                }
            }

            if (!empty($keyword)) {
                $kw = '%' . trim($keyword) . '%';
                $sql .= " AND (`title` LIKE :kw1 OR `genre` LIKE :kw2 OR `vietnamese_title` LIKE :kw3)";
                $params[':kw1'] = $kw;
                $params[':kw2'] = $kw;
                $params[':kw3'] = $kw;
            }

            $sql .= " ORDER BY `id` ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Lỗi getAllMovies: " . $e->getMessage());
            return [];
        }
    }

    public function getMovieById($id) {
        if (!$this->pdo) return null;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM `movies` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => (int)$id]);
            $res = $stmt->fetch();
            return $res ? $res : null;
        } catch (\PDOException $e) {
            error_log("Lỗi getMovieById: " . $e->getMessage());
            return null;
        }
    }

    public function addMovie($data) {
        if (!$this->pdo) return false;
        try {
            $sql = "INSERT INTO `movies` (
                `title`, `genre`, `duration`, `rating`, `age_limit`,
                `poster`, `description`, `status`, `trailer_url`,
                `formats`, `release_date`, `vietnamese_title`
            ) VALUES (
                :title, :genre, :duration, :rating, :age_limit,
                :poster, :description, :status, :trailer_url,
                :formats, :release_date, :vietnamese_title
            )";

            $status = trim($data['status'] ?? 'showing');
            if ($status === 'now_showing') $status = 'showing';
            if ($status === 'coming_soon') $status = 'upcoming';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':title'            => trim($data['title'] ?? ''),
                ':genre'            => trim($data['genre'] ?? $data['genres'] ?? 'Hành động'),
                ':duration'         => trim($data['duration'] ?? '120 phút'),
                ':rating'           => floatval($data['rating'] ?? 9.0),
                ':age_limit'        => trim($data['age_limit'] ?? $data['age_rating'] ?? 'P'),
                ':poster'           => trim($data['poster'] ?? ''),
                ':description'      => $data['description'] ?? '',
                ':status'           => $status,
                ':trailer_url'      => trim($data['trailer_url'] ?? ''),
                ':formats'          => trim($data['formats'] ?? '2D, 3D, IMAX'),
                ':release_date'     => trim($data['release_date'] ?? date('d/m/Y')),
                ':vietnamese_title' => trim($data['vietnamese_title'] ?? $data['title'] ?? '')
            ]);

            return (int)$this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Lỗi addMovie: " . $e->getMessage());
            return false;
        }
    }

    public function updateMovie($id, $data) {
        if (!$this->pdo) return false;
        try {
            $sql = "UPDATE `movies` SET
                `title` = :title,
                `genre` = :genre,
                `duration` = :duration,
                `rating` = :rating,
                `age_limit` = :age_limit,
                `poster` = :poster,
                `description` = :description,
                `status` = :status,
                `trailer_url` = :trailer_url,
                `formats` = :formats,
                `release_date` = :release_date,
                `vietnamese_title` = :vietnamese_title
                WHERE `id` = :id";

            $status = trim($data['status'] ?? 'showing');
            if ($status === 'now_showing') $status = 'showing';
            if ($status === 'coming_soon') $status = 'upcoming';

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id'               => (int)$id,
                ':title'            => trim($data['title'] ?? ''),
                ':genre'            => trim($data['genre'] ?? $data['genres'] ?? 'Hành động'),
                ':duration'         => trim($data['duration'] ?? '120 phút'),
                ':rating'           => floatval($data['rating'] ?? 9.0),
                ':age_limit'        => trim($data['age_limit'] ?? $data['age_rating'] ?? 'P'),
                ':poster'           => trim($data['poster'] ?? ''),
                ':description'      => $data['description'] ?? '',
                ':status'           => $status,
                ':trailer_url'      => trim($data['trailer_url'] ?? ''),
                ':formats'          => trim($data['formats'] ?? '2D, 3D, IMAX'),
                ':release_date'     => trim($data['release_date'] ?? date('d/m/Y')),
                ':vietnamese_title' => trim($data['vietnamese_title'] ?? $data['title'] ?? '')
            ]);
        } catch (\PDOException $e) {
            error_log("Lỗi updateMovie: " . $e->getMessage());
            return false;
        }
    }

    public function deleteMovie($id) {
        if (!$this->pdo) return false;
        try {
            $stmt = $this->pdo->prepare("DELETE FROM `movies` WHERE `id` = :id");
            return $stmt->execute([':id' => (int)$id]);
        } catch (\PDOException $e) {
            error_log("Lỗi deleteMovie: " . $e->getMessage());
            return false;
        }
    }

    public function countMovies($status = null) {
        if (!$this->pdo) return 0;
        try {
            if ($status) {
                if ($status === 'showing' || $status === 'now_showing') {
                    $stmt = $this->pdo->query("SELECT COUNT(*) FROM `movies` WHERE `status` IN ('showing', 'now_showing')");
                } elseif ($status === 'upcoming' || $status === 'coming_soon') {
                    $stmt = $this->pdo->query("SELECT COUNT(*) FROM `movies` WHERE `status` IN ('upcoming', 'coming_soon')");
                } else {
                    $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM `movies` WHERE `status` = :st");
                    $stmt->execute([':st' => $status]);
                }
            } else {
                $stmt = $this->pdo->query("SELECT COUNT(*) FROM `movies`");
            }
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            return 0;
        }
    }

    public function getAllTickets($keyword = null) {
        if (!$this->pdo) return [];
        try {
            $sql = "SELECT * FROM `tickets` WHERE 1=1";
            $params = [];

            if (!empty($keyword)) {
                $kw = '%' . trim($keyword) . '%';
                $sql .= " AND (`ticket_code` LIKE :kw1 OR `customer_phone` LIKE :kw2 OR `customer_name` LIKE :kw3 OR `movie_title` LIKE :kw4)";
                $params[':kw1'] = $kw;
                $params[':kw2'] = $kw;
                $params[':kw3'] = $kw;
                $params[':kw4'] = $kw;
            }

            $sql .= " ORDER BY `id` DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Lỗi getAllTickets: " . $e->getMessage());
            return [];
        }
    }

    public function getTicketById($id) {
        if (!$this->pdo) return null;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM `tickets` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => (int)$id]);
            $res = $stmt->fetch();
            return $res ? $res : null;
        } catch (\PDOException $e) {
            return null;
        }
    }

    public function deleteTicket($identifier) {
        if (!$this->pdo || empty($identifier)) return false;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM `tickets` WHERE `id` = :id OR `ticket_code` = :code LIMIT 1");
            $stmt->execute([
                ':id'   => is_numeric($identifier) ? (int)$identifier : 0,
                ':code' => trim($identifier)
            ]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$ticket) return false;

            $delStmt = $this->pdo->prepare("DELETE FROM `tickets` WHERE `id` = :id");
            $success = $delStmt->execute([':id' => (int)$ticket['id']]);

            if ($success) {
                $cleanCode = preg_replace('/[^a-zA-Z0-9_\-]/', '', $ticket['ticket_code']);
                $filePath = dirname(__DIR__) . '/sent-emails/ticket_' . $cleanCode . '.html';
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
                return $ticket;
            }
            return false;
        } catch (\PDOException $e) {
            error_log("Lỗi deleteTicket: " . $e->getMessage());
            return false;
        }
    }

    public function countTickets() {
        if (!$this->pdo) return 0;
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM `tickets`");
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            return 0;
        }
    }

    public function getOccupiedSeats($movieTitle, $showtime = null) {
        if (!$this->pdo || empty($movieTitle)) return [];
        try {
            $movieTrimmed = trim($movieTitle);
            $movieDecoded = html_entity_decode($movieTrimmed, ENT_QUOTES, 'UTF-8');
            $movieEncoded = htmlspecialchars($movieTrimmed, ENT_QUOTES, 'UTF-8');

            $sql = "SELECT `seats` FROM `tickets` WHERE (
                `movie_title` = :m1
                OR `movie_title` = :m2
                OR `movie_title` = :m3
                OR `movie_title` LIKE :m4
            )";

            $params = [
                ':m1' => $movieTrimmed,
                ':m2' => $movieDecoded,
                ':m3' => $movieEncoded,
                ':m4' => '%' . $movieTrimmed . '%'
            ];

            if (!empty($showtime)) {
                $stTrimmed = trim($showtime);

                preg_match('/(\d{1,2}:\d{2})/', $stTrimmed, $matches);
                $timeOnly = !empty($matches[1]) ? $matches[1] : null;

                if ($timeOnly) {
                    $sql .= " AND (`showtime` LIKE :st1 OR `showtime` LIKE :st2)";
                    $params[':st1'] = '%' . $stTrimmed . '%';
                    $params[':st2'] = '%' . $timeOnly . '%';
                } else {
                    $sql .= " AND `showtime` LIKE :st1";
                    $params[':st1'] = '%' . $stTrimmed . '%';
                }
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $occupiedSeats = [];
            foreach ($rows as $seatStr) {
                if (empty($seatStr)) continue;
                $parts = preg_split('/[,;\s]+/', $seatStr);
                foreach ($parts as $p) {
                    $p = trim($p);
                    if (!empty($p)) {
                        $occupiedSeats[] = $p;

                        if (preg_match('/^([A-Za-z]+)(\d+)$/', $p, $m)) {
                            $rowLetter = strtoupper($m[1]);
                            $colNum = (int)$m[2];
                            $occupiedSeats[] = $rowLetter . $colNum;
                            $occupiedSeats[] = $rowLetter . sprintf('%02d', $colNum);
                        }
                    }
                }
            }

            return array_values(array_unique($occupiedSeats));
        } catch (\PDOException $e) {
            error_log("Lỗi getOccupiedSeats: " . $e->getMessage());
            return [];
        }
    }

    public function searchCustomerTickets($keyword) {
        if (!$this->pdo || empty($keyword)) return [];
        try {
            $kw = trim($keyword);
            $kwLike = '%' . $kw . '%';

            $sql = "SELECT * FROM `tickets`
                    WHERE `ticket_code` LIKE :kw1
                       OR `customer_phone` LIKE :kw2
                       OR `customer_email` LIKE :kw3
                       OR `customer_name` LIKE :kw4
                    ORDER BY `id` DESC LIMIT 30";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':kw1' => $kwLike,
                ':kw2' => $kwLike,
                ':kw3' => $kwLike,
                ':kw4' => $kwLike
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Lỗi searchCustomerTickets: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalRevenue() {
        if (!$this->pdo) return 0;
        try {
            $stmt = $this->pdo->query("SELECT `total_amount` FROM `tickets`");
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $total = 0;
            foreach ($rows as $amount) {
                if (empty($amount)) continue;

                $numeric = preg_replace('/[^0-9]/', '', $amount);
                if (!empty($numeric)) {
                    $total += (int)$numeric;
                }
            }
            return $total;
        } catch (\PDOException $e) {
            error_log("Lỗi getTotalRevenue: " . $e->getMessage());
            return 0;
        }
    }

    public function getTopBookedMovie() {
        if (!$this->pdo) return null;
        try {
            $sql = "SELECT `movie_title`, COUNT(*) as `ticket_count`
                    FROM `tickets`
                    WHERE `movie_title` IS NOT NULL AND TRIM(`movie_title`) != ''
                    GROUP BY `movie_title`
                    ORDER BY `ticket_count` DESC, `id` DESC
                    LIMIT 1";
            $stmt = $this->pdo->query($sql);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) {
                $res['movie_title'] = html_entity_decode($res['movie_title'], ENT_QUOTES, 'UTF-8');
                return $res;
            }
            return null;
        } catch (\PDOException $e) {
            error_log("Lỗi getTopBookedMovie: " . $e->getMessage());
            return null;
        }
    }
}
