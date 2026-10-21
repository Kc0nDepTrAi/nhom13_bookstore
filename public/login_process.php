<?php
require_once '../db/db_connect.php';bookstore_models();
if ($_SERVER['REQUEST_METHOD']!=='POST') bookstore_reject('Cần gửi bằng POST.',405);
try {$u=(new \App\Models\UserModel())->verifyLogin((string)($_POST['username']??''),(string)($_POST['password']??''));}
catch (\Throwable $e) {bookstore_reject($e->getMessage(),429);}
if (!$u) bookstore_reject('Sai tài khoản hoặc mật khẩu.',401);
session_regenerate_id(true);
$_SESSION['username']=$u['username'];$_SESSION['user_id']=$u['id'];$_SESSION['role']=$u['role'];
header('Location: '.BASE_URL.($u['role']==='admin'?'/admin':'/'));exit;
