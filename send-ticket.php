<?php

header('Content-Type: application/json; charset=utf-8');

if (file_exists(__DIR__ . '/config.secret.php')) {
    require_once __DIR__ . '/config.secret.php';
}

require_once __DIR__ . '/libs/PHPMailer/Exception.php';
require_once __DIR__ . '/libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$rawInput = file_get_contents('php://input');
if (empty($rawInput)) {
    $rawInput = @file_get_contents('php://stdin');
}
if (!empty($rawInput)) {

    $rawInput = preg_replace('/^\xEF\xBB\xBF/', '', $rawInput);
}
$data = json_decode($rawInput, true);
if (!$data && !empty($_POST)) {
    $data = $_POST;
}

if (!$data) {

    $data = [
        'ticketCode'    => 'CGO-' . rand(100000, 999999),
        'customerName'  => 'Phan Đình An',
        'customerEmail' => 'dinhan33316@gmail.com',
        'customerPhone' => '0396 118 147',
        'movieTitle'    => 'Deadpool & Wolverine',
        'moviePoster'   => 'https://image.tmdb.org/t/p/w500/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg',
        'cinemaName'    => 'CINEGO Landmark 81 IMAX',
        'hallName'      => 'Phòng Chiếu IMAX Laser',
        'showtime'      => '19:20 • ' . date('d/m/Y'),
        'seats'         => 'F06, F07',
        'format'        => 'IMAX 3D Laser',
        'combos'        => 'Combo Couple Bestseller x 1',
        'totalAmount'   => '350.000 đ',
        'paymentMethod' => 'Ví MoMo (Đã thanh toán)'
    ];
}

$rawTicketCode    = !empty($data['ticketCode']) ? trim($data['ticketCode']) : ('CGO-' . rand(100000, 999999));
$rawCustomerName  = !empty($data['customerName']) ? trim($data['customerName']) : 'Quý Khách';
$rawCustomerEmail = !empty($data['customerEmail']) ? filter_var(trim($data['customerEmail']), FILTER_SANITIZE_EMAIL) : 'dinhan33316@gmail.com';
$rawCustomerPhone = !empty($data['customerPhone']) ? trim($data['customerPhone']) : '0901 234 567';
$rawMovieTitle    = !empty($data['movieTitle']) ? trim($data['movieTitle']) : 'Phim Chiếu Rạp CINEGO';
$rawMoviePoster   = !empty($data['moviePoster']) ? trim($data['moviePoster']) : 'https://image.tmdb.org/t/p/w500/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg';
$rawCinemaName    = !empty($data['cinemaName']) ? trim($data['cinemaName']) : 'CINEGO Landmark 81 IMAX';
$rawHallName      = !empty($data['hallName']) ? trim($data['hallName']) : 'Phòng Chiếu 01';
$rawShowtime      = !empty($data['showtime']) ? trim($data['showtime']) : date('H:i • d/m/Y');
$rawSeats         = !empty($data['seats']) ? trim($data['seats']) : 'A01';
$rawFormat        = !empty($data['format']) ? trim($data['format']) : '2D Phụ đề';
$rawCombos        = !empty($data['combos']) ? trim($data['combos']) : 'Không có';
$rawTotalAmount   = !empty($data['totalAmount']) ? trim($data['totalAmount']) : '0 đ';
$rawPaymentMethod = !empty($data['paymentMethod']) ? trim($data['paymentMethod']) : 'Ví MoMo';

