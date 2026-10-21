<?php
// Shared design assets; supports routed views and directly accessed PHP pages.
$uiBase = defined('BASE_URL') ? BASE_URL : preg_replace('#/(?:admin|public)(?:/.*)?$#', '', $_SERVER['SCRIPT_NAME']);
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= htmlspecialchars($uiBase, ENT_QUOTES) ?>/public/assets/bookstore.css">
<script src="<?= htmlspecialchars($uiBase, ENT_QUOTES) ?>/public/assets/bookstore.js" defer></script>

<?php if (preg_match('#/admin(?:/|$)#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))): ?>
<?php require __DIR__ . '/stitch-head.php'; ?>
<link rel="stylesheet" href="<?= htmlspecialchars($uiBase, ENT_QUOTES) ?>/public/assets/stitch-integration.css">
<?php endif; ?>
