<?php
$host = 'localhost';
$db   = 'db_cinego';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
];

$pdo = null;
$dbConnectionError = null;

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    $dbConnectionError = 'Lỗi kết nối MySQL: ' . $e->getMessage();
    $pdo = null;
}

// Xử lý khi kết nối thất bại
if ($pdo === null) {
    $scriptName = basename($_SERVER['SCRIPT_FILENAME'] ?? '');
    $isApi = in_array($scriptName, ['db.php', 'api.php', 'chat-ai.php', 'send-ticket.php'])
             || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
             || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    if ($isApi) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
        }
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi kết nối CSDL: Vui lòng khởi động dịch vụ MySQL trong XAMPP Control Panel!',
            'error'   => $dbConnectionError
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Hiển thị giao diện thông báo lỗi tiếng Việt thân thiện, rõ ràng khi truy cập từ trình duyệt web
    if (!headers_sent()) {
        http_response_code(500);
    }
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Lỗi Kết Nối Cơ Sở Dữ Liệu | CINEGO Cinema</title>
      <script src="https://cdn.tailwindcss.com"></script>
      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
      <style>body { font-family: 'Inter', sans-serif; }</style>
    </head>
    <body class="min-h-screen bg-[#0B0B0F] text-zinc-100 flex items-center justify-center p-4 relative overflow-hidden">
      <!-- Background Glow Effects -->
      <div class="absolute -top-40 -left-40 w-96 h-96 bg-red-600/20 rounded-full blur-[130px] pointer-events-none"></div>
      <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-amber-500/15 rounded-full blur-[130px] pointer-events-none"></div>

      <div class="w-full max-w-lg bg-[#18181F]/95 backdrop-blur-2xl border border-red-500/30 rounded-3xl p-6 sm:p-8 shadow-2xl relative z-10 space-y-6">
        
        <div class="flex items-center gap-3 pb-5 border-b border-white/10">
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-red-600 to-[#E50914] flex items-center justify-center text-white text-2xl font-black shadow-lg shadow-red-900/40 shrink-0">
            ⚠️
          </div>
          <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-red-500/20 border border-red-500/30 text-red-400 text-[10px] font-black uppercase tracking-wider">
              CINEGO SYSTEM ALERT
            </div>
            <h1 class="text-lg sm:text-xl font-bold text-white mt-1 leading-snug">Chưa Bật Dịch Vụ MySQL (XAMPP)</h1>
          </div>
        </div>

        <div class="text-xs sm:text-sm text-zinc-300 leading-relaxed space-y-3">
          <p>Website không thể kết nối tới cơ sở dữ liệu MySQL <code class="px-2 py-0.5 rounded-lg bg-zinc-800 text-amber-400 font-mono text-xs">db_cinego</code> trên máy chủ cục bộ.</p>
          
          <div class="p-4 rounded-2xl bg-[#0B0B0F] border border-white/10 space-y-2.5">
            <h2 class="text-xs font-bold uppercase tracking-wider text-amber-400 flex items-center gap-1.5">
              <span>🛠️ Hướng dẫn khắc phục nhanh:</span>
            </h2>
            <ol class="list-decimal list-inside space-y-1.5 text-xs text-zinc-400">
              <li>Mở bảng điều khiển <strong>XAMPP Control Panel</strong> trên máy tính.</li>
              <li>Tại dòng <strong>MySQL</strong>, bấm nút <strong class="text-emerald-400">Start</strong> để bật dịch vụ.</li>
              <li>Nếu cơ sở dữ liệu chưa có, hãy nhập file <code class="text-zinc-200">db_cinego.sql</code> vào Navicat hoặc phpMyAdmin.</li>
              <li>Bấm nút <strong>"Thử Kết Nối Lại"</strong> bên dưới.</li>
            </ol>
          </div>

          <div class="p-3 rounded-xl bg-red-950/40 border border-red-500/30 text-[11px] text-red-300 font-mono break-all">
            <span class="text-red-400 font-bold block mb-0.5">Thông báo kỹ thuật:</span>
            <?php echo htmlspecialchars($dbConnectionError ?? 'Không thể khởi tạo PDO.'); ?>
          </div>
        </div>

        <div class="flex items-center justify-between gap-3 pt-3 border-t border-white/10">
          <button onclick="window.location.reload();" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#E50914] to-red-700 hover:from-red-600 hover:to-red-800 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-lg shadow-red-900/30 cursor-pointer flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span>Thử Kết Nối Lại</span>
          </button>
          <a href="http://localhost/phpmyadmin/" target="_blank" class="px-4 py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white text-xs font-semibold transition-all">
            Mở phpMyAdmin &rarr;
          </a>
        </div>

      </div>
    </body>
    </html>
    <?php
    exit;
}