$ticketCode    = htmlspecialchars($rawTicketCode, ENT_QUOTES, 'UTF-8');
$customerName  = htmlspecialchars($rawCustomerName, ENT_QUOTES, 'UTF-8');
$customerEmail = htmlspecialchars($rawCustomerEmail, ENT_QUOTES, 'UTF-8');
$customerPhone = htmlspecialchars($rawCustomerPhone, ENT_QUOTES, 'UTF-8');
$movieTitle    = htmlspecialchars($rawMovieTitle, ENT_QUOTES, 'UTF-8');
$moviePoster   = htmlspecialchars($rawMoviePoster, ENT_QUOTES, 'UTF-8');
$cinemaName    = htmlspecialchars($rawCinemaName, ENT_QUOTES, 'UTF-8');
$hallName      = htmlspecialchars($rawHallName, ENT_QUOTES, 'UTF-8');
$showtime      = htmlspecialchars($rawShowtime, ENT_QUOTES, 'UTF-8');
$seats         = htmlspecialchars($rawSeats, ENT_QUOTES, 'UTF-8');
$format        = htmlspecialchars($rawFormat, ENT_QUOTES, 'UTF-8');
$combos        = htmlspecialchars($rawCombos, ENT_QUOTES, 'UTF-8');
$totalAmount   = htmlspecialchars($rawTotalAmount, ENT_QUOTES, 'UTF-8');
$paymentMethod = htmlspecialchars($rawPaymentMethod, ENT_QUOTES, 'UTF-8');

$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=10&data=CINEGO-TICKET-" . urlencode($ticketCode);

