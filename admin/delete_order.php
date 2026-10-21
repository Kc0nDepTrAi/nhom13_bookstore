<?php
require_once '../db/db_connect.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') bookstore_reject('Xóa đơn cần gửi bằng POST.',405);
bookstore_models();
try {(new \App\Models\OrderModel())->deleteWithDetails((int)($_POST['id']??0));}
catch (\Throwable $e) {bookstore_reject($e->getMessage(),400);}
header('Location: manage_orders.php');exit;
