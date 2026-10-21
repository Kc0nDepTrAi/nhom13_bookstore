<?php
// View-only renderer: the source HTML is imported from the supplied Stitch export.
require_once dirname(__DIR__, 3) . '/config/config.php';
$stitchPages = json_decode(file_get_contents(__DIR__ . '/manifest.json'), true);
$stitchPage = isset($stitchPages[$stitchPage ?? '']) ? $stitchPage : 'home';
if ($stitchPage === 'orders' && isset($_GET['id'])) $stitchPage = 'orderDetail';
if (preg_match('/Mobile|Android|iPhone/i', $_SERVER['HTTP_USER_AGENT'] ?? '')) {
    if ($stitchPage === 'catalog') $stitchPage = 'catalogMobile';
    if ($stitchPage === 'account') $stitchPage = 'accountMobile';
}
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
$html = str_replace('</body>', '<script src="' . BASE_URL . '/public/assets/stitch-live.js?v=' . $stitchAssetVersion('stitch-live.js') . '"></script><script src="' . BASE_URL . '/public/assets/stitch-admin-live.js?v=' . $stitchAssetVersion('stitch-admin-live.js') . '"></script></body>', $html);
echo $html;
