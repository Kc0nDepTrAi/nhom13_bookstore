<?php
$stitchPage = isset($_GET['book_id']) ? 'detail' : (isset($_GET['search']) || isset($_GET['category_id']) ? 'catalog' : 'home');
require dirname(__DIR__) . '/stitch/render.php';