$emailHtml = '
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vé Điện Tử CINEGO</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0B0B0F; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; color: #FFFFFF;">
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #0B0B0F; padding: 30px 10px;">
    <tr>
      <td align="center">

        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; background-color: #14141B; border-radius: 20px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 50px rgba(0,0,0,0.8);">

          <tr>
            <td align="center" style="padding: 28px 20px; background: linear-gradient(180deg, #1A1A24 0%, #14141B 100%); border-bottom: 1px solid rgba(255,255,255,0.08);">
              <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td align="center">
                    <span style="display: inline-block; background-color: #E50914; color: #FFFFFF; font-weight: 900; font-size: 22px; padding: 6px 14px; border-radius: 10px; letter-spacing: 2px;">CINE<span style="color: #FFFFFF;">GO</span></span>
                  </td>
                </tr>
                <tr>
                  <td align="center" style="padding-top: 8px;">
                    <span style="color: #A1A1AA; font-size: 11px; letter-spacing: 3px; text-transform: uppercase;">PREMIUM CINEMA EXPERIENCE</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td align="center" style="padding: 24px 20px 10px 20px;">
              <span style="display: inline-block; background-color: rgba(16, 185, 129, 0.15); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 11px; font-weight: bold; text-transform: uppercase; padding: 6px 16px; border-radius: 20px; letter-spacing: 1px;">
                ✓ XÁC NHẬN ĐẶT VÉ THÀNH CÔNG
              </span>
              <h1 style="color: #FFFFFF; font-size: 22px; font-weight: 800; margin: 14px 0 6px 0;">Cảm ơn bạn, ' . $customerName . '!</h1>
              <p style="color: #A1A1AA; font-size: 13px; line-height: 1.5; margin: 0; max-width: 480px;">
                Vé xem phim điện tử của bạn đã được thanh toán thành công. Dưới đây là thông tin chi tiết và mã QR để vào phòng chiếu.
              </p>
            </td>
          </tr>

          <tr>
            <td style="padding: 20px;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #0E0E13; border-radius: 16px; border: 1px solid #272733; overflow: hidden;">

                <tr>
                  <td style="padding: 20px; border-bottom: 1px dashed #2E2E3E;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td width="90" valign="top">
                          <img src="' . $moviePoster . '" alt="' . $movieTitle . '" width="90" height="130" style="border-radius: 10px; display: block; object-fit: cover; border: 1px solid rgba(255,255,255,0.1);" />
                        </td>
                        <td valign="top" style="padding-left: 18px;">
                          <span style="background-color: #E50914; color: #FFFFFF; font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 4px; text-transform: uppercase; display: inline-block; margin-bottom: 6px;">' . $format . '</span>
                          <h2 style="color: #FFFFFF; font-size: 18px; font-weight: 800; margin: 0 0 6px 0; line-height: 1.3;">' . $movieTitle . '</h2>
                          <p style="color: #E50914; font-size: 13px; font-weight: bold; margin: 0 0 4px 0;">' . $cinemaName . '</p>
                          <p style="color: #F59E0B; font-size: 12px; font-weight: 600; margin: 0;">' . $hallName . '</p>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <tr>
                  <td style="padding: 18px 20px; background-color: #12121A;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td width="50%" valign="top" style="padding-bottom: 12px;">
                          <span style="color: #71717A; font-size: 10px; text-transform: uppercase; font-weight: bold; display: block;">MÃ ĐẶT VÉ</span>
                          <span style="color: #FFFFFF; font-size: 16px; font-family: monospace; font-weight: 900; letter-spacing: 1px;">' . $ticketCode . '</span>
                        </td>
                        <td width="50%" valign="top" style="padding-bottom: 12px;">
                          <span style="color: #71717A; font-size: 10px; text-transform: uppercase; font-weight: bold; display: block;">GHẾ ĐÃ CHỌN</span>
                          <span style="color: #E50914; font-size: 16px; font-weight: 900;">' . $seats . '</span>
                        </td>
                      </tr>
                      <tr>
                        <td width="50%" valign="top" style="padding-bottom: 12px;">
                          <span style="color: #71717A; font-size: 10px; text-transform: uppercase; font-weight: bold; display: block;">SUẤT CHIẾU</span>
                          <span style="color: #FFFFFF; font-size: 13px; font-weight: 700;">' . $showtime . '</span>
                        </td>
                        <td width="50%" valign="top" style="padding-bottom: 12px;">
                          <span style="color: #71717A; font-size: 10px; text-transform: uppercase; font-weight: bold; display: block;">BẮP NƯỚC KÈM THEO</span>
                          <span style="color: #D4D4D8; font-size: 12px;">' . $combos . '</span>
                        </td>
                      </tr>
                      <tr>
                        <td width="50%" valign="top">
                          <span style="color: #71717A; font-size: 10px; text-transform: uppercase; font-weight: bold; display: block;">PHƯƠNG THỨC</span>
                          <span style="color: #10B981; font-size: 12px; font-weight: bold;">' . $paymentMethod . '</span>
                        </td>
                        <td width="50%" valign="top">
                          <span style="color: #71717A; font-size: 10px; text-transform: uppercase; font-weight: bold; display: block;">TỔNG THANH TOÁN</span>
                          <span style="color: #FFFFFF; font-size: 16px; font-weight: 900;">' . $totalAmount . '</span>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <tr>
                  <td align="center" style="padding: 24px 20px; background-color: #0E0E13; border-top: 1px dashed #2E2E3E;">
                    <span style="color: #A1A1AA; font-size: 11px; text-transform: uppercase; font-weight: bold; letter-spacing: 1px; display: block; margin-bottom: 12px;">QUÉT MÃ NÀY TẠI CỔNG SOÁT VÉ</span>

                    <div style="display: inline-block; padding: 10px; background-color: #FFFFFF; border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.5);">
                      <img src="' . $qrCodeUrl . '" alt="QR Code: ' . $ticketCode . '" width="180" height="180" style="display: block;" />
                    </div>

                    <p style="color: #71717A; font-size: 11px; margin: 12px 0 0 0;">
                      Mã xác thực: <strong style="color: #D4D4D8; font-family: monospace;">' . $ticketCode . '</strong> • Khách: ' . $customerEmail . '
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding: 0 20px 20px 20px;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: rgba(229, 9, 20, 0.08); border-radius: 12px; border: 1px solid rgba(229, 9, 20, 0.2); padding: 14px;">
                <tr>
                  <td style="color: #E50914; font-size: 12px; font-weight: bold; padding-bottom: 4px;">
                    ℹ Lưu ý quan trọng khi vào rạp:
                  </td>
                </tr>
                <tr>
                  <td style="color: #A1A1AA; font-size: 11px; line-height: 1.6;">
                    • Vui lòng đến rạp trước giờ chiếu từ 10 - 15 phút để ổn định chỗ ngồi.<br>
                    • Bạn có thể quét trực tiếp mã QR trên điện thoại hoặc đưa cho nhân viên tại quầy.<br>
                    • Không chụp ảnh, quay phim hoặc mang thức ăn ngoài quy định vào phòng chiếu.
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td align="center" style="padding: 24px 20px; background-color: #0E0E13; border-top: 1px solid rgba(255,255,255,0.06); color: #71717A; font-size: 11px; line-height: 1.6;">
              <p style="margin: 0 0 6px 0;">Hotline Hỗ Trợ Khách Hàng: <strong style="color: #E50914;">1900 8181</strong> (8:00 - 23:00 hàng ngày)</p>
              <p style="margin: 0 0 10px 0;">Email: <a href="mailto:hotro@cinego.vn" style="color: #A1A1AA; text-decoration: none;">hotro@cinego.vn</a> | Website: <a href="http://localhost/Cinego/" style="color: #E50914; text-decoration: none;">cinego.vn</a></p>
              <p style="margin: 0; color: #52525B;">&copy; 2026 CINEGO Vietnam. Nền tảng đặt vé xem phim trực tuyến hàng đầu.</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
