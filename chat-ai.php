<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.secret.php';
$geminiApiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';

$rawInput = file_get_contents('php://input');
if (empty($rawInput)) {
    $rawInput = @file_get_contents('php://stdin');
}

$data = json_decode($rawInput, true);
if (!$data) {
    $data = $_POST;
}

$userMessage  = isset($data['message']) ? trim($data['message']) : '';
$history      = isset($data['history']) && is_array($data['history']) ? $data['history'] : [];
$movieContext = isset($data['movieContext']) && is_array($data['movieContext']) ? $data['movieContext'] : [];

if (empty($userMessage)) {
    echo json_encode([
        'success' => false,
        'reply'   => 'Xin chào! Tôi là Trợ lý ảo CINEGO AI. Bạn đang muốn tìm phim gì hoặc cần tư vấn suất chiếu nào hôm nay?',
        'source'  => 'system'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$isApiKeyValid = !empty($geminiApiKey) && $geminiApiKey !== 'YOUR_GEMINI_API_KEY' && strlen(trim($geminiApiKey)) > 15;
$responseReply = null;
$source = 'smart_bot';

$systemInstruction = "Bạn là CINEGO AI - Trợ lý ảo chính thức của cụm rạp chiếu phim cao cấp CINEGO tại Việt Nam.
Nhiệm vụ của bạn:
1. Tư vấn phim đang chiếu, phim sắp chiếu, thể loại phù hợp với sở thích của khách (hành động, hoạt hình, tiên hiệp, tình cảm, viễn tưởng).
2. Cung cấp thông tin giá vé, công nghệ chiếu (IMAX Laser, Dolby Atmos 7.1, 2D/3D Laser, ghế đôi Sweetbox).
3. Hướng dẫn đặt vé trực tuyến 3 bước và nhắc khách áp dụng mã giảm giá 'CINEGO50' (giảm ngay 50% cho lần đặt đầu tiên).
4. Giới thiệu các combo bắp rang thơm bơ (Combo Solo 69k, Combo Couple Bestseller 99k, Combo Family 179k).
5. Phong cách trả lời: Thân thiện, nhiệt tình, lịch sự, ngắn gọn súc tích, ngắt dòng dễ đọc, dùng emoji điện ảnh sinh động.
6. ĐẶC BIỆT: Khi giới thiệu hoặc gợi ý một bộ phim cụ thể, hãy đặt cú pháp [Đặt vé: Tên phim] ở ngay sau phim đó để hệ thống tự động tạo nút Đặt Vé Ngay cho khách hàng (ví dụ: [Đặt vé: Deadpool & Wolverine] hoặc [Đặt vé: Bạch Xà: Duyên Khởi]).";

$movieSummaryText = "";
if (!empty($movieContext)) {
    $movieSummaryText .= "\nDanh sách phim đang chiếu tại hệ thống rạp CINEGO:\n";
    foreach ($movieContext as $m) {
        $t = isset($m['title']) ? $m['title'] : '';
        $g = isset($m['genres']) ? $m['genres'] : '';
        $d = isset($m['duration']) ? $m['duration'] : '';
        $r = isset($m['rating']) ? $m['rating'] : '';
        $f = isset($m['format']) ? $m['format'] : '2D, IMAX';
        $movieSummaryText .= "- {$t} | Thể loại: {$g} | Thời lượng: {$d} | Điểm: {$r}/10 | Định dạng: {$f}\n";
    }
} else {
    $movieSummaryText = "\nPhim nổi bật tại CINEGO:
- Deadpool & Wolverine (Hành động, Hài hước, T18, IMAX 3D, 9.3/10)
- Inside Out 2 (Hoạt hình, Gia đình, P, 2D/3D, 9.5/10)
- Bạch Xà: Duyên Khởi (Hoạt hình 3D, Tiên hiệp, T13, IMAX Laser, 9.1/10)
- Na Tra: Ma Đồng Giáng Thế (Hoạt hình 3D, Thần thoại, P, IMAX, 9.3/10)
- Dune: Part Two (Khoa học viễn tưởng, T16, IMAX Laser, 9.4/10)
- Godzilla x Kong: The New Empire (Hành động, Quái thú, T13, 8.9/10)";
}

if ($isApiKeyValid) {
    $contents = [];
    $firstUserPrompt = $systemInstruction . "\n" . $movieSummaryText . "\n\nKhách hàng hỏi: " . $userMessage;

    if (!empty($history)) {
        $recentHistory = array_slice($history, -6);
        foreach ($recentHistory as $idx => $turn) {
            $role = ($turn['role'] === 'user') ? 'user' : 'model';
            $contentStr = isset($turn['content']) ? trim($turn['content']) : '';
            if (!empty($contentStr)) {
                $contents[] = [
                    'role'  => $role,
                    'parts' => [['text' => $contentStr]]
                ];
            }
        }
    }

    $contents[] = [
        'role'  => 'user',
        'parts' => [['text' => $firstUserPrompt]]
    ];

    $payload = [
        'contents' => $contents,
        'generationConfig' => [
            'temperature'     => 0.7,
            'topK'            => 40,
            'topP'            => 0.95,
            'maxOutputTokens' => 800,
        ]
    ];

    $models = ['gemini-2.5-flash', 'gemini-1.5-flash'];

    foreach ($models as $modelName) {
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . urlencode($geminiApiKey);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $apiUrl,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $apiResponse = curl_exec($ch);
        $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && !empty($apiResponse)) {
            $respJson = json_decode($apiResponse, true);
            if (isset($respJson['candidates'][0]['content']['parts'][0]['text'])) {
                $responseReply = trim($respJson['candidates'][0]['content']['parts'][0]['text']);
                $source = 'gemini';
                break;
            }
        }
    }
}

if (empty($responseReply)) {
    $source = 'smart_bot';
    $responseReply = generateSmartResponse($userMessage, $movieContext);
}

echo json_encode([
    'success'          => true,
    'reply'            => $responseReply,
    'source'           => $source,
    'apiKeyConfigured' => $isApiKeyValid
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;

function generateSmartResponse($msg, $context = []) {
    $unaccent = removeVietnameseAccents($msg);
    $m = mb_strtolower($unaccent, 'UTF-8');

    if (strpos($m, 'phim dang chieu hot') !== false || strpos($m, 'phim hot') !== false || strpos($m, 'bom tan') !== false || strpos($m, 'phim hay') !== false || strpos($m, 'dang chieu') !== false) {
        return "🔥 **TOP PHIM ĐANG 'CHÁY VÉ' TẠI RẠP CINEGO HÔM NAY:**\n\n" .
               "1. **Deadpool & Wolverine** (T18)\n" .
               "• Điểm: ⭐ **9.3/10** | Hành động kịch tính & siêu hài hước từ Marvel Studios.\n" .
               "• Định dạng: **IMAX 3D Laser, 2D Phụ đề**.\n" .
               "[Đặt vé: Deadpool & Wolverine]\n\n" .
               "2. **Bạch Xà: Duyên Khởi** (T13)\n" .
               "• Điểm: ⭐ **9.1/10** | Siêu phẩm hoạt hình 3D tiên hiệp mãn nhãn với chuyện tình tiền duyên ngàn năm.\n" .
               "• Định dạng: **IMAX Laser, 3D, 2D**.\n" .
               "[Đặt vé: Bạch Xà: Duyên Khởi]\n\n" .
               "3. **Inside Out 2 (Những Mảnh Ghép Cảm Xúc 2)** (P)\n" .
               "• Điểm: ⭐ **9.5/10** | Phim hoạt hình ý nghĩa, cảm xúc cho cả gia đình và bạn trẻ.\n" .
               "[Đặt vé: Inside Out 2]\n\n" .
               "4. **Dune: Part Two (Hành Tinh Cát 2)** (T16)\n" .
               "• Điểm: ⭐ **9.4/10** | Kiệt tác điện ảnh viễn tưởng trên màn ảnh khổng lồ IMAX.\n" .
               "[Đặt vé: Dune: Part Two]\n\n" .
               "💡 *Bạn hãy bấm nút **[Đặt vé]** ngay dưới phim yêu thích để giữ ghế vị trí đẹp nhất nhé!*";
    }

    if (strpos($m, 'tu van phim theo so thich') !== false || strpos($m, 'so thich') !== false || strpos($m, 'the loai') !== false || strpos($m, 'nen xem phim gi') !== false || strpos($m, 'tu van phim') !== false) {
        return "🎯 **GỢI Ý PHIM THEO GU XEM CỦA BẠN:**\n\n" .
               "⚔️ **Gu Hành động & Bom tấn mãn nhãn:**\n" .
               "• *Deadpool & Wolverine* (Cực hài hước, đánh đấm đỉnh cao) [Đặt vé: Deadpool & Wolverine]\n" .
               "• *Godzilla x Kong: Đế Chế Mới* (Đại chiến quái thú tiền sử) [Đặt vé: Godzilla x Kong: The New Empire]\n\n" .
               "🐉 **Gu Tiên hiệp, Thần thoại & Kỹ xảo 3D:**\n" .
               "• *Bạch Xà: Duyên Khởi* (Đồ họa 3D tuyệt mỹ) [Đặt vé: Bạch Xà: Duyên Khởi]\n" .
               "• *Na Tra: Ma Đồng Giáng Thế* (Nghịch thiên cải mệnh bùng nổ) [Đặt vé: Na Tra: Ma Đồng Giáng Thế]\n\n" .
               "👨‍👩‍👧 **Gu Gia đình, Hoạt hình vui nhộn & Cảm xúc:**\n" .
               "• *Inside Out 2* (Ấm áp, hài hước, tuổi dậy thì) [Đặt vé: Inside Out 2]\n" .
               "• *Kung Fu Panda 4* (Võ thuật gấu Po lém lỉnh) [Đặt vé: Kung Fu Panda 4]\n" .
               "• *Suzume* (Anime Nhật Bản cảm xúc) [Đặt vé: Suzume]\n\n" .
               "👉 *Bạn đang tìm suất chiếu cho mấy người và rạp ở khu vực nào (Hồ Chí Minh, Hà Nội hay Đà Nẵng)?*";
    }

    if (strpos($m, 'gia ve & ma giam gia') !== false || strpos($m, 'gia ve') !== false || strpos($m, 'ma giam gia') !== false || strpos($m, 'khuyen mai') !== false || strpos($m, 'voucher') !== false || strpos($m, 'uu dai') !== false || strpos($m, 'cinego50') !== false) {
        return "🎟️ **BẢNG GIÁ VÉ & ƯU ĐÃI ĐẶC QUYỀN TẠI CINEGO:**\n\n" .
               "💰 **Bảng giá vé tiêu chuẩn:**\n" .
               "• **Ghế Thường (2D):** 85.000đ – 95.000đ\n" .
               "• **Ghế VIP Trung tâm:** 110.000đ – 120.000đ (Góc nhìn hoàn mỹ)\n" .
               "• **Vé IMAX 3D Laser:** 160.000đ – 175.000đ (Màn hình siêu lớn, âm thanh Dolby Atmos)\n" .
               "• **Ghế Đôi Sweetbox:** 220.000đ/cặp (Hàng ghế cuối riêng tư cho cặp đôi)\n\n" .
               "🎁 **MÃ GIẢM GIÁ ĐỘC QUYỀN:**\n" .
               "Nhập mã **`CINEGO50`** tại Bước 4 (Thanh toán) để được **GIẢM NGAY 50%** tổng tiền vé cho lần đặt online đầu tiên!\n\n" .
               "🌟 **Ưu đãi cố định:**\n" .
               "• *Thứ Tư Vui Vẻ:* Đồng giá 55.000đ/vé 2D cả ngày.\n" .
               "• *Học sinh - Sinh viên (U22):* 45.000đ từ thứ 2 đến thứ 6.\n\n" .
               "[Đặt vé: Deadpool & Wolverine]";
    }

    if (strpos($m, 'huong dan dat ve') !== false || strpos($m, 'cach dat ve') !== false || strpos($m, 'quy trinh') !== false || strpos($m, 'chon ghe') !== false || strpos($m, 'mua ve') !== false) {
        return "📱 **HƯỚNG DẪN ĐẶT VÉ NHANH TRONG 3 BƯỚC TẠI CINEGO:**\n\n" .
               "1️⃣ **Bước 1 - Chọn Phim & Suất Chiếu:**\n" .
               "Bấm nút **Đặt Vé** tại trang chủ hoặc trên tin nhắn này. Chọn rạp gần bạn, ngày chiếu và khung giờ mong muốn.\n\n" .
               "2️⃣ **Bước 2 - Chọn Ghế & Bắp Nước:**\n" .
               "Chọn vị trí ghế yêu thích trên sơ đồ rạp (Ghế VIP, Thường hoặc Ghế đôi Sweetbox). Chọn thêm combo bắp rang bơ phô mai hoặc caramel thơm ngon.\n\n" .
               "3️⃣ **Bước 3 - Thanh Toán & Nhận Vé:**\n" .
               "Nhập mã giảm giá **`CINEGO50`**, điền email để nhận vé điện tử và mã QR. Quét mã QR trực tiếp tại cổng soát vé mà không cần xếp hàng đổi vé giấy!\n\n" .
               "[Đặt vé: Inside Out 2]";
    }

    if (strpos($m, 'bach xa') !== false || strpos($m, 'na tra') !== false || strpos($m, 'tien hiep') !== false || strpos($m, 'white snake') !== false || strpos($m, 'ne zha') !== false) {
        return "🐉 **SIÊU PHẨM HOẠT HÌNH 3D TIÊN HIỆP ĐANG CHIẾU:**\n\n" .
               "1. **Bạch Xà: Duyên Khởi** (*White Snake*)\n" .
               "• Đồ họa: Kỹ xảo 3D đỉnh cao của Light Chaser Animation.\n" .
               "• Đánh giá: ⭐ **9.1/10** | Thời lượng: **99 phút** (T13).\n" .
               "• Phòng chiếu: 2D, 3D & **IMAX Laser** sống động từng chi tiết phép thuật.\n" .
               "[Đặt vé: Bạch Xà: Duyên Khởi]\n\n" .
               "2. **Na Tra: Ma Đồng Giáng Thế** (*Ne Zha*)\n" .
               "• Bom tấn phá vỡ kỷ lục phòng vé với tinh thần bất khuất 'Mệnh do ta định chứ không do trời'.\n" .
               "• Đánh giá: ⭐ **9.3/10** | Phù hợp mọi lứa tuổi (P).\n" .
               "[Đặt vé: Na Tra: Ma Đồng Giáng Thế]";
    }

    if (strpos($m, 'rap') !== false || strpos($m, 'o dau') !== false || strpos($m, 'dia chi') !== false || strpos($m, 'imax') !== false || strpos($m, 'landmark') !== false) {
        return "📍 **CÁC CỤM RẠP CINEGO TIÊU CHUẨN QUỐC TẾ:**\n\n" .
               "🏙️ **TP. Hồ Chí Minh:**\n" .
               "• **CINEGO Landmark 81 IMAX:** Tầng B1, Tòa Landmark 81, Vinhomes Central Park, Bình Thạnh (Rạp IMAX Laser đỉnh nhất).\n" .
               "• **CINEGO Vincom Đồng Khởi:** Tầng 3, 72 Lê Thánh Tôn, Bến Nghé, Quận 1.\n" .
               "• **CINEGO Hùng Vương Plaza:** Tầng 7, 126 Hồng Bàng, Phường 12, Quận 5.\n\n" .
               "🏛️ **Hà Nội:**\n" .
               "• **CINEGO Metropolis Liễu Giai:** Tầng 4, Vincom Metropolis, 29 Liễu Giai, Ba Đình.\n" .
               "• **CINEGO Royal City Mega Mall:** Tầng B2, 72A Nguyễn Trãi, Thanh Xuân.\n\n" .
               "🌊 **Đà Nẵng:**\n" .
               "• **CINEGO Riverside:** Tầng 4, Vincom Plaza, 910A Ngô Quyền, Sơn Trà.\n\n" .
               "👉 *Bạn có thể chọn rạp ngay khi bấm nút Đặt Vé bên dưới!*";
    }

    if (strpos($m, 'bap') !== false || strpos($m, 'nuoc') !== false || strpos($m, 'combo') !== false || strpos($m, 'do an') !== false || strpos($m, 'popcorn') !== false) {
        return "🍿 **MENU BẮP NƯỚC CỰC HẤP DẪN TẠI CINEGO:**\n\n" .
               "• **Combo Solo (69.000đ):** 1 Bắp ngọt 60oz + 1 Nước ngọt lớn 32oz.\n" .
               "• **Combo Couple Bestseller (99.000đ):** 1 Bắp khổng lồ 85oz (Phô mai thơm nồng / Caramel ngọt dịu) + 2 Nước ngọt lớn 32oz.\n" .
               "• **Combo Family Party (179.000đ):** 2 Bắp lớn 85oz + 4 Nước ngọt + 1 Khoai tây lắc giòn rụm.\n\n" .
               "🥤 *Tất cả bắp rang đều được nổ nóng giòn liên tục tại quầy. Bạn có thể chọn kèm bắp nước ở Bước 3 khi đặt vé.*";
    }

    if (strpos($m, 'chao') !== false || strpos($m, 'hi') !== false || strpos($m, 'hello') !== false || strpos($m, 'alo') !== false) {
        return "👋 **Xin chào quý khách! Tôi là CINEGO AI - Trợ lý điện ảnh của bạn.**\n\n" .
               "Tôi có thể hỗ trợ bạn:\n" .
               "• 🎬 Gợi ý phim hot hôm nay (Deadpool, Bạch Xà, Inside Out 2, Na Tra...)\n" .
               "• 🎟️ Tra cứu giá vé & áp dụng mã giảm giá **`CINEGO50`** (giảm 50%)\n" .
               "• 📍 Tìm rạp chiếu CINEGO gần bạn nhất\n" .
               "• 🍿 Tư vấn các combo bắp nước giòn tan\n\n" .
               "Bạn muốn xem phim nào hay cần mình tư vấn phim theo sở thích?";
    }

    return "🎬 **Cảm ơn bạn đã nhắn tin cho CINEGO AI!**\n\n" .
           "Hôm nay rạp CINEGO đang chiếu rất nhiều bom tấn hấp dẫn:\n" .
           "• **Hành động & Siêu anh hùng:** *Deadpool & Wolverine* [Đặt vé: Deadpool & Wolverine]\n" .
           "• **Hoạt hình 3D & Tiên hiệp:** *Bạch Xà: Duyên Khởi*, *Na Tra: Ma Đồng Giáng Thế* [Đặt vé: Bạch Xà: Duyên Khởi]\n" .
           "• **Gia đình & Tâm lý:** *Inside Out 2*, *Kung Fu Panda 4* [Đặt vé: Inside Out 2]\n\n" .
           "💡 Bạn có thể bấm vào các nút gợi ý nhanh ở phía trên hoặc hỏi tôi về giá vé, suất chiếu và mã giảm giá nhé!";
}

function removeVietnameseAccents($str) {
    $str = mb_strtolower($str, 'UTF-8');
    $accents = [
        'a' => ['à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ'],
        'e' => ['è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ'],
        'i' => ['ì','í','ị','ỉ','ĩ'],
        'o' => ['ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ'],
        'u' => ['ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ'],
        'y' => ['ỳ','ý','ỵ','ỷ','ỹ'],
        'd' => ['đ']
    ];
    foreach ($accents as $non => $list) {
        $str = str_replace($list, $non, $str);
    }
    return $str;
}
