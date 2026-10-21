<?php
require_once '../../db/db_connect.php';bookstore_models();
if ($_SERVER['REQUEST_METHOD']!=='POST') bookstore_reject('Đặt hàng cần gửi bằng POST.',405);
if (empty($_SESSION['username'])) bookstore_reject('Vui lòng đăng nhập.',401);
$cart=$_SESSION['cart']??[];if (!$cart) bookstore_reject('Giỏ hàng trống.',400);
$items=[];foreach($cart as $id=>$item)$items[]=['book_id'=>$item['book_id']??$id,'quantity'=>$item['quantity']];
try {(new \App\Models\OrderModel())->createWithItems($_SESSION['username'],$items,['notes'=>$_POST['notes']??'','payment_method'=>$_POST['payment_method']??'cash']);$_SESSION['cart']=[];}
catch (\Throwable $e) {bookstore_reject($e->getMessage(),400);}
header('Location: '.BASE_URL.'/my-orders');exit;