';

$backupDir = __DIR__ . '/sent-emails';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}
$backupFile = $backupDir . '/ticket_' . preg_replace('/[^a-zA-Z0-9_\-]/', '', $ticketCode) . '.html';
file_put_contents($backupFile, $emailHtml);

$smtpConfig = [
    'host'       => 'smtp.gmail.com',
    'port'       => 587,
    'username'   => defined('SMTP_USER') ? SMTP_USER : '',
    'password'   => defined('SMTP_PASS') ? SMTP_PASS : '',
    'from_email' => defined('SMTP_USER') ? SMTP_USER : '',
    'from_name'  => 'CINEGO Cinema Vietnam'
];

$emailSent = false;
$smtpMessage = null;
$mail = new PHPMailer(true);

$isConfigured = !empty($smtpConfig['username']) &&
                !empty($smtpConfig['password']) &&
                $smtpConfig['password'] !== 'xxxx xxxx xxxx xxxx';

if ($isConfigured) {
    try {
        $mail->isSMTP();
        $mail->Host       = $smtpConfig['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpConfig['username'];
        $mail->Password   = $smtpConfig['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtpConfig['port'];
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 8;

        $mail->setFrom($smtpConfig['from_email'], $smtpConfig['from_name']);
        $mail->addAddress($customerEmail, $customerName);

        $mail->isHTML(true);
        $mail->Subject = "🎬 [CINEGO] Vé Điện Tử: {$movieTitle} (Mã vé: {$ticketCode})";
        $mail->Body    = $emailHtml;
        $mail->AltBody = "Xác nhận đặt vé thành công: {$movieTitle} tại {$cinemaName}. Suất: {$showtime}. Ghế: {$seats}. Mã vé: {$ticketCode}. Tổng tiền: {$totalAmount}. Xuất trình mã tại rạp.";

        $mail->send();
        $emailSent = true;
        $smtpMessage = "Đã gửi email thật thành công qua Google SMTP đến " . $customerEmail;
    } catch (Exception $e) {
        $smtpMessage = "Lỗi kết nối SMTP: " . $mail->ErrorInfo;
    }
} else {
    $smtpMessage = "Môi trường XAMPP cục bộ: Đã tạo email HTML hoàn chỉnh và lưu bản sao tại sent-emails/. (Để gửi thư thực tế qua Gmail, hãy điền Mật khẩu ứng dụng 16 ký tự vào file send-ticket.php)";
}

$dbSaved  = false;
$dbError  = null;
$ticketId = null;

try {

    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/models/CinegoModel.php';

    if (isset($pdo) && $pdo instanceof \PDO) {
        $cinegoModel = new CinegoModel($pdo);

        $existingOccupied = $cinegoModel->getOccupiedSeats($rawMovieTitle, $rawShowtime);
        $requestSeats = array_values(array_filter(array_map('trim', preg_split('/[,;\s]+/', $rawSeats))));

        $conflicted = [];
        foreach ($requestSeats as $seatName) {

            $variants = [$seatName];
            if (preg_match('/^([A-Za-z]+)(\d+)$/', $seatName, $m)) {
                $rowL = strtoupper($m[1]);
                $colN = (int)$m[2];
                $variants[] = $rowL . $colN;
                $variants[] = $rowL . sprintf('%02d', $colN);
            }
            foreach ($variants as $var) {
                if (in_array($var, $existingOccupied, true)) {
                    $conflicted[] = $seatName;
                    break;
                }
            }
        }

        if (!empty($conflicted)) {
            $conflictList = implode(', ', array_unique($conflicted));
            echo json_encode([
                'success'       => false,
                'conflict'      => true,
                'message'       => "Ghế [{$conflictList}] vừa được khách hàng khác đặt trước. Vui lòng quay lại bước chọn ghế!",
                'conflictSeats' => array_values(array_unique($conflicted))
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS `tickets` (
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
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $sql = "INSERT INTO tickets (
            ticket_code,
            customer_name,
            customer_email,
            customer_phone,
            movie_title,
            movie_poster,
            cinema_name,
            hall_name,
            showtime,
            seats,
            format,
            combos,
            total_amount,
            payment_method
        ) VALUES (
            :ticket_code,
            :customer_name,
            :customer_email,
            :customer_phone,
            :movie_title,
            :movie_poster,
            :cinema_name,
            :hall_name,
            :showtime,
            :seats,
            :format,
            :combos,
            :total_amount,
            :payment_method
        )";

        $stmt = $pdo->prepare($sql);

        $insertParams = [
            ':ticket_code'    => mb_substr($rawTicketCode, 0, 50, 'UTF-8'),
            ':customer_name'  => mb_substr($rawCustomerName, 0, 100, 'UTF-8'),
            ':customer_email' => mb_substr($rawCustomerEmail, 0, 100, 'UTF-8'),
            ':customer_phone' => mb_substr($rawCustomerPhone, 0, 20, 'UTF-8'),
            ':movie_title'    => mb_substr($rawMovieTitle, 0, 255, 'UTF-8'),
            ':movie_poster'   => $rawMoviePoster,
            ':cinema_name'    => mb_substr($rawCinemaName, 0, 255, 'UTF-8'),
            ':hall_name'      => mb_substr($rawHallName, 0, 100, 'UTF-8'),
            ':showtime'       => mb_substr($rawShowtime, 0, 100, 'UTF-8'),
            ':seats'          => mb_substr($rawSeats, 0, 255, 'UTF-8'),
            ':format'         => mb_substr($rawFormat, 0, 50, 'UTF-8'),
            ':combos'         => $rawCombos,
            ':total_amount'   => mb_substr($rawTotalAmount, 0, 50, 'UTF-8'),
            ':payment_method' => mb_substr($rawPaymentMethod, 0, 50, 'UTF-8')
        ];

        $executed = $stmt->execute($insertParams);
        if ($executed) {
            $ticketId = (int)$pdo->lastInsertId();
            $dbSaved  = true;

            $cookieDuration = time() + (7 * 24 * 60 * 60);
            setcookie('cinego_customer_name',  $rawCustomerName,  $cookieDuration, '/');
            setcookie('cinego_customer_email', $rawCustomerEmail, $cookieDuration, '/');
            setcookie('cinego_customer_phone', $rawCustomerPhone, $cookieDuration, '/');
        } else {
            $dbError = "Lỗi: Không thể thực thi câu lệnh SQL INSERT.";
        }
    } else {
        $dbError = !empty($dbConnectionError) ? $dbConnectionError : "Không thể khởi tạo đối tượng PDO kết nối MySQL.";
    }
} catch (\PDOException $e) {

    $dbSaved = false;
    $dbError = "Lỗi PDOException: " . $e->getMessage();
} catch (\Throwable $e) {

    $dbSaved = false;
    $dbError = "Lỗi ngoại lệ: " . $e->getMessage();
}

echo json_encode([
    'success'       => true,
    'emailSent'     => $emailSent,
    'simulated'     => !$emailSent,
    'recipient'     => $customerEmail,
    'ticketCode'    => $rawTicketCode,
    'ticketId'      => $ticketId,
    'dbSaved'       => $dbSaved,
    'dbError'       => $dbError,
    'movieTitle'    => $rawMovieTitle,
    'cinemaName'    => $rawCinemaName,
    'showtime'      => $rawShowtime,
    'seats'         => $rawSeats,
    'totalAmount'   => $rawTotalAmount,
    'message'       => "Vé điện tử và mã QR đã được gửi thành công đến email {$customerEmail}",
    'previewFile'   => 'sent-emails/' . basename($backupFile),
    'smtpStatus'    => $smtpMessage
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
