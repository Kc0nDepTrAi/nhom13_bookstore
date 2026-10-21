<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('bookstore_sess');
    session_start();
}
include '../db/db_connect.php'; 
?>
<?php
$stitchPage = isset($_GET['book_id']) ? 'detail' : (isset($_GET['search']) || isset($_GET['category_id']) ? 'catalog' : 'home');
require dirname(__DIR__, 1) . '/app/views/stitch/render.php';
?>
