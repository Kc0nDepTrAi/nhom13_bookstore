<?php
// View-only renderer: the source HTML is imported from the supplied Stitch export.
require_once dirname(__DIR__, 3) . '/config/config.php';
$stitchPages = json_decode(file_get_contents(__DIR__ . '/manifest.json'), true);
$stitchPage = isset($stitchPages[$stitchPage ?? '']) ? $stitchPage : 'home';
if ($stitchPage === 'orders' && isset($_GET['id'])) $stitchPage = 'orderDetail';
// Tạm thời cố định layout desktop cho khách hàng theo yêu cầu
// if (preg_match('/Mobile|Android|iPhone/i', $_SERVER['HTTP_USER_AGENT'] ?? '')) {
//     if ($stitchPage === 'catalog') $stitchPage = 'catalogMobile';
//     if ($stitchPage === 'account') $stitchPage = 'accountMobile';
// }
$stitchManagementSection = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '') === 'manage_inventory.php' ? 'stock' : 'categories';
$stitchConfig = ['csrf' => $_SESSION['csrf_token'], 'base' => BASE_URL, 'page' => $stitchPage, 'managementSection' => $stitchPage === 'categories' ? $stitchManagementSection : null,
 'user' => ['id' => $_SESSION['user_id'] ?? null, 'username' => $_SESSION['username'] ?? '', 'role' => $_SESSION['role'] ?? ''],
 'functions' => $stitchPages[$stitchPage]['functions']];
$html = file_get_contents(__DIR__ . '/templates/' . $stitchPage . '.html');
if ($stitchPage === 'categories') {
    $unrelatedSection = $stitchManagementSection === 'stock' ? 'phan-he-the-loai' : 'phan-he-ton-kho';
    $html = preg_replace('~<section\b(?=[^>]*id="' . $unrelatedSection . '")[^>]*>.*?</section>~s', '', $html);
    // Keep the shared source template, but emit only the modal for this screen.
    $unrelatedModal = $stitchManagementSection === 'stock' ? 'categoryModal' : 'stockModal';
    if (preg_match('~<div\b[^>]*id="' . $unrelatedModal . '"[^>]*>~', $html, $modalMatch, PREG_OFFSET_CAPTURE)) {
        $start = $modalMatch[0][1];
        preg_match_all('~</?div\b[^>]*>~', substr($html, $start), $tags, PREG_OFFSET_CAPTURE);
        $depth = 0;
        foreach ($tags[0] as [$tag, $offset]) {
            $depth += str_starts_with($tag, '</') ? -1 : 1;
            if ($depth === 0) {
                $html = substr_replace($html, '', $start, $offset + strlen($tag));
                break;
            }
        }
    }
    $title = $stitchManagementSection === 'stock' ? 'Quản lý tồn kho' : 'Quản lý thể loại';
    $html = preg_replace('~<title>.*?</title>~s', '<title>' . $title . ' | BOOKSTORE123</title>', $html);
    $html = str_replace('Vận hành kho &amp; Danh mục', $title, $html);
}
// Only render the admin entry for an authenticated administrator.
$html = preg_replace_callback('~<a\b(?=[^>]*data-path="quan-tri-vien")[^>]*>.*?</a>~s', static function(array $match): string {
    if (empty($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'admin') return '';
    return preg_replace('~href="[^"]*"~', 'href="' . htmlspecialchars(BASE_URL . '/admin', ENT_QUOTES, 'UTF-8') . '"', $match[0], 1);
}, $html);

$stitchAssetVersion = static function (string $name): string {
    $path = dirname(__DIR__, 3) . '/public/assets/' . $name;
    return substr(hash_file('sha256', $path), 0, 12);
};
$assets = '<script>window.StitchConfig=' . json_encode($stitchConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) . ';</script>';
$assets .= '<link rel="stylesheet" href="' . BASE_URL . '/public/assets/stitch-live.css?v=' . $stitchAssetVersion('stitch-live.css') . '">';
$html = str_replace('</head>', $assets . '</head>', $html);

if (!str_contains($html, 'id="chat-popup"') && $stitchPage !== 'chat') {
    $chatPopup = <<<'HTML'
<!-- Interactive Support Chat Drawer Widget (Bottom Right) -->
<div class="fixed bottom-24 right-6 z-40 w-80 sm:w-[420px] h-[520px] max-h-[82vh] bg-surface-container-lowest rounded-2xl shadow-2xl border-0 overflow-hidden hidden flex-col transition-all" id="chat-popup">
<div class="bg-primary p-space-md text-on-primary flex items-center justify-between shrink-0">
<div class="flex items-center gap-2">
<div class="w-3 h-3 rounded-full bg-emerald-300 animate-pulse"></div>
<div>
<h4 class="font-title-sm text-title-sm leading-tight">Tư vấn viên BOOKSTORE123</h4>
<span class="font-label-sm text-label-sm opacity-80">Trực tuyến (Phản hồi tức thì)</span>
</div>
</div>
<button class="text-on-primary hover:opacity-80" onclick="toggleChat()" type="button">
<span class="material-symbols-outlined text-[20px]">close</span>
</button>
</div>
<div class="p-space-md flex-1 overflow-y-auto flex flex-col gap-space-sm bg-surface" id="chatMessages">
<div class="self-start max-w-[85%] bg-surface-container-lowest p-space-sm rounded-xl text-on-surface shadow-sm font-body-sm text-body-sm">
        Xin chào quý bạn đọc! BOOKSTORE123 có thể hỗ trợ tìm kiếm đầu sách hay kiểm tra đơn hàng giúp bạn không ạ?
      </div>
<div class="self-end max-w-[85%] bg-primary-container text-on-primary p-space-sm rounded-xl font-body-sm text-body-sm">
        Tôi muốn hỏi về tựa sách "Nỗi Buồn Chiến Tranh" còn bản bìa cứng không?
      </div>
<div class="self-start max-w-[85%] bg-surface-container-lowest p-space-sm rounded-xl text-on-surface shadow-sm font-body-sm text-body-sm">
        Dạ tác phẩm hiện tại đang có sẵn 24 cuốn bìa mềm cao cấp từ NXB Hội Nhà Văn, bản in đẹp chuẩn giấy chống cận thị ạ!
      </div>
</div>
<form class="p-space-sm bg-surface-container-lowest border-t border-surface-container flex items-center gap-2 shrink-0" id="chatForm">
<input class="flex-1 bg-surface-container-low px-3 py-2 rounded-xl font-body-sm text-body-sm text-on-surface outline-none focus:ring-1 focus:ring-primary" id="chatInput" placeholder="Nhập câu hỏi tại đây..." type="text">
<button class="p-2 bg-primary text-on-primary rounded-xl hover:bg-primary-container" type="submit">
<span class="material-symbols-outlined text-[18px]">send</span>
</button>
</form>
</div>
HTML;
    $html = str_replace('</body>', $chatPopup . '</body>', $html);
}

$html = str_replace('</body>', '<script src="' . BASE_URL . '/public/assets/stitch-live.js?v=' . $stitchAssetVersion('stitch-live.js') . '"></script><script src="' . BASE_URL . '/public/assets/stitch-admin-live.js?v=' . $stitchAssetVersion('stitch-admin-live.js') . '"></script></body>', $html);
echo $html;
