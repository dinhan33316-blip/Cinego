<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    exit;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/models/CinegoModel.php';

$cinegoModel = new CinegoModel($pdo);

$action = $_GET['action'] ?? $_POST['action'] ?? '';
if (empty($action)) {
    $raw = @file_get_contents('php://input');
    if (!empty($raw)) {
        $body = json_decode($raw, true);
        $action = $body['action'] ?? '';
    }
}

switch ($action) {
    case 'get_occupied_seats':
        $movie    = trim($_GET['movie'] ?? $_POST['movie'] ?? '');
        $showtime = trim($_GET['showtime'] ?? $_POST['showtime'] ?? '');

        if (empty($movie)) {
            echo json_encode([
                'success' => false,
                'message' => 'Thiếu thông tin tên phim (movie)',
                'seats'   => []
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $occupiedSeats = $cinegoModel->getOccupiedSeats($movie, $showtime);

        echo json_encode([
            'success'       => true,
            'movie'         => $movie,
            'showtime'      => $showtime,
            'occupiedSeats' => $occupiedSeats,
            'count'         => count($occupiedSeats)
        ], JSON_UNESCAPED_UNICODE);
        exit;

    case 'lookup_tickets':
        $keyword = trim($_GET['keyword'] ?? $_POST['keyword'] ?? '');

        if (empty($keyword)) {
            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng nhập Mã vé, Số điện thoại hoặc Email để tra cứu',
                'tickets' => []
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $tickets = $cinegoModel->searchCustomerTickets($keyword);
        $results = [];
        $emailDir = __DIR__ . '/sent-emails/';

        foreach ($tickets as $t) {
            $code = $t['ticket_code'];
            $cleanCode = preg_replace('/[^a-zA-Z0-9_\-]/', '', $code);
            $fileName = 'ticket_' . $cleanCode . '.html';
            $filePath = $emailDir . $fileName;
            $hasFile = file_exists($filePath);

            $results[] = [
                'id'             => (int)$t['id'],
                'ticket_code'    => $t['ticket_code'],
                'customer_name'  => $t['customer_name'],
                'customer_email' => $t['customer_email'],
                'customer_phone' => $t['customer_phone'],
                'movie_title'    => html_entity_decode($t['movie_title'], ENT_QUOTES, 'UTF-8'),
                'movie_poster'   => $t['movie_poster'],
                'cinema_name'    => $t['cinema_name'],
                'hall_name'      => $t['hall_name'],
                'showtime'       => $t['showtime'],
                'seats'          => $t['seats'],
                'format'         => $t['format'],
                'combos'         => $t['combos'],
                'total_amount'   => $t['total_amount'],
                'payment_method' => $t['payment_method'],
                'created_at'     => $t['created_at'],
                'ticket_file'    => $hasFile ? ('sent-emails/' . $fileName) : null
            ];
        }

        echo json_encode([
            'success' => true,
            'keyword' => $keyword,
            'count'   => count($results),
            'tickets' => $results
        ], JSON_UNESCAPED_UNICODE);
        exit;

    case 'cancel_ticket':
    case 'delete_ticket':
        $ticketCode = trim($_POST['ticket_code'] ?? $_GET['ticket_code'] ?? '');
        $ticketId   = trim($_POST['id'] ?? $_GET['id'] ?? '');

        $identifier = !empty($ticketCode) ? $ticketCode : $ticketId;
        if (empty($identifier)) {
            $raw = @file_get_contents('php://input');
            if (!empty($raw)) {
                $body = json_decode($raw, true);
                $identifier = trim($body['ticket_code'] ?? $body['id'] ?? '');
            }
        }

        if (empty($identifier)) {
            echo json_encode([
                'success' => false,
                'message' => 'Thiếu thông tin mã vé (ticket_code) hoặc ID để hủy vé'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $deletedTicket = $cinegoModel->deleteTicket($identifier);
        if ($deletedTicket) {
            echo json_encode([
                'success'     => true,
                'message'     => 'Hủy vé ' . $deletedTicket['ticket_code'] . ' thành công! Ghế đã đặt được giải phóng.',
                'ticket_code' => $deletedTicket['ticket_code'],
                'seats'       => $deletedTicket['seats'],
                'movie_title' => $deletedTicket['movie_title'],
                'showtime'    => $deletedTicket['showtime']
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy vé hoặc xảy ra lỗi trong quá trình hủy vé'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;

    case 'get_reviews':
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        if ($limit <= 0) $limit = 20;
        $reviews = $cinegoModel->getAllReviews($limit);

        echo json_encode([
            'success' => true,
            'count'   => count($reviews),
            'reviews' => $reviews
        ], JSON_UNESCAPED_UNICODE);
        exit;

    case 'submit_review':
        $ticketCode = trim($_POST['ticket_code'] ?? $_GET['ticket_code'] ?? '');
        $rating     = $_POST['rating'] ?? $_GET['rating'] ?? '';
        $comment    = trim($_POST['comment'] ?? $_GET['comment'] ?? '');

        if (empty($ticketCode) || empty($comment)) {
            $raw = @file_get_contents('php://input');
            if (!empty($raw)) {
                $body = json_decode($raw, true);
                if ($body) {
                    $ticketCode = trim($body['ticket_code'] ?? $ticketCode);
                    $rating     = $body['rating'] ?? $rating;
                    $comment    = trim($body['comment'] ?? $comment);
                }
            }
        }

        if (empty($ticketCode)) {
            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng cung cấp Mã vé (ticket_code).'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($rating === '' || floatval($rating) <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng chọn điểm đánh giá từ 1 đến 10.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (empty($comment)) {
            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng nhập nội dung đánh giá của bạn.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $reviewResult = $cinegoModel->addReview($ticketCode, $rating, $comment);
        echo json_encode($reviewResult, JSON_UNESCAPED_UNICODE);
        exit;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Hành động không hợp lệ hoặc thiếu tham số action'
        ], JSON_UNESCAPED_UNICODE);
        exit;
}
